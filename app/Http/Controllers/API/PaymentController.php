<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function updatePayment($code)
    {
        $payment = Payment::where('code', $code)->first();
        $payment->update(['status' => 'success']);
        return response()->json($payment);
    }
}
