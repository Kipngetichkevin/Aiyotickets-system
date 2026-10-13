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
     * Events whose gate is open: upcoming or still running.
     */
    private function scannableEvents()
    {
        $query = Event::where(function ($q) {
            $q->where('end_date', '>=', now())
              ->orWhere(fn ($q) => $q->whereNull('end_date')->where('date', '>=', now()->startOfDay()));
        });

        if (!Auth::user()->hasRole('admin')) {
            $query->where('user_id', Auth::id());
        }

        return $query->orderBy('date');
    }

    /**
     * Show all current events for the authenticated user.
     */
    public function index()
    {
        $events = $this->scannableEvents()->get();

        return view('qrcode.index', compact('events'));
    }

    /**
     * Gate scanner for one event.
     */
    public function showQrCodePage($event_id)
    {
        $event = $this->scannableEvents()->where('id', $event_id)->first();

        if (!$event) {
            return redirect()->route('qrcode.index')->with('error', 'Event not found, not yours, or already over.');
        }

        return view('qrcode.scanner', [
            'title'     => $event->name,
            'backUrl'   => route('qrcode.index'),
            'backLabel' => 'All events',
            'verifyUrl' => route('qrcode.scan', $event->id),
            'payload'   => [],
            'stats'     => $this->eventStats($event->id),
        ]);
    }

    /**
     * Check in the attendee whose QR code was scanned.
     */
    public function scanQRCode(Request $request, $eventId)
    {
        $request->validate(['qr' => 'required|string|max:2000']);

        $user  = Auth::user();
        $event = Event::find($eventId);

        if (!$event || (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id)) {
            return response()->json(['success' => false, 'message' => 'Not authorized for this event.'], 403);
        }

        $attendee = Attendee::with('ticket')
            ->where('qr_code', $this->extractCode($request->input('qr')))
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->first();

        if (!$attendee) {
            return response()->json(['success' => false, 'message' => 'QR not recognized for this event.'], 404);
        }

        if ($attendee->payment_status !== 'paid') {
            return response()->json([
                'success'  => false,
                'message'  => 'Ticket is not paid.',
                'attendee' => $attendee->name,
            ], 400);
        }

        if ($attendee->checked_in) {
            return response()->json([
                'success'       => false,
                'already'       => true,
                'message'       => 'Already checked in.',
                'attendee'      => $attendee->name,
                'checked_in_at' => optional($attendee->checked_in_at)->format('d M Y, H:i'),
            ], 409);
        }

        $attendee->forceFill([
            'checked_in'    => true,
            'checked_in_at' => now(),
            'checked_in_by' => $user->id,
        ])->save();

        return response()->json([
            'success'       => true,
            'message'       => 'Checked in successfully.',
            'attendee'      => $attendee->name,
            'detail'        => $attendee->ticket?->name,
            'checked_in_at' => $attendee->checked_in_at->format('d M Y, H:i'),
            'stats'         => $this->eventStats($event->id),
        ]);
    }

    private function eventStats(int $eventId): array
    {
        $paid = Attendee::where('payment_status', 'paid')
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $eventId));

        return [
            'total'      => (clone $paid)->count(),
            'checked_in' => (clone $paid)->where('checked_in', true)->count(),
        ];
    }

    /**
     * The scanned text is normally just the code, but older ticket/booking PDFs
     * encoded several lines of text with the code inside.
     */
    private function extractCode(string $scanned): string
    {
        $scanned = trim($scanned);

        if (preg_match('/\b((?:EXP|QR)-[A-Z0-9]+(?:-[A-Z0-9]+)+)\b/', $scanned, $m)) {
            return $m[1];
        }

        return $scanned;
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

        return view('qrcode.scanner', [
            'title'     => $experience->name,
            'backUrl'   => route('experiences.bookings', $experience->id),
            'backLabel' => 'Bookings list',
            'verifyUrl' => route('qrcode.experience-scanner.verify'),
            'payload'   => ['experience_id' => $experience->id],
            'stats'     => null,
        ]);
    }

    /* =========================================================
     |  EXPERIENCE QR SCANNER — verify a scanned string
     |========================================================= */
    public function verifyExperienceQr(Request $request)
    {
        $request->validate([
            'qr'            => 'required|string|max:2000',
            'experience_id' => 'required|integer|exists:experiences,id',
        ]);

        $user       = Auth::user();
        $experience = Experience::findOrFail($request->input('experience_id'));

        if (!$user->hasRole('admin') && (int) $experience->user_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $code    = $this->extractCode($request->input('qr'));
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
            'detail'        => trim(($booking->package?->name ?? '') . ($booking->category ? ' · ' . $booking->category->name : '')),
            'checked_in_at' => $booking->checked_in_at->format('d M Y, H:i'),
        ]);
    }
}