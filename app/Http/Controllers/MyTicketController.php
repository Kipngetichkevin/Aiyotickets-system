<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Support\Facades\Auth;

class MyTicketController extends Controller
{
    public function index()
    {
        $email = Auth::user()->email;

        $tickets = Attendee::where('email', $email)
            ->with(['ticket.event', 'order'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('my-tickets.index', compact('tickets'));
    }
}