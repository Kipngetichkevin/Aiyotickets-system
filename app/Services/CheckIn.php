<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use Illuminate\Support\Facades\DB;

/**
 * Gate check-in for events (attendees) and experiences (bookings), shared by the
 * organiser's scanner and gate links. Results are [http status, json body].
 * $by is who scanned: ['user' => id] for a logged-in organiser, ['pass' => id] for a gate link.
 */
class CheckIn
{
    /* ---------- events ---------- */

    public function scanEvent(Event $event, string $scanned, array $by): array
    {
        $code     = $this->extractCode($scanned);
        $attendee = Attendee::with('ticket')->where('qr_code', $code)
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->first();

        if (!$attendee) {
            return [404, ['success' => false, 'message' => $this->notHereMessage($code)]];
        }
        if ($attendee->payment_status !== 'paid') {
            return [400, ['success' => false, 'message' => $this->unpaidMessage($attendee->payment_status, 'Ticket'), 'attendee' => $attendee->name]];
        }

        $claimed = $this->claim(Attendee::class, $attendee->id, $by);
        $attendee->refresh();

        return $this->result($claimed, $attendee->id, $attendee->name, $attendee->ticket?->name, $attendee->checked_in_at, $this->eventStats($event));
    }

    public function searchEvent(Event $event, ?string $q): array
    {
        if ($q === null) {
            return [];
        }

        return Attendee::with(['ticket', 'order'])
            ->whereHas('ticket', fn ($t) => $t->where('event_id', $event->id))
            ->where('payment_status', 'paid')
            ->where(fn ($w) => $this->matching($w, $q))
            ->orderBy('name')->limit(15)->get()
            ->map(fn ($a) => $this->row($a->id, $a->name, $a->ticket?->name, $a->phone, $a->order?->order_no, $a->qr_code, $a->checked_in, $a->checked_in_at))
            ->all();
    }

    public function undoEvent(Event $event, Attendee $attendee): array
    {
        $attendee->forceFill(['checked_in' => false, 'checked_in_at' => null, 'checked_in_by' => null, 'gate_pass_id' => null])->save();

        return ['success' => true, 'message' => "Check-in undone for {$attendee->name}.", 'stats' => $this->eventStats($event)];
    }

    public function eventStats(Event $event): array
    {
        return $this->stats(Attendee::query()
            ->join('tickets', 'tickets.id', '=', 'attendees.ticket_id')
            ->where('tickets.event_id', $event->id)
            ->where('attendees.payment_status', 'paid')
            ->selectRaw('tickets.name as name, count(*) as total, sum(case when attendees.checked_in = 1 then 1 else 0 end) as checked_in')
            ->groupBy('tickets.name'));
    }

    /* ---------- experiences ---------- */

    public function scanExperience(Experience $experience, string $scanned, array $by): array
    {
        $code    = $this->extractCode($scanned);
        $booking = ExperienceBooking::with(['package', 'category'])
            ->where('qr_code', $code)->where('experience_id', $experience->id)->first();

        if (!$booking) {
            return [404, ['success' => false, 'message' => $this->notHereMessage($code)]];
        }
        if ($booking->payment_status !== 'paid') {
            return [400, ['success' => false, 'message' => $this->unpaidMessage($booking->payment_status, 'Booking'), 'attendee' => $booking->name]];
        }

        $claimed = $this->claim(ExperienceBooking::class, $booking->id, $by);
        $booking->refresh();

        return $this->result($claimed, $booking->id, $booking->name, $this->bookingDetail($booking), $booking->checked_in_at, $this->experienceStats($experience));
    }

    public function searchExperience(Experience $experience, ?string $q): array
    {
        if ($q === null) {
            return [];
        }

        return ExperienceBooking::with(['package', 'category', 'order'])
            ->where('experience_id', $experience->id)
            ->where('payment_status', 'paid')
            ->where(fn ($w) => $this->matching($w, $q))
            ->orderBy('name')->limit(15)->get()
            ->map(fn ($b) => $this->row($b->id, $b->name, $this->bookingDetail($b), $b->phone, $b->order?->order_no, $b->qr_code, $b->checked_in, $b->checked_in_at))
            ->all();
    }

