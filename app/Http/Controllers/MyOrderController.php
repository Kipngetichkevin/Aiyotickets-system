<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MyOrderController extends Controller
{
    public function index()
    {
        $email = Auth::user()->email;

        $orders = Order::where('email', $email)
            ->with(['event', 'orderItems', 'attendees'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('my-orders.index', compact('orders'));
    }
}