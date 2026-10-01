<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Show all current events for the authenticated user.
     */
    public function index()
    {
        $events = Event::where('user_id', Auth::id())
                       ->whereDate('end_date', '>=', today())
                       ->get();

        return view('qrcode.index', compact('events'));
    }

    /**
     * Show the scanner page for a specific event.
     */
    public function showQrCodePage($event_id)
    {
        $event = Event::where('user_id', Auth::id())
                      ->where('id', $event_id)
                      ->whereDate('end_date', '>=', today())
                      ->first();

        if (!$event) {
            return redirect()->back()->with('error', 'Event not found or not accessible.');
        }

        // Ticket types for this event, with attendee counts
        $tickets = Ticket::where('event_id', $event->id)->get();

        $ticketStats = $tickets->map(function ($ticket) {
            $total    = Attendee::where('ticket_id', $ticket->id)->sum('qty');
            $checked  = Attendee::where('ticket_id', $ticket->id)->where('checked_in', true)->sum('qty');
            return [
                'id'        => $ticket->id,
                'name'      => $ticket->name,
                'total'     => (int) $total,
                'checked'   => (int) $checked,
                'pending'   => max(0, (int) $total - (int) $checked),
            ];
        });

        // Recent check-ins for this event
        $recent = Attendee::with('ticket')
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->where('checked_in', true)
            ->orderByDesc('checked_in_at')
            ->limit(15)
            ->get();

        return view('qrcode.show', compact('event', 'tickets', 'ticketStats', 'recent'));
    }

    /**
     * POST /qrcode/check-in/{event_id}
     * Body: { qr_data: "..." }  or  { uuid: "..." }
     */
    public function checkIn(Request $request, $event_id)
    {
        $event = Event::where('user_id', Auth::id())
                      ->where('id', $event_id)
                      ->first();

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found.'], 404);
        }

        $raw = trim((string) $request->input('qr_data', $request->input('uuid', '')));

        if ($raw === '') {
            return response()->json(['success' => false, 'message' => 'No QR data provided.'], 422);
        }

        // QR may contain either a raw UUID, or a JSON blob with "uuid" key, or the
        // old PDF format ("Ticket number: ..."). Try to extract the UUID.
        $uuid = $this->extractUuid($raw);

        if (!$uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code — no valid attendee identifier found.',
            ], 422);
        }

        $attendee = Attendee::with('ticket')->where('uuid', $uuid)->first();

        if (!$attendee) {
            return response()->json(['success' => false, 'message' => 'Attendee not found.'], 404);
        }

        if (!$attendee->ticket || $attendee->ticket->event_id != $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket belongs to a different event.',
            ], 403);
        }

        if ($attendee->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is not paid (' . $attendee->payment_status . ').',
            ], 403);
        }

        if ($attendee->checked_in) {
            return response()->json([
                'success'        => false,
                'message'        => 'Already checked in at ' .
                                    optional($attendee->checked_in_at)->format('d M Y, H:i'),
                'attendee'       => [
                    'name'         => $attendee->name,
                    'ticket'       => $attendee->ticket->name ?? '—',
                    'checked_in_at'=> optional($attendee->checked_in_at)->toDateTimeString(),
                ],
            ], 409);
        }

        $attendee->update([
            'checked_in'    => true,
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Checked in successfully.',
            'attendee' => [
                'id'           => $attendee->id,
                'name'         => $attendee->name,
                'ticket'       => $attendee->ticket->name ?? '—',
                'phone'        => $attendee->phone,
                'checked_in_at'=> $attendee->checked_in_at->toDateTimeString(),
            ],
        ]);
    }

    /**
     * JSON endpoint: live counts for ticket types + recent scans.
     */
    public function activity($event_id)
    {
        $event = Event::where('user_id', Auth::id())->where('id', $event_id)->firstOrFail();

        $tickets = Ticket::where('event_id', $event->id)->get();

        $stats = $tickets->map(function ($ticket) {
            $total   = Attendee::where('ticket_id', $ticket->id)->sum('qty');
            $checked = Attendee::where('ticket_id', $ticket->id)->where('checked_in', true)->sum('qty');
            return [
                'id'      => $ticket->id,
                'name'    => $ticket->name,
                'total'   => (int) $total,
                'checked' => (int) $checked,
                'pending' => max(0, (int) $total - (int) $checked),
            ];
        });

        $recent = Attendee::with('ticket')
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->where('checked_in', true)
            ->orderByDesc('checked_in_at')
            ->limit(15)
            ->get()
            ->map(fn ($a) => [
                'id'            => $a->id,
                'name'          => $a->name,
                'ticket'        => $a->ticket->name ?? '—',
                'checked_in_at' => optional($a->checked_in_at)->format('d M Y, H:i:s'),
            ]);

        return response()->json([
            'stats'  => $stats,
            'recent' => $recent,
        ]);
    }

    /**
     * Generate QR Code for a specific ticket.
     */
    public function generateQRCode($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)
                        ->whereHas('event', function ($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $qrData = json_encode([
            'ticket_id'     => $ticket->id,
            'event_id'      => $ticket->event_id,
            'attendee_name' => $ticket->attendee->name ?? 'N/A',
        ]);

        $qrCode = QrCode::size(200)->generate($qrData);

        return response($qrCode, 200)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Pull a UUID out of various QR payload shapes.
     */
    protected function extractUuid(string $raw): ?string
    {
        // 1. Raw UUID
        if (preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $raw, $m)) {
            return $m[0];
        }

        // 2. JSON with a uuid/attendee_uuid key
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            foreach (['uuid', 'attendee_uuid', 'attendee_id', 'id'] as $key) {
                if (!empty($decoded[$key])) {
                    return (string) $decoded[$key];
                }
            }
        }

        // 3. "key: value" lines from old PDF format
        if (preg_match('/uuid[:=]\s*([0-9a-f\-]{36})/i', $raw, $m)) {
            return $m[1];
        }

        return null;
    }
}