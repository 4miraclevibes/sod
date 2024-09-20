<?php

namespace App\Http\Controllers;

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

        return view('pages.landing.transaction', compact('transactions', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'total_price' => 'required|numeric|min:0',
            'checked_items' => 'required|array',
            'checked_items.*' => 'exists:carts,id',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1',
            'shipping_price' => 'required|numeric|min:0',
            'app_fee' => 'required|numeric|min:0',
        ]);

        $checkedCarts = Cart::whereIn('id', $request->checked_items)
                            ->where('user_id', Auth::user()->id)
                            ->get();

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
            $transaction = Transaction::create([
                'total_price' => $request->total_price,
                'code' => $this->generateTransactionCode(),
                'user_id' => Auth::user()->id,
                'status' => 'pending',
                'shipping_price' => $request->shipping_price,
                'app_fee' => $request->app_fee,
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
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,done,cancelled',
        ]);

        $transaction->update([
            'status' => $request->status,
        ]);

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
}