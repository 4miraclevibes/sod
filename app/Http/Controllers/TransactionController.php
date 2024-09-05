<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionDetail;
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
            'app_fee' => 'required|numeric|min:0', // Tambahkan validasi untuk app_fee
        ]);

        $checkedCarts = Cart::whereIn('id', $request->checked_items)
                            ->where('user_id', Auth::user()->id)
                            ->get();

        //Cek apakah stok produk mencukupi
        foreach($checkedCarts as $cart) {
            if($cart->variant->getAvailableStockCount() < $request->quantities[$cart->id]) {
                return back()->with('error', 'Stok produk tidak mencukupi');
            }
        }
                            
        $transaction = Transaction::create([
            'total_price' => $request->total_price,
            'code' => $this->generateTransactionCode(),
            'user_id' => Auth::user()->id,
            'status' => 'pending',
            'shipping_price' => $request->shipping_price,
            'app_fee' => $request->app_fee, // Pastikan app_fee diisi
        ]);

        foreach($checkedCarts as $cart) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'variant_id' => $cart->variant_id,
                'quantity' => $request->quantities[$cart->id],
                'price' => $cart->variant->price,
                'capital_price' => $cart->variant->capital_price,
            ]);
        }

        $checkedCarts->each->delete();

        return redirect()->route('cart.success')->with('success', 'Transaksi berhasil dibuat');
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
            $response = Http::get("{$this->ssoUrl}/payment/show/{$transaction->payment->code}");
            if ($response->successful()) {
                $status = $response->json('data.status');
                $payment = Payment::where('code', $transaction->payment->code)->first();
                if ($payment && $status == 'success') {
                    $transaction->update(['status' => 'done']);
                    $payment->update(['status' => $status]);
                } else {
                    DB::rollBack();
                    return back()->with('error', 'Payment not found');
                }
                DB::commit();
                if($status == 'success') {
                    return back()->with('success', 'Transaksi berhasil ditandai selesai.');
                } else {
                    return back()->with('error', 'Transaksi Belum Lunas');
                }
            } else {
                DB::rollBack();
                return back()->with('error', 'Something went wrong, please try again later');
            }
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

        DB::beginTransaction();
        try {
            $response = Http::get("{$this->ssoUrl}/payment/show/{$transaction->payment->code}");
            if ($response->successful()) {
                $status = $response->json('data.status');
                $payment = Payment::where('code', $transaction->payment->code)->first();
                if ($payment && $status == 'success') {
                    $payment->update(['status' => $status]);
                    DB::commit();
                    return back()->with('success', 'Pembayaran Berhasil');
                } else {
                    DB::rollBack();
                    return redirect()->away('https://m.edupay.cloud/dashboard');
                }
            } else {
                DB::rollBack();
                return back()->with('error', 'Something went wrong, please try again later');
            }
        } catch (Exception $e) {
            Log::error('Error updating payment: ' . $e->getMessage());
            DB::rollBack();
            return back()->with('error', 'Something went wrong, please try again later');
        }
    }
}