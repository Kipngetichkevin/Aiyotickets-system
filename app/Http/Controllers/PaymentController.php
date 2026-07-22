<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $paymentData = $request->all();

        Payment::create([
            'ticket_id' => 1, // Replace with actual ticket ID
            'attendee_name' => 'John Doe', // Replace with actual attendee name
            'transaction_code' => 'TRX' . uniqid(),
            'mode' => $paymentData['mode'],
            'service' => $paymentData['service'] ?? null,
            'qty' => 1, // Replace with actual quantity
            'cost' => 100 // Replace with actual cost
        ]);

        return response()->json(['message' => 'Payment processed successfully']);
    }
}
