<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\StockDetail;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\VariantStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class TransactionController extends Controller
{
    protected $ssoUrl;

    public function __construct()
    {
        // Mengambil URL SSO dari environment variables
        $this->ssoUrl = env('SSO_API_URL');
    }
    
    public function index(Request $request)
    {
        $assets = Asset::where('is_active', true)->get();
        // Tentukan status default berdasarkan role
        $defaultStatus = Auth::user()->role->name == 'driver' ? 'shipped' : 'all';
        $status = $request->query('status', $defaultStatus);

        if (Auth::user()->role->name == 'user') {
            $query = Transaction::where('user_id', Auth::user()->id)
                ->with('details.variant.product');
        } else {
            $query = Transaction::with('details.variant.product')
                ->whereHas('payment', function($q) {
                    $q->where('user_id', Auth::user()->id);
                });
        }

        switch ($status) {
            case 'all':
                $query->whereIn('status', ['pending', 'processing']);
                break;
            case 'shipped':
                $query->whereIn('status', ['shipped', 'delivered']);
                break;
            case 'delivered':
                $query->whereIn('status', ['done']);
                break;
            case 'cancelled':
                $query->where('status', 'cancelled');
                break;
        }

        $transactions = $query->latest()->get();

        return view('pages.landing.transaction', compact('transactions', 'status', 'assets'));
    }

    public function store(Request $request)
    {
        if(Auth::user()->userAddress->where('status', 'active')->first() == null){
            return back()->with('error', 'User tidak memiliki alamat');
        }

        $request->validate([
            'checked_items' => 'required|array',
            'checked_items.*' => 'exists:carts,id',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1',
            'shipping_price' => 'required|numeric|min:0',
            'app_fee' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $checkedCarts = Cart::whereIn('id', $request->checked_items)
                            ->where('user_id', Auth::user()->id)
                            ->with('variant.product')
                            ->get();

        $total = 0;
        foreach ($checkedCarts as $cart) {
            $quantity = $request->quantities[$cart->id];
            $price = $cart->variant->price;
            $total += $quantity * $price;
        }

        if($total < 5000){
            return back()->with('error', 'Minimal pembelian Rp. 5.000');
        }
        
        $totalWithFees = $total + $request->shipping_price;
        if($totalWithFees !== (int)$request->total_price){
            return back()->with('error', 'Ada update harga');
        }
        
        
        DB::beginTransaction();
        try {
            foreach($checkedCarts as $cart) {
                $productVariant = $cart->variant;
                $quantityToUpdate = $request->quantities[$cart->id];
                $availableStockDetails = $productVariant->getAvailableStockDetails();
                
                if ($availableStockDetails->count() < $quantityToUpdate) {
                    throw new \Exception("Stok tidak cukup untuk produk {$productVariant->product->name} - {$productVariant->name}");
                }
            }
            $transactionAddress = Auth::user()->userAddress->where('status', 'active')->first();
            $transaction = Transaction::create([
                'total_price' => $totalWithFees,
                'code' => $this->generateTransactionCode(),
                'user_id' => Auth::user()->id,
                'status' => 'pending',
                'shipping_price' => $request->shipping_price,
                'app_fee' => $request->app_fee,
                'address' => $transactionAddress->subDistrict->name . ' - ' . $transactionAddress->address. ' - ' . $transactionAddress->receiver_name . ' - ' . $transactionAddress->receiver_phone . ' - ' . $transactionAddress->longitude . ' - ' . $transactionAddress->latitude,
                'notes' => $request->notes,
            ]);
    
            foreach($checkedCarts as $cart) {
                $transactionDetail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'variant_id' => $cart->variant_id,
                    'quantity' => $request->quantities[$cart->id],
                    'price' => $cart->variant->price,
                    'capital_price' => $cart->variant->getCapitalPriceForQuantity($request->quantities[$cart->id]),
                ]);
            }
            $updateIds = $availableStockDetails->take($quantityToUpdate)->pluck('id')->toArray();
                
            $updatedCount = StockDetail::whereIn('id', $updateIds)
                ->where('status', 'ready')
                ->update([
                    'status' => 'sold',
                    'price' => $transactionDetail->price,
                ]);
            
            if ($updatedCount != $quantityToUpdate) {
                throw new \Exception("Terjadi perubahan stok untuk produk {$productVariant->product->name} - {$productVariant->name}");
            }
            $checkedCarts->each->delete();
            DB::commit();
            return redirect()->route('cart.success')->with('success', 'Transaksi berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error dan kembalikan respons error yang sesuai
            return back()->with('error', $e->getMessage());
        }
    }

    private function generateTransactionCode()
    {
        $latestTransaction = Transaction::latest()->first();
        $transactionId = $latestTransaction ? $latestTransaction->id + 1 : 1;
        
        $now = Carbon::now();
        $userId = Auth::id();
        
        $code = sprintf(
            "TRX - %d%d%02d%02d%02d",
            $userId,
            $transactionId,
            $now->day,
            $now->month,
            $now->year % 100
        );
        
        return $code;
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() && $transaction->payment->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah status transaksi ini.');
        }
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,done,cancelled',
        ]);

        if($request->status == 'cancelled'){
            if($transaction->status !== 'pending'){
                return back()->with('error', 'Hanya transaksi dengan status "Pending" yang dapat dibatalkan.');
            }
            foreach ($transaction->details as $detail) {
                $quantity = $detail->quantity;

                $stockDetails = StockDetail::whereHas('variantStock', function ($query) use ($detail) {
                    $query->where('product_variant_id', $detail->variant_id);
                })
                ->where('status', 'sold')
                ->orderBy('created_at', 'desc')
                ->take($quantity)
                ->get();

                foreach ($stockDetails as $stockDetail) {
                    $stockDetail->update(['status' => 'ready']);
                }
            }
            $transaction->update([
                'status' => $request->status,
            ]);
        }

        return redirect()->route('transaction')->with('success', 'Status transaksi berhasil diperbarui.');
    }

    public function markAsDone(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() && $transaction->payment->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah status transaksi ini.');
        }

        if ($transaction->status !== 'delivered') {
            return back()->with('error', 'Hanya transaksi dengan status "Diterima" yang dapat ditandai selesai.');
        }

        DB::beginTransaction();
        try {
                $payment = Payment::where('code', $transaction->payment->code)->first();
                if ($payment && $payment->status == 'success') {
                    $transaction->update(['status' => 'done']);
                } else {
                    DB::rollBack();
                    return back()->with('error', 'Payment not found');
                }
                DB::commit();
                return back()->with('success', 'Transaksi berhasil ditandai selesai.');
                } catch (Exception $e) {
                Log::error('Error updating payment: ' . $e->getMessage());
                DB::rollBack();
                return back()->with('error', 'Something went wrong, please try again later');
        }
    }

    public function pay(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() && $transaction->payment->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah status transaksi ini.');
        }

        if ($transaction->status !== 'delivered') {
            return back()->with('error', 'Hanya transaksi dengan status "Diterima" yang dapat ditandai selesai.');
        }
        return redirect()->away('https://m.edupay.cloud/dashboard');
    }

    public function updateAdditionalCost(Request $request, Transaction $transaction)
    {
        $request->validate([
            'additional_cost' => 'required|numeric|min:0'
        ]);

        $transaction->update([
            'additional_cost' => $request->additional_cost
        ]);

        return redirect()->back()->with('success', 'Biaya tambahan berhasil diperbarui');
    }
}
