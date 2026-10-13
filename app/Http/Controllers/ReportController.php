<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Services\SalesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'nullable|date',
            'to'       => 'nullable|date|after_or_equal:from',
            'event_id' => 'nullable|integer',
        ]);

        $user = Auth::user();

        // Events the user may pick in the filter (their own; all for admins)
        $events = Event::when(!$user->hasRole('admin'), fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('date')
            ->get(['id', 'name', 'date']);

        $eventId = $request->integer('event_id') ?: null;
        if ($eventId && !$events->contains('id', $eventId)) {
            abort(403, 'Unauthorized action.');
        }

        $report = new SalesReport(
            $user,
            $request->filled('from') ? Carbon::parse($request->input('from')) : null,
            $request->filled('to') ? Carbon::parse($request->input('to')) : null,
            $eventId,
        );

        return view('reports.index', [
            'events'       => $events,
            'filters'      => $request->only('from', 'to', 'event_id'),
            'totals'       => $report->totals(),
            'byEvent'      => $report->byEvent(),
            'byTicketType' => $report->byTicketType(),
            'byExperience' => $report->byExperience(),
            'daily'        => $report->daily(),
            'feePercent'   => config('aiyo.platform_fee_percent'),
        ]);
    }

    /**
     * Paid attendees of one event as CSV (for door lists and the organiser's records).
     * QR codes are left out: they are entry credentials.
     */
    public function exportAttendees(Event $event)
    {
        $this->authorizeEventOwner($event);

        $attendees = Attendee::with('ticket')
            ->where('payment_status', 'paid')
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->orderBy('created_at')
            ->get();

        $filename = 'attendees-' . \Illuminate\Support\Str::slug($event->name) . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($attendees) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Ticket', 'Price (KES)', 'M-Pesa receipt', 'Checked in', 'Checked in at', 'Purchased at']);
            foreach ($attendees as $a) {
                fputcsv($out, array_map([$this, 'csvSafe'], [
                    $a->name, $a->email, $a->phone, $a->ticket?->name, $a->total_price, $a->transaction_code,
                    $a->checked_in ? 'Yes' : 'No', optional($a->checked_in_at)->format('Y-m-d H:i'),
                    optional($a->created_at)->format('Y-m-d H:i'),
                ]));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Stop spreadsheet formula injection from buyer-entered names (=, +, -, @). */
    public function csvSafe($value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
