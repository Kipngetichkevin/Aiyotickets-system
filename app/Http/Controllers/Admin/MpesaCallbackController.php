<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaCallback;
use Illuminate\Http\Request;

class MpesaCallbackController extends Controller
{
    public function index(Request $request)
    {
        $query = MpesaCallback::with(['mpesaStk', 'order'])->latest();

        if ($status = $request->input('status')) {
            $query->where('processing_status', $status);
        }

        if ($phone = $request->input('phone')) {
            $query->where('phone_number', 'like', "%{$phone}%");
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('mpesa_receipt_number', 'like', "%{$search}%")
                  ->orWhere('checkout_request_id', 'like', "%{$search}%")
                  ->orWhere('merchant_request_id', 'like', "%{$search}%");
            });
        }

        $callbacks = $query->paginate(25)->withQueryString();

        $counts = [
            'all'       => MpesaCallback::count(),
            'received'  => MpesaCallback::where('processing_status', 'received')->count(),
            'processed' => MpesaCallback::where('processing_status', 'processed')->count(),
            'failed'    => MpesaCallback::where('processing_status', 'failed')->count(),
        ];

        return view('admin.mpesa-callbacks.index', compact('callbacks', 'counts'));
    }

    public function show(MpesaCallback $callback)
    {
        $callback->load(['mpesaStk', 'order']);
        return view('admin.mpesa-callbacks.show', compact('callback'));
    }
}