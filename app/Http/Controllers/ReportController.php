<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Service;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);

        return view('backend.reports.index', $data);
    }

    /**
     * Export the same report data as CSV.
     */
    public function exportCsv(Request $request)
    {
        $data = $this->buildReportData($request);
        $rows = $data['rows'];

        $filename = 'reports-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows, $data) {
            $file = fopen('php://output', 'w');

            // Title row
            fputcsv($file, ['AiyoTickets — Report']);
            fputcsv($file, ['Generated', now()->format('d M Y, H:i')]);

            if ($data['from'] && $data['to']) {
                fputcsv($file, [
                    'Period',
                    Carbon::parse($data['from'])->format('d M Y') . ' — ' . Carbon::parse($data['to'])->format('d M Y'),
                ]);
            } else {
                fputcsv($file, ['Period', 'All time']);
            }
            fputcsv($file, []);

            // Summary row
            fputcsv($file, [
                'SUMMARY',
                'Events: ' . $data['totalEvents'],
                'Tickets: ' . $data['totalTickets'],
                'Revenue: KES ' . number_format($data['totalRevenue'], 2),
                'Payouts: KES ' . number_format($data['totalPayouts'], 2),
                'Profit: KES ' . number_format($data['totalProfit'], 2),
            ]);
            fputcsv($file, []);

            // Column headers
            fputcsv($file, [
                'Event',
                'Event Date',
                'Tickets Sold',
                'Revenue (KES)',
                'Payouts (KES)',
                'Profit (KES)',
                'Margin %',
            ]);

            foreach ($rows as $r) {
                fputcsv($file, [
                    $r['event']->name,
                    Carbon::parse($r['event']->date)->format('d M Y'),
                    $r['tickets_sold'],
                    number_format($r['revenue'], 2, '.', ''),
                    number_format($r['payouts'], 2, '.', ''),
                    number_format($r['profit'], 2, '.', ''),
                    number_format($r['margin'], 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Shared logic for building report data (used by index and exportCsv).
     */
    private function buildReportData(Request $request): array
    {
        $userId = auth()->id();

        // ---- Date range ----
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : null;
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : null;

        // ---- Filter: only events owned by current user ----
        $eventsQuery = Event::where('user_id', $userId)->orderBy('date', 'desc');

        if ($request->filled('event_id')) {
            $eventsQuery->where('id', $request->event_id);
        }

        $events = $eventsQuery->get();

        $rows = [];
        $totalRevenue = 0;
        $totalPayouts = 0;
        $totalTickets = 0;

        foreach ($events as $event) {
            $ticketIds = Ticket::where('event_id', $event->id)->pluck('id');

            // ---- Tickets sold (count of attendees) ----
            $attendeesQuery = Attendee::whereIn('ticket_id', $ticketIds);
            if ($from) $attendeesQuery->where('created_at', '>=', $from);
            if ($to)   $attendeesQuery->where('created_at', '<=', $to);

            $ticketsSold = $attendeesQuery->count();

            // ---- Revenue (paid payments for this event's orders) ----
            $orderNos = \App\Models\Order::where('event_id', $event->id)->pluck('order_no');

            $revenueQuery = Payment::where('status', 'paid')->whereIn('order_no', $orderNos);
            if ($from) $revenueQuery->where('created_at', '>=', $from);
            if ($to)   $revenueQuery->where('created_at', '<=', $to);

            $revenue = (float) $revenueQuery->sum('amount_paid');

            // ---- Payouts (paid/approved/processing) linked via services ----
            $serviceIds = Service::where('event_id', $event->id)->pluck('id');

            $payoutsQuery = Payout::whereIn('service_id', $serviceIds)
                ->whereIn('status', ['paid', 'approved', 'processing']);
            if ($from) $payoutsQuery->where('created_at', '>=', $from);
            if ($to)   $payoutsQuery->where('created_at', '<=', $to);

            $payouts = (float) $payoutsQuery->sum('amount');

            $profit = $revenue - $payouts;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            $rows[] = [
                'event'        => $event,
                'tickets_sold' => $ticketsSold,
                'revenue'      => $revenue,
                'payouts'      => $payouts,
                'profit'       => $profit,
                'margin'       => $margin,
            ];

            $totalRevenue += $revenue;
            $totalPayouts += $payouts;
            $totalTickets += $ticketsSold;
        }

        $totalProfit = $totalRevenue - $totalPayouts;
        $totalMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // ---- All events dropdown list (for the filter) ----
        $allEvents = Event::where('user_id', $userId)->orderBy('date', 'desc')->get();

        return [
            'rows'         => $rows,
            'allEvents'    => $allEvents,
            'totalEvents'  => $events->count(),
            'totalRevenue' => $totalRevenue,
            'totalPayouts' => $totalPayouts,
            'totalProfit'  => $totalProfit,
            'totalTickets' => $totalTickets,
            'totalMargin'  => $totalMargin,
            'from'         => $request->input('from'),
            'to'           => $request->input('to'),
        ];
    }
}