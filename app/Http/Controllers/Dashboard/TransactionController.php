<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user')->latest()->get();
        return view('pages.dashboard.transactions.index', compact('transactions'));
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);
        $transaction->update(['status' => $request->status]);
        return redirect()->route('dashboard.transaction.index')->with('success', 'Status transaksi berhasil diubah');
    }

    public function show(Transaction $transaction)
    {
        return view('pages.dashboard.transactions.show', compact('transaction'));
    }

    public function deliveries()
    {
        $transactions = Transaction::where('status', 'shipped')
            ->whereDoesntHave('payment')
            ->orWhereHas('payment', function($query) {
                $query->where('status', 'pending');
            })
            ->get();

        $users = User::whereHas('role', function($query) {
            $query->where('name', 'driver');
        })->get();

        return view('pages.dashboard.deliveries.index', compact('transactions', 'users'));
    }
}