<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gate check-in for events (attendees) and experiences (bookings): the scanner hub,
 * the scanner, and what the scanner calls — scan, search by name/phone/order, undo.
 */
class QrCodeController extends Controller
{
    /* =========================================================
     |  HUB
     |========================================================= */

    /** Events and experiences whose gate is open (upcoming or still running), with check-in progress. */
    public function index()
    {
        $events = $this->scannableEvents()->get();
        $eventCounts = Attendee::query()
            ->join('tickets', 'tickets.id', '=', 'attendees.ticket_id')
            ->whereIn('tickets.event_id', $events->pluck('id'))
            ->where('attendees.payment_status', 'paid')
            ->selectRaw('tickets.event_id, count(*) as total, sum(case when attendees.checked_in = 1 then 1 else 0 end) as checked_in')
            ->groupBy('tickets.event_id')
            ->get()->keyBy('event_id');

        $experiences = $this->scannableExperiences()->get();
        $expCounts = ExperienceBooking::query()
            ->whereIn('experience_id', $experiences->pluck('id'))
            ->where('payment_status', 'paid')
            ->selectRaw('experience_id, count(*) as total, sum(case when checked_in = 1 then 1 else 0 end) as checked_in')
            ->groupBy('experience_id')
            ->get()->keyBy('experience_id');

        return view('qrcode.index', compact('events', 'eventCounts', 'experiences', 'expCounts'));
    }

    private function scannableEvents()
    {
        return Event::where(fn ($q) => $q->where('end_date', '>=', now())
                ->orWhere(fn ($q) => $q->whereNull('end_date')->where('date', '>=', now()->startOfDay())))
            ->where('status', '!=', 'cancelled')
            ->when(!Auth::user()->hasRole('admin'), fn ($q) => $q->where('user_id', Auth::id()))
            ->orderBy('date');
    }

