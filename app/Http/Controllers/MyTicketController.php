<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Support\Facades\Auth;

class MyTicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $tickets = Attendee::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->with(['ticket.event', 'order'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('my-tickets.index', compact('tickets'));
    }
}