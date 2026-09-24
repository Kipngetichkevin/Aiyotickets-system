<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $isMerchant = $user->hasRole('merchant');

        // -------- BUYER DASHBOARD (role = user) --------
        if (!$isAdmin && !$isMerchant) {
            $email = $user->email;

            $myTicketsCount = Attendee::where('email', $email)->count();
            $myOrdersCount  = Order::where('email', $email)->count();
            $mySpent        = Order::where('email', $email)
                ->where('status', 'paid')
                ->sum('total_amount');

            // Upcoming events with tickets available
            $upcomingEvents = Event::whereDate('end_date', '>=', today())
                ->has('tickets')
                ->orderBy('date', 'ASC')
                ->limit(6)
                ->get();

            // Recent tickets
            $myRecentTickets = Attendee::where('email', $email)
                ->with(['ticket.event'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            return view('buyer.dashboard', compact(
                'user',
                'myTicketsCount',
                'myOrdersCount',
                'mySpent',
                'upcomingEvents',
                'myRecentTickets'
            ));
        }

        // -------- ORGANIZER DASHBOARD (admin / merchant) --------
        $eventsQuery = Event::query();
        if (!$isAdmin) {
            $eventsQuery->where('user_id', $user->id);
        }
        $eventIds = $eventsQuery->pluck('id');

        $currentEventsCount = Event::whereIn('id', $eventIds)
            ->whereDate('end_date', '>=', today())
            ->count();

        $ordersQuery = Order::whereIn('event_id', $eventIds);

        $ticketsSold = (clone $ordersQuery)->where('status', 'paid')->sum('qty');
        $totalSales  = (clone $ordersQuery)->where('status', 'paid')->sum('total_amount');
        $pendingSales = (clone $ordersQuery)->where('status', 'pending')->sum('total_amount');
        $totalOrders  = (clone $ordersQuery)->count();

        $users = $isAdmin ? User::all() : collect();

        $events = Event::whereIn('id', $eventIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $attendees = Attendee::whereHas('ticket.event', function ($query) use ($user, $isAdmin) {
                if (!$isAdmin) {
                    $query->where('user_id', $user->id);
                }
            })
            ->with(['ticket.event'])
            ->latest()
            ->limit(10)
            ->get();

        $payments = $attendees;
        $eventTypes = EventType::all();

        return view('dashboard', compact(
            'isAdmin',
            'currentEventsCount',
            'ticketsSold',
            'totalSales',
            'pendingSales',
            'totalOrders',
            'users',
            'events',
            'attendees',
            'payments',
            'eventTypes'
        ));
    }

    public function index()
    {
        return view('layouts.dashboard');
    }

    public function getStats(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $isMerchant = $user->hasRole('merchant');

        if (!$isAdmin && !$isMerchant) {
            // Buyer stats
            return response()->json([
                'my_tickets'  => Attendee::where('email', $user->email)->count(),
                'my_orders'   => Order::where('email', $user->email)->count(),
                'total_spent' => Order::where('email', $user->email)->where('status', 'paid')->sum('total_amount'),
            ]);
        }

        $eventIds = $isAdmin
            ? Event::pluck('id')
            : Event::where('user_id', $user->id)->pluck('id');

        $stats = [
            'total_events'       => Event::whereIn('id', $eventIds)->count(),
            'upcoming_events'    => Event::whereIn('id', $eventIds)->whereDate('end_date', '>=', today())->count(),
            'past_events'        => Event::whereIn('id', $eventIds)->whereDate('end_date', '<', today())->count(),
            'total_orders'       => Order::whereIn('event_id', $eventIds)->count(),
            'total_tickets_sold' => Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('qty'),
            'total_revenue'      => Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('total_amount'),
            'total_attendees'    => Attendee::whereHas('ticket', function ($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            })->count(),
        ];

        return response()->json($stats);
    }
}