    private function scannableExperiences()
    {
        return Experience::where('status', 'active')
            ->where(fn ($q) => $q->whereDate('end_date', '>=', today())
                ->orWhere(fn ($q) => $q->whereNull('end_date')->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '>=', today()))))
            ->when(!Auth::user()->hasRole('admin'), fn ($q) => $q->where('user_id', Auth::id()))
            ->orderBy('start_date');
    }

    /* =========================================================
     |  EVENTS
     |========================================================= */

    public function showQrCodePage($event_id)
    {
        $event = $this->scannableEvents()->where('id', $event_id)->first();

        if (!$event) {
            return redirect()->route('qrcode.index')->with('error', 'Event not found, not yours, or already over.');
        }

        return view('qrcode.scanner', [
            'title'     => $event->name,
            'subtitle'  => collect([$event->date?->format('D d M, H:i'), $event->location])->filter()->implode(' · '),
            'backUrl'   => route('qrcode.index'),
            'backLabel' => 'All gates',
            'verifyUrl' => route('qrcode.scan', $event->id),
            'searchUrl' => route('qrcode.search', $event->id),
            'undoUrl'   => route('qrcode.undo', [$event->id, '__ID__']),
            'payload'   => [],
            'stats'     => $this->eventStats($event->id),
        ]);
    }

    /** Check in the attendee whose QR code was scanned (or picked from search). */
    public function scanQRCode(Request $request, $eventId)
    {
        $request->validate(['qr' => 'required|string|max:2000']);

        $event = $this->ownedEvent($eventId);
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Not authorized for this event.'], 403);
        }

        $code     = $this->extractCode($request->input('qr'));
        $attendee = Attendee::with('ticket')->where('qr_code', $code)
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->first();

        if (!$attendee) {
            return response()->json(['success' => false, 'message' => $this->notHereMessage($code)], 404);
        }

        if ($attendee->payment_status !== 'paid') {
            return response()->json([
                'success'  => false,
                'message'  => $attendee->payment_status === 'partial' ? 'Balance not fully paid yet (lipa mdogo mdogo).' : 'Ticket is not paid.',
                'attendee' => $attendee->name,
            ], 400);
        }

        // One conditional update: if two gates scan the same code at once, only one wins
        $claimed = Attendee::whereKey($attendee->id)->where(fn ($q) => $q->where('checked_in', false)->orWhereNull('checked_in'))
            ->update(['checked_in' => true, 'checked_in_at' => now(), 'checked_in_by' => Auth::id()]);
        $attendee->refresh();

        return $this->scanResult($claimed, $attendee->id, $attendee->name, $attendee->ticket?->name, $attendee->checked_in_at, $this->eventStats($event->id));
    }

    /** Find a paid ticket holder by name, phone, email, order number or code (lost or dead phone). */
    public function searchEvent(Request $request, $eventId)
    {
        $event = $this->ownedEvent($eventId);
        abort_unless($event, 403);

        $q = $this->searchTerm($request);
        if ($q === null) {
            return response()->json(['results' => []]);
        }

        $results = Attendee::with(['ticket', 'order'])
            ->whereHas('ticket', fn ($t) => $t->where('event_id', $event->id))
            ->where('payment_status', 'paid')
            ->where(fn ($w) => $this->matching($w, $q))
            ->orderBy('name')->limit(15)->get()
            ->map(fn ($a) => $this->searchRow($a->id, $a->name, $a->ticket?->name, $a->phone, $a->order?->order_no, $a->qr_code, $a->checked_in, $a->checked_in_at));

        return response()->json(['results' => $results]);
    }

    /** Undo a mistaken check-in. */
    public function undoEvent($eventId, Attendee $attendee)
    {
        $event = $this->ownedEvent($eventId);
        abort_unless($event && (int) $attendee->ticket?->event_id === (int) $event->id, 403);

        $attendee->forceFill(['checked_in' => false, 'checked_in_at' => null, 'checked_in_by' => null])->save();

        return response()->json(['success' => true, 'message' => "Check-in undone for {$attendee->name}.", 'stats' => $this->eventStats($event->id)]);
    }

    private function ownedEvent($eventId): ?Event
    {
        $event = Event::find($eventId);
        $user  = Auth::user();

        return $event && ($user->hasRole('admin') || (int) $event->user_id === (int) $user->id) ? $event : null;
    }

    private function eventStats(int $eventId): array
    {
        $byType = Attendee::query()
            ->join('tickets', 'tickets.id', '=', 'attendees.ticket_id')
            ->where('tickets.event_id', $eventId)
            ->where('attendees.payment_status', 'paid')
            ->selectRaw('tickets.name, count(*) as total, sum(case when attendees.checked_in = 1 then 1 else 0 end) as checked_in')
            ->groupBy('tickets.name')
            ->orderBy('tickets.name')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'total' => (int) $r->total, 'checked_in' => (int) $r->checked_in]);

        return [
            'total'      => $byType->sum('total'),
            'checked_in' => $byType->sum('checked_in'),
            'by_type'    => $byType->values(),
        ];
    }

    /* =========================================================
     |  EXPERIENCES
     |========================================================= */

    public function experienceScanner(Request $request, $experience_id)
    {
        $experience = $this->ownedExperience($experience_id);
        abort_unless($experience, 403, 'You do not own this experience.');

        return view('qrcode.scanner', [
            'title'     => $experience->name,
            'subtitle'  => collect([$experience->start_date?->format('D d M'), $experience->location])->filter()->implode(' · '),
            'backUrl'   => route('qrcode.index'),
            'backLabel' => 'All gates',
            'verifyUrl' => route('qrcode.experience-scanner.verify'),
            'searchUrl' => route('qrcode.experience-scanner.search', $experience->id),
            'undoUrl'   => route('qrcode.experience-scanner.undo', [$experience->id, '__ID__']),
            'payload'   => ['experience_id' => $experience->id],
            'stats'     => $this->experienceStats($experience->id),
        ]);
    }

    public function verifyExperienceQr(Request $request)
    {
        $request->validate([
            'qr'            => 'required|string|max:2000',
            'experience_id' => 'required|integer|exists:experiences,id',
        ]);

        $experience = $this->ownedExperience($request->input('experience_id'));
        if (!$experience) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $code    = $this->extractCode($request->input('qr'));
        $booking = ExperienceBooking::with(['package', 'category'])
            ->where('qr_code', $code)
            ->where('experience_id', $experience->id)
            ->first();

        if (!$booking) {
            return response()->json(['success' => false, 'message' => $this->notHereMessage($code)], 404);
        }

        if ($booking->payment_status !== 'paid') {
            return response()->json([
                'success'  => false,
                'message'  => $booking->payment_status === 'partial' ? 'Balance not fully paid yet (lipa mdogo mdogo).' : 'Booking is not paid.',
                'attendee' => $booking->name,
            ], 400);
        }

        // One conditional update: if two gates scan the same code at once, only one wins
        $claimed = ExperienceBooking::whereKey($booking->id)->where(fn ($q) => $q->where('checked_in', false)->orWhereNull('checked_in'))
            ->update(['checked_in' => true, 'checked_in_at' => now(), 'checked_in_by' => Auth::id()]);
        $booking->refresh();

        return $this->scanResult($claimed, $booking->id, $booking->name, $this->bookingDetail($booking), $booking->checked_in_at, $this->experienceStats($experience->id));
    }

    public function searchExperience(Request $request, $experienceId)
    {
        $experience = $this->ownedExperience($experienceId);
        abort_unless($experience, 403);

        $q = $this->searchTerm($request);
        if ($q === null) {
            return response()->json(['results' => []]);
        }

        $results = ExperienceBooking::with(['package', 'category', 'order'])
            ->where('experience_id', $experience->id)
            ->where('payment_status', 'paid')
            ->where(fn ($w) => $this->matching($w, $q))
            ->orderBy('name')->limit(15)->get()
            ->map(fn ($b) => $this->searchRow($b->id, $b->name, $this->bookingDetail($b), $b->phone, $b->order?->order_no, $b->qr_code, $b->checked_in, $b->checked_in_at));

        return response()->json(['results' => $results]);
    }

    public function undoExperience($experienceId, ExperienceBooking $booking)
    {
        $experience = $this->ownedExperience($experienceId);
        abort_unless($experience && (int) $booking->experience_id === (int) $experience->id, 403);

        $booking->forceFill(['checked_in' => false, 'checked_in_at' => null, 'checked_in_by' => null])->save();

        return response()->json(['success' => true, 'message' => "Check-in undone for {$booking->name}.", 'stats' => $this->experienceStats($experience->id)]);
    }

    private function ownedExperience($experienceId): ?Experience
    {
        $experience = Experience::find($experienceId);
        $user       = Auth::user();

        return $experience && ($user->hasRole('admin') || (int) $experience->user_id === (int) $user->id) ? $experience : null;
    }

    private function experienceStats(int $experienceId): array
    {
        $byType = ExperienceBooking::query()
            ->leftJoin('experience_packages', 'experience_packages.id', '=', 'experience_bookings.package_id')
            ->where('experience_bookings.experience_id', $experienceId)
            ->where('experience_bookings.payment_status', 'paid')
            ->selectRaw("coalesce(experience_packages.name, 'Booking') as name, count(*) as total, sum(case when experience_bookings.checked_in = 1 then 1 else 0 end) as checked_in")
            ->groupBy(DB::raw("coalesce(experience_packages.name, 'Booking')"))
            ->orderBy('name')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'total' => (int) $r->total, 'checked_in' => (int) $r->checked_in]);

        return [
            'total'      => $byType->sum('total'),
            'checked_in' => $byType->sum('checked_in'),
            'by_type'    => $byType->values(),
        ];
    }

    private function bookingDetail(ExperienceBooking $booking): string
    {
        return trim(($booking->package?->name ?? '') . ($booking->category ? ' · ' . $booking->category->name : ''), ' ·');
    }

    /* =========================================================
     |  SHARED
     |========================================================= */

    private function scanResult(int $claimed, int $id, string $name, ?string $detail, $checkedInAt, array $stats)
    {
        if (!$claimed) {
            return response()->json([
                'success'       => false,
                'already'       => true,
                'message'       => 'Already checked in.',
                'id'            => $id,
                'attendee'      => $name,
                'detail'        => $detail,
                'checked_in_at' => optional($checkedInAt)->format('d M Y, H:i'),
                'stats'         => $stats,
            ], 409);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Checked in successfully.',
            'id'            => $id,
            'attendee'      => $name,
            'detail'        => $detail,
            'checked_in_at' => $checkedInAt->format('d M Y, H:i'),
            'stats'         => $stats,
        ]);
    }

    /** A code that isn't for this gate: say whether it's a real ticket for something else. */
    private function notHereMessage(string $code): string
    {
        $elsewhere = Attendee::where('qr_code', $code)->exists() || ExperienceBooking::where('qr_code', $code)->exists();

        return $elsewhere
            ? 'Valid ticket, but for a different event — send them to the right gate.'
            : 'QR code not recognised. Check it is an Aiyo ticket, or search by name.';
    }

    private function searchTerm(Request $request): ?string
    {
        $q = trim((string) $request->input('q'));

        return mb_strlen($q) >= 2 ? mb_substr($q, 0, 100) : null;
    }

    /** Name, phone (any format), email, order number or code. */
    private function matching($query, string $q): void
    {
        $digits = preg_replace('/\D/', '', $q);
        $phone  = strlen($digits) >= 6 ? substr($digits, -9) : null;   // 0712…, 254712…, +254 712… all match

        $query->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('qr_code', strtoupper($q))
            ->when($phone, fn ($w) => $w->orWhere('phone', 'like', "%{$phone}"))
            ->orWhereHas('order', fn ($o) => $o->where('order_no', strtoupper($q)));
    }

    private function searchRow(int $id, string $name, ?string $detail, ?string $phone, ?string $orderNo, ?string $code, $checkedIn, $checkedInAt): array
    {
        return [
            'id'            => $id,
            'name'          => $name,
            'detail'        => $detail,
            'phone'         => $phone ? '…' . substr(preg_replace('/\D/', '', $phone), -4) : null,
            'order_no'      => $orderNo,
            'code'          => $code,
            'checked_in'    => (bool) $checkedIn,
            'checked_in_at' => optional($checkedInAt)->format('H:i, d M'),
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
}
