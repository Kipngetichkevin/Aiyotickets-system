<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
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
        $events = Event::where('user_id', Auth::id())
                       ->where('date', '>=', now())
                       ->where('end_date', '>=', now())
                       ->get();

        return view('qrcode.index', compact('events'));
    }

    /**
     * Show QR code page for a specific event.
     */
    public function showQrCodePage($event_id)
    {
        $event = Event::where('user_id', Auth::id())
                      ->where('id', $event_id)
                      ->where('date', '>=', now())
                      ->where('end_date', '>=', now())
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

    /* =========================================================
     |  EXPERIENCE QR SCANNER — page
     |========================================================= */
    public function experienceScanner(Request $request, $experience_id)
    {
        $experience = Experience::findOrFail($experience_id);

        $user = Auth::user();
        if (!$user->hasRole('admin') && (int) $experience->user_id !== (int) $user->id) {
            abort(403, 'You do not own this experience.');
        }

        return view('qrcode.experience-scanner', compact('experience'));
    }

    /* =========================================================
     |  EXPERIENCE QR SCANNER — verify a scanned string
     |========================================================= */
    public function verifyExperienceQr(Request $request)
    {
        $request->validate([
            'qr'            => 'required|string|max:255',
            'experience_id' => 'required|integer|exists:experiences,id',
        ]);

        $user       = Auth::user();
        $experience = Experience::findOrFail($request->input('experience_id'));

        if (!$user->hasRole('admin') && (int) $experience->user_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $code    = trim($request->input('qr'));
        $booking = ExperienceBooking::with(['package', 'category'])
            ->where('qr_code', $code)
            ->where('experience_id', $experience->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'QR not recognized for this experience.',
            ], 404);
        }

        if ($booking->payment_status !== 'paid') {
            return response()->json([
                'success'  => false,
                'message'  => 'Booking is not paid.',
                'attendee' => $booking->name,
            ], 400);
        }

        if ($booking->checked_in) {
            return response()->json([
                'success'       => false,
                'already'       => true,
                'message'       => 'Already checked in.',
                'attendee'      => $booking->name,
                'checked_in_at' => $booking->checked_in_at?->format('d M Y, H:i'),
            ], 409);
        }

        $booking->update([
            'checked_in'    => true,
            'checked_in_at' => now(),
            'checked_in_by' => $user->id,
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Checked in successfully.',
            'attendee'      => $booking->name,
            'package'       => $booking->package?->name,
            'category'      => $booking->category?->name,
            'checked_in_at' => $booking->checked_in_at->format('d M Y, H:i'),
        ]);
    }
}