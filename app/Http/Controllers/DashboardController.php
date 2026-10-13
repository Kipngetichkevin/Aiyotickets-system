<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\User;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function dashboard()
    {
        $userId = auth()->id();

        // Current events: not ended yet (running multi-day events included)
        $currentEventsIds = Event::where('user_id', $userId)
            ->where(fn ($q) => $q->where('end_date', '>=', now())
                ->orWhere(fn ($q) => $q->whereNull('end_date')->where('date', '>=', now()->startOfDay())))
            ->pluck('id');

        $currentEventsCount = $currentEventsIds->count();

        // Tickets sold and sales: paid tickets of those events only
        $paidTickets = Attendee::where('payment_status', 'paid')
            ->whereHas('ticket', fn ($q) => $q->whereIn('event_id', $currentEventsIds));

        $ticketsSold = (clone $paidTickets)->count();
        $totalSales  = (float) (clone $paidTickets)->sum('total_price');

        // Calculate net sales (95% after platform fee)
        $netSales = $totalSales * 0.95;

        // Get only the authenticated user's events
        $events = Event::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get recent attendees for the card (only attendees from the user's events)
        $attendees = Attendee::whereHas('ticket.event', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['ticket.event'])
            ->latest()
            ->limit(10)
            ->get();

        // Paid ticket amounts for the chart — only the fields it plots (no names, phones or QR codes)
        $payments = Attendee::where('payment_status', 'paid')
            ->whereHas('ticket.event', fn ($query) => $query->where('user_id', $userId))
            ->orderBy('created_at')
            ->get(['id', 'total_price', 'created_at']);
        $eventTypes = EventType::all();

        return view('dashboard', compact(
            'currentEventsCount',
            'ticketsSold',
            'totalSales',
            'netSales',
            'events',
            'attendees',
            'payments',
            'eventTypes'
        ));
    }

}