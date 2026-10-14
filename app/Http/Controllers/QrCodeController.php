<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\GatePass;
use App\Services\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The organiser's gate: the hub of events and experiences to check people into,
 * the scanner, and gate links for door staff (the check-in itself is App\Services\CheckIn).
 */
class QrCodeController extends Controller
{
    public function __construct(private CheckIn $checkIn)
    {
    }

    /* =========================================================
     |  HUB
     |========================================================= */

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
            'gatesUrl'  => route('qrcode.gates', ['event', $event->id]),
            'verifyUrl' => route('qrcode.scan', $event->id),
            'searchUrl' => route('qrcode.search', $event->id),
            'undoUrl'   => route('qrcode.undo', [$event->id, '__ID__']),
            'payload'   => [],
            'stats'     => $this->checkIn->eventStats($event),
        ]);
    }

    public function scanQRCode(Request $request, $eventId)
    {
        $request->validate(['qr' => 'required|string|max:2000']);

        if (!$event = $this->ownedEvent($eventId)) {
            return response()->json(['success' => false, 'message' => 'Not authorized for this event.'], 403);
        }

        [$status, $body] = $this->checkIn->scanEvent($event, $request->input('qr'), ['user' => Auth::id()]);

        return response()->json($body, $status);
    }

    public function searchEvent(Request $request, $eventId)
    {
        abort_unless($event = $this->ownedEvent($eventId), 403);

        return response()->json(['results' => $this->checkIn->searchEvent($event, CheckIn::term($request->input('q')))]);
    }

    public function undoEvent($eventId, Attendee $attendee)
    {
        $event = $this->ownedEvent($eventId);
        abort_unless($event && (int) $attendee->ticket?->event_id === (int) $event->id, 403);

        return response()->json($this->checkIn->undoEvent($event, $attendee));
    }

    private function ownedEvent($eventId): ?Event
    {
        $event = Event::find($eventId);
        $user  = Auth::user();

        return $event && ($user->hasRole('admin') || (int) $event->user_id === (int) $user->id) ? $event : null;
    }

    /* =========================================================
     |  EXPERIENCES
     |========================================================= */

    public function experienceScanner(Request $request, $experience_id)
    {
        abort_unless($experience = $this->ownedExperience($experience_id), 403, 'You do not own this experience.');

        return view('qrcode.scanner', [
            'title'     => $experience->name,
            'subtitle'  => collect([$experience->start_date?->format('D d M'), $experience->location])->filter()->implode(' · '),
            'backUrl'   => route('qrcode.index'),
            'backLabel' => 'All gates',
            'gatesUrl'  => route('qrcode.gates', ['experience', $experience->id]),
            'verifyUrl' => route('qrcode.experience-scanner.verify'),
            'searchUrl' => route('qrcode.experience-scanner.search', $experience->id),
            'undoUrl'   => route('qrcode.experience-scanner.undo', [$experience->id, '__ID__']),
            'payload'   => ['experience_id' => $experience->id],
            'stats'     => $this->checkIn->experienceStats($experience),
        ]);
    }

    public function verifyExperienceQr(Request $request)
    {
        $request->validate([
            'qr'            => 'required|string|max:2000',
            'experience_id' => 'required|integer|exists:experiences,id',
        ]);

        if (!$experience = $this->ownedExperience($request->input('experience_id'))) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        [$status, $body] = $this->checkIn->scanExperience($experience, $request->input('qr'), ['user' => Auth::id()]);

        return response()->json($body, $status);
    }

    public function searchExperience(Request $request, $experienceId)
    {
        abort_unless($experience = $this->ownedExperience($experienceId), 403);

        return response()->json(['results' => $this->checkIn->searchExperience($experience, CheckIn::term($request->input('q')))]);
    }

    public function undoExperience($experienceId, ExperienceBooking $booking)
    {
        $experience = $this->ownedExperience($experienceId);
        abort_unless($experience && (int) $booking->experience_id === (int) $experience->id, 403);

        return response()->json($this->checkIn->undoExperience($experience, $booking));
    }

    private function ownedExperience($experienceId): ?Experience
    {
        $experience = Experience::find($experienceId);
        $user       = Auth::user();

        return $experience && ($user->hasRole('admin') || (int) $experience->user_id === (int) $user->id) ? $experience : null;
    }

    /* =========================================================
     |  GATE LINKS (door staff without an account)
     |========================================================= */

    public function gates(string $type, $id)
    {
        $target = $this->ownedTarget($type, $id);

        return view('qrcode.gates', [
            'type'   => $type,
            'target' => $target,
            'passes' => GatePass::where($type . '_id', $target->id)->latest()->get(),
            'scans'  => ($type === 'event' ? Attendee::query() : ExperienceBooking::query())
                ->whereNotNull('gate_pass_id')
                ->selectRaw('gate_pass_id, count(*) n')->groupBy('gate_pass_id')->pluck('n', 'gate_pass_id'),
            'defaultExpiry' => $this->defaultExpiry($type, $target),
        ]);
    }

    public function storeGate(Request $request, string $type, $id)
    {
        $target = $this->ownedTarget($type, $id);
        $data   = $request->validate([
            'label'      => 'required|string|max:100',
            'pin'        => 'nullable|digits_between:4,6',
            'expires_at' => 'required|date|after:now',
        ], ['pin.digits_between' => 'The PIN must be 4 to 6 digits.']);

        $pass = GatePass::issue([$type . '_id' => $target->id, 'label' => $data['label'], 'expires_at' => $data['expires_at']], $data['pin'] ?? null, $request->user());

        return redirect()->route('qrcode.gates', [$type, $target->id])
            ->with('created', $pass->id)
            ->with('success', "Gate link for “{$pass->label}” is ready. Send it to them" . (!empty($data['pin']) ? ' — and the PIN separately.' : '.'));
    }

    public function revokeGate(GatePass $pass)
    {
        $type = $pass->event_id ? 'event' : 'experience';
        $this->ownedTarget($type, $pass->event_id ?? $pass->experience_id);

        $pass->forceFill(['revoked_at' => now()])->save();

        return back()->with('success', "Gate link “{$pass->label}” switched off. It stops working immediately.");
    }

    private function ownedTarget(string $type, $id): Event|Experience
    {
        $target = match ($type) {
            'event'      => $this->ownedEvent($id),
            'experience' => $this->ownedExperience($id),
            default      => null,
        };
        abort_unless($target, 403);

        return $target;
    }

    /** Links last until the morning after the event ends. */
    private function defaultExpiry(string $type, Event|Experience $target)
    {
        $end = $type === 'event'
            ? ($target->end_date ?? $target->date?->copy()->endOfDay())
            : ($target->end_date ?? $target->start_date?->copy()->endOfDay());

        return ($end && $end->isFuture() ? $end->copy() : now()->endOfDay())->addHours(12);
    }
}