    public function undoExperience(Experience $experience, ExperienceBooking $booking): array
    {
        $booking->forceFill(['checked_in' => false, 'checked_in_at' => null, 'checked_in_by' => null, 'gate_pass_id' => null])->save();

        return ['success' => true, 'message' => "Check-in undone for {$booking->name}.", 'stats' => $this->experienceStats($experience)];
    }

    public function experienceStats(Experience $experience): array
    {
        $name = "coalesce(experience_packages.name, 'Booking')";

        return $this->stats(ExperienceBooking::query()
            ->leftJoin('experience_packages', 'experience_packages.id', '=', 'experience_bookings.package_id')
            ->where('experience_bookings.experience_id', $experience->id)
            ->where('experience_bookings.payment_status', 'paid')
            ->selectRaw("{$name} as name, count(*) as total, sum(case when experience_bookings.checked_in = 1 then 1 else 0 end) as checked_in")
            ->groupBy(DB::raw($name)));
    }

    /* ---------- shared ---------- */

    /** Search text from a request, or null when too short to search. */
    public static function term(?string $q): ?string
    {
        $q = trim((string) $q);

        return mb_strlen($q) >= 2 ? mb_substr($q, 0, 100) : null;
    }

    /** One conditional update: if two gates scan the same code at once, only one wins. */
    private function claim(string $model, int $id, array $by): int
    {
        return $model::whereKey($id)
            ->where(fn ($q) => $q->where('checked_in', false)->orWhereNull('checked_in'))
            ->update([
                'checked_in'    => true,
                'checked_in_at' => now(),
                'checked_in_by' => $by['user'] ?? null,
                'gate_pass_id'  => $by['pass'] ?? null,
            ]);
    }

    private function result(int $claimed, int $id, string $name, ?string $detail, $at, array $stats): array
    {
        $body = [
            'id'            => $id,
            'attendee'      => $name,
            'detail'        => $detail,
            'checked_in_at' => optional($at)->format('d M Y, H:i'),
            'stats'         => $stats,
        ];

        return $claimed
            ? [200, ['success' => true, 'message' => 'Checked in successfully.'] + $body]
            : [409, ['success' => false, 'already' => true, 'message' => 'Already checked in.'] + $body];
    }

    private function stats($query): array
    {
        $byType = $query->orderBy('name')->get()
            ->map(fn ($r) => ['name' => $r->name, 'total' => (int) $r->total, 'checked_in' => (int) $r->checked_in])
            ->values();

        return ['total' => $byType->sum('total'), 'checked_in' => $byType->sum('checked_in'), 'by_type' => $byType];
    }

    private function bookingDetail(ExperienceBooking $booking): string
    {
        return trim(($booking->package?->name ?? '') . ($booking->category ? ' · ' . $booking->category->name : ''), ' ·');
    }

    private function unpaidMessage(?string $status, string $what): string
    {
        return $status === 'partial' ? 'Balance not fully paid yet (lipa mdogo mdogo).' : "{$what} is not paid.";
    }

    /** A code that isn't for this gate: say whether it's a real ticket for something else. */
    private function notHereMessage(string $code): string
    {
        $elsewhere = Attendee::where('qr_code', $code)->exists() || ExperienceBooking::where('qr_code', $code)->exists();

        return $elsewhere
            ? 'Valid ticket, but for a different event — send them to the right gate.'
            : 'QR code not recognised. Check it is an Aiyo ticket, or search by name.';
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

    private function row(int $id, string $name, ?string $detail, ?string $phone, ?string $orderNo, ?string $code, $checkedIn, $at): array
    {
        return [
            'id'            => $id,
            'name'          => $name,
            'detail'        => $detail,
            'phone'         => $phone ? '…' . substr(preg_replace('/\D/', '', $phone), -4) : null,
            'order_no'      => $orderNo,
            'code'          => $code,
            'checked_in'    => (bool) $checkedIn,
            'checked_in_at' => optional($at)->format('H:i, d M'),
        ];
    }

    /**
     * The scanned text is normally just the code, but older ticket/booking PDFs
     * encoded several lines of text with the code inside.
     */
    public function extractCode(string $scanned): string
    {
        $scanned = trim($scanned);

        if (preg_match('/\b((?:EXP|QR)-[A-Z0-9]+(?:-[A-Z0-9]+)+)\b/', $scanned, $m)) {
            return $m[1];
        }

        return $scanned;
    }
}
