<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Show all current events for the authenticated user.
     */
    public function index()
    {
        // ✅ FIX: only end_date matters. Any event that hasn't finished yet shows.
        $events = Event::where('user_id', Auth::id())
                       ->whereDate('end_date', '>=', today())
                       ->get();

        return view('qrcode.index', compact('events'));
    }

    /**
     * Show QR code page for a specific event.
     */
    public function showQrCodePage($event_id)
    {
        // ✅ FIX: whereDate, drop the date >= now() filter
        $event = Event::where('user_id', Auth::id())
                      ->where('id', $event_id)
                      ->whereDate('end_date', '>=', today())
                      ->first();

        if (!$event) {
            return redirect()->back()->with('error', 'Event not found or not accessible.');
        }

        $tickets = Ticket::where('event_id', $event_id)->get();

        return view('qrcode.show', compact('event', 'tickets'));
    }

    /**
     * Handle scanning the QR code and checking in attendees.
     */
    public function scanQRCode(Request $request, $eventId)
    {
        $userId = auth()->user()->id;

        $event = Event::where('user_id', $userId)
                      ->where('id', $eventId)
                      ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ]);
        }

        $ticketId = $request->input('ticket_id');

        if (!$ticketId) {
            return response()->json([
                'success' => false,
                'message' => 'No ticket ID provided.',
            ]);
        }

        $ticket = Ticket::where('event_id', $eventId)
                        ->where('id', $ticketId)
                        ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ]);
        }

        if ($ticket->checked_in) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket already checked in.',
            ]);
        }

        $ticket->checked_in = true;
        $ticket->checked_in_at = now();
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Ticket successfully checked in.',
            'attendee' => $ticket,
        ]);
    }

    /**
     * Generate QR Code for a specific ticket.
     */
    public function generateQRCode($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)
                        ->whereHas('event', function($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $qrData = json_encode([
            'ticket_id' => $ticket->id,
            'event_id' => $ticket->event_id,
            'attendee_name' => $ticket->attendee->name ?? 'N/A',
        ]);

        $qrCode = QrCode::size(200)->generate($qrData);

        return response($qrCode, 200)->header('Content-Type', 'image/svg+xml');
    }
}