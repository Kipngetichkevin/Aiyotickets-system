<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Admins and merchants see the management dashboard
        if ($user->hasAnyRole(['admin', 'merchant']) || $user->is_merchant) {
            return $this->managementDashboard($user);
        }

        // Everyone else → buyer dashboard
        return $this->buyerDashboard($user);
    }

    // ==================================================================
    // MANAGEMENT DASHBOARD (admin + merchant)
    // ==================================================================
    private function managementDashboard($user)
    {
        $userId = $user->id;
        $now    = now();

        $eventIds  = Event::where('user_id', $userId)->pluck('id');
        $ticketIds = Ticket::whereIn('event_id', $eventIds)->pluck('id');

        $totalEvents      = Event::where('user_id', $userId)->count();
        $activeEvents     = Event::where('user_id', $userId)->where('date', '>=', $now)->count();
        $totalTicketsSold = Attendee::whereIn('ticket_id', $ticketIds)->count();

        $totalRevenue = Payment::where('status', 'paid')
            ->whereIn('order_no', function ($q) use ($eventIds) {
                $q->select('order_no')->from('orders')->whereIn('event_id', $eventIds);
            })
            ->sum('amount_paid');

        $recentAttendees = Attendee::whereIn('ticket_id', $ticketIds)
            ->latest()->take(8)->get();

        $upcomingEvents = Event::where('user_id', $userId)
            ->where('date', '>=', $now)
            ->orderBy('date', 'ASC')
            ->take(5)->get();

        $userPoints = $user->points ?? 0;

        // ---- Charts: rolling last 12 months ----
        $monthLabels = [];
        $monthKeys   = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $monthKeys[]   = $date->format('Y-m');
            $monthLabels[] = $date->format('M');
        }

        $revenueByMonth = array_fill(0, 12, 0);
        $payments = Payment::where('status', 'paid')
            ->whereIn('order_no', function ($q) use ($eventIds) {
                $q->select('order_no')->from('orders')->whereIn('event_id', $eventIds);
            })
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->get();

        foreach ($payments as $p) {
            $date = $p->paid_at ?? $p->created_at;
            if (!$date) continue;
            $key = Carbon::parse($date)->format('Y-m');
            $idx = array_search($key, $monthKeys);
            if ($idx !== false) $revenueByMonth[$idx] += (float) $p->amount_paid;
        }

        $ticketsByMonth = array_fill(0, 12, 0);
        $attendees = Attendee::whereIn('ticket_id', $ticketIds)
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->get();

        foreach ($attendees as $a) {
            if (!$a->created_at) continue;
            $key = $a->created_at->format('Y-m');
            $idx = array_search($key, $monthKeys);
            if ($idx !== false) $ticketsByMonth[$idx] += 1;
        }

        $growthByMonth = [];
        $running = 0;
        foreach ($ticketsByMonth as $count) {
            $running += $count;
            $growthByMonth[] = $running;
        }

        return view('dashboard', compact(
            'totalEvents',
            'activeEvents',
            'totalTicketsSold',
            'totalRevenue',
            'recentAttendees',
            'upcomingEvents',
            'userPoints',
            'revenueByMonth',
            'ticketsByMonth',
            'growthByMonth',
            'monthLabels'
        ));
    }

    // ==================================================================
    // BUYER DASHBOARD (regular users)
    // ==================================================================
    private function buyerDashboard($user)
    {
        // All attendee records linked to this user
        $myAttendees = Attendee::with(['ticket.event'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        // Counts
        $totalTickets = $myAttendees->count();

        $eventIds = $myAttendees
            ->pluck('ticket.event_id')
            ->unique()
            ->filter()
            ->values();

        $totalEvents = $eventIds->count();

        // Upcoming (event date in the future)
        $upcomingAttendees = $myAttendees
            ->filter(function ($a) {
                return $a->ticket
                    && $a->ticket->event
                    && Carbon::parse($a->ticket->event->date)->isFuture();
            })
            ->values();

        $upcomingCount = $upcomingAttendees->count();

        // Total spent = sum of ticket prices
        $totalSpent = $myAttendees->sum(function ($a) {
            return (float) ($a->ticket->price ?? 0);
        });

        // Rewards
        $userPoints = $user->points ?? 0;

        return view('buyer.dashboard', compact(
            'user',
            'myAttendees',
            'totalTickets',
            'totalEvents',
            'upcomingAttendees',
            'upcomingCount',
            'totalSpent',
            'userPoints'
        ));
    }
}