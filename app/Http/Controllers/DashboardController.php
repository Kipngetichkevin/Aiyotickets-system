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

        // Get current events
        $currentEventsIds = Event::where('user_id', $userId)
            ->where('date', '>', now())
            ->pluck('id');

        $currentEventsCount = $currentEventsIds->count();

        // Get tickets sold
        $ticketsSold = Order::whereIn('event_id', $currentEventsIds)->sum('qty');

        // Get total sales
        $totalSales = Order::whereIn('event_id', $currentEventsIds)->sum('total_amount');

        // Calculate net sales (95% after platform fee)
        $netSales = $totalSales * 0.95;

        // Get all users for the table (exclude current user or show all)
        $users = User::all();

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

        // Get payments for the chart (only from the user's events)
        $payments = Attendee::whereHas('ticket.event', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
        $eventTypes = EventType::all();

        return view('dashboard', compact(
            'currentEventsCount',
            'ticketsSold',
            'totalSales',
            'netSales',
            'users',
            'events',
            'attendees',
            'payments',
            'eventTypes'
        ));
    }

    /**
     * Display the index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('layouts.dashboard');
    }

    /**
     * Get dashboard statistics for API or AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $userId = auth()->id();

        $stats = [
            'total_events' => Event::where('user_id', $userId)->count(),
            'upcoming_events' => Event::where('user_id', $userId)
                ->where('date', '>', now())
                ->count(),
            'past_events' => Event::where('user_id', $userId)
                ->where('date', '<', now())
                ->count(),
            'total_orders' => Order::whereHas('event', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count(),
            'total_tickets_sold' => Order::whereHas('event', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->sum('qty'),
            'total_revenue' => Order::whereHas('event', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->sum('total_amount'),
            'total_attendees' => Attendee::whereHas('ticket.event', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count(),
        ];

        return response()->json($stats);
    }
}