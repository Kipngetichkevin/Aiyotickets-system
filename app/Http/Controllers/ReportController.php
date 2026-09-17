<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        // Date range — defaults to last 6 months
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subMonths(6)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        // Scope events by role
        $eventsQuery = Event::query();
        if (!$isAdmin) {
            $eventsQuery->where('user_id', $user->id);
        }
        $eventIds = $eventsQuery->pluck('id');

        // Optional event filter
        $selectedEventId = $request->filled('event_id') ? (int) $request->event_id : null;
        if ($selectedEventId) {
            $eventIds = collect([$selectedEventId]);
        }

        // ================================================================
        // PAYMENT SCOPE
        // Admin → all payments
        // Merchant → only payments whose order_no belongs to their events
        // ================================================================
        $scopedOrderNos = $isAdmin
            ? null
            : Order::whereIn('event_id', $eventIds)->pluck('order_no');

        $applyPaymentScope = function ($query) use ($scopedOrderNos) {
            if ($scopedOrderNos !== null) {
                $query->whereIn('order_no', $scopedOrderNos);
            }
            return $query;
        };

        // ================================================================
        // Stat cards — in range
        // ================================================================
        $revenueQuery = $applyPaymentScope(
            Payment::where('status', 'paid')->whereBetween('created_at', [$from, $to])
        );
        $totalRevenue = (clone $revenueQuery)->sum('amount_paid');

        $pendingQuery = $applyPaymentScope(
            Payment::where('status', 'pending')->whereBetween('created_at', [$from, $to])
        );
        $pendingRevenue = (clone $pendingQuery)->sum('balance');

        // Orders — scoped by event (for merchants) OR all (for admin)
        $ordersQuery = Order::whereIn('event_id', $eventIds)
            ->whereBetween('created_at', [$from, $to]);
        $totalOrders = (clone $ordersQuery)->count();

        // Tickets sold
        $ticketIds = Ticket::whereIn('event_id', $eventIds)->pluck('id');
        $attendeesQuery = Attendee::whereIn('ticket_id', $ticketIds)
            ->whereBetween('created_at', [$from, $to]);
        $totalAttendees = (clone $attendeesQuery)->count();
        $ticketsSold    = (clone $attendeesQuery)->sum('qty');

        $avgOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

        // ================================================================
        // Monthly chart — last 12 months
        // ================================================================
        $monthlyQuery = $applyPaymentScope(
            Payment::where('status', 'paid')->where('created_at', '>=', now()->subMonths(12))
        );

        $monthlyRevenue = $monthlyQuery
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(amount_paid) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ================================================================
        // Top events by revenue
        // ================================================================
        $topEvents = Event::whereIn('id', $eventIds)
            ->withCount(['orders as orders_count'])
            ->get()
            ->map(function ($event) use ($isAdmin) {
                $eventOrderNos = Order::where('event_id', $event->id)->pluck('order_no');
                $event->revenue = Payment::whereIn('order_no', $eventOrderNos)
                    ->where('status', 'paid')
                    ->sum('amount_paid');
                $eventTicketIds = Ticket::where('event_id', $event->id)->pluck('id');
                $event->tickets_sold = Attendee::whereIn('ticket_id', $eventTicketIds)->sum('qty');
                return $event;
            })
            ->sortByDesc('revenue')
            ->take(5);

        // ================================================================
        // Top ticket types
        // ================================================================
        $topTickets = Ticket::whereIn('id', $ticketIds)
            ->get()
            ->map(function ($ticket) {
                $ticket->sold = Attendee::where('ticket_id', $ticket->id)->sum('qty');
                $ticket->revenue = Attendee::where('ticket_id', $ticket->id)->sum('total_price');
                return $ticket;
            })
            ->sortByDesc('revenue')
            ->take(5);

        // ================================================================
        // Payment method breakdown (all-time paid)
        // ================================================================
        $paymentBreakdown = $applyPaymentScope(Payment::where('status', 'paid'))
            ->select('payment_mode', DB::raw('SUM(amount_paid) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_mode')
            ->orderByDesc('total')
            ->get();

        // ================================================================
        // Events dropdown
        // ================================================================
        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('reports.index', compact(
            'isAdmin',
            'from',
            'to',
            'events',
            'totalRevenue',
            'pendingRevenue',
            'totalOrders',
            'totalAttendees',
            'ticketsSold',
            'avgOrderValue',
            'monthlyRevenue',
            'topEvents',
            'topTickets',
            'paymentBreakdown',
            'selectedEventId'
        ));
    }
}