<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Sales figures for a merchant (their own events and experiences) or, for an admin,
 * the whole platform. Only paid tickets and bookings count.
 */
class SalesReport
{
    public function __construct(
        private User $user,
        private ?CarbonInterface $from = null,
        private ?CarbonInterface $to = null,
        private ?int $eventId = null,
        private bool $ownOnly = false,
    ) {
    }

    /** A user's own sales only (also for admins) — used for balances and payouts. */
    public static function own(User $user): self
    {
        return new self($user, ownOnly: true);
    }

    public static function feeRate(): float
    {
        return config('aiyo.platform_fee_percent') / 100;
    }

    private function isAdmin(): bool
    {
        return !$this->ownOnly && $this->user->hasRole('admin');
    }

    /** Paid tickets of the user's events (all events for admins). */
    public function tickets(): Builder
    {
        return Attendee::query()
            ->join('tickets', 'tickets.id', '=', 'attendees.ticket_id')
            ->join('events', 'events.id', '=', 'tickets.event_id')
            ->where('attendees.payment_status', 'paid')
            ->when(!$this->isAdmin(), fn ($q) => $q->where('events.user_id', $this->user->id))
            ->when($this->eventId, fn ($q) => $q->where('events.id', $this->eventId))
            ->when($this->from, fn ($q) => $q->where('attendees.created_at', '>=', $this->from->copy()->startOfDay()))
            ->when($this->to, fn ($q) => $q->where('attendees.created_at', '<=', $this->to->copy()->endOfDay()));
    }

    /** Paid bookings of the user's experiences (all for admins). Skipped when filtering by event. */
    public function bookings(): Builder
    {
        return ExperienceBooking::query()
            ->join('experiences', 'experiences.id', '=', 'experience_bookings.experience_id')
            ->where('experience_bookings.payment_status', 'paid')
            ->when(!$this->isAdmin(), fn ($q) => $q->where('experiences.user_id', $this->user->id))
            ->when($this->eventId, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($this->from, fn ($q) => $q->where('experience_bookings.created_at', '>=', $this->from->copy()->startOfDay()))
            ->when($this->to, fn ($q) => $q->where('experience_bookings.created_at', '<=', $this->to->copy()->endOfDay()));
    }

    /** Lipa mdogo mdogo orders sold by the user (all for admins). */
    public function instalmentOrders(): Builder
    {
        return Order::query()
            ->where('payment_plan', 'instalments')
            ->when(!$this->isAdmin(), fn ($q) => $q->where('seller_id', $this->user->id))
            ->when($this->eventId, fn ($q) => $q->where('event_id', $this->eventId));
    }

    /** Deposits kept from plans that missed their due date (merchant revenue). */
    public function forfeitedDeposits(): float
    {
        return (float) $this->instalmentOrders()->where('status', 'lapsed')
            ->when($this->from, fn ($q) => $q->where('lapsed_at', '>=', $this->from->copy()->startOfDay()))
            ->when($this->to, fn ($q) => $q->where('lapsed_at', '<=', $this->to->copy()->endOfDay()))
            ->sum('deposit_amount');
    }

    public function totals(): array
    {
        $ticketGross  = (float) $this->tickets()->sum('attendees.total_price');
        $bookingGross = (float) $this->bookings()->sum('experience_bookings.total_price');
        $forfeited    = $this->forfeitedDeposits();
        $gross        = $ticketGross + $bookingGross + $forfeited;
        $fee          = round($gross * self::feeRate(), 2);

        $tickets  = $this->tickets()->count();
        $bookings = $this->bookings()->count();
        $checked  = $this->tickets()->where('attendees.checked_in', true)->count()
                  + $this->bookings()->where('experience_bookings.checked_in', true)->count();

        return [
            'gross'      => $gross,
            'fee'        => $fee,
            'net'        => $gross - $fee,
            'tickets'    => $tickets,
            'bookings'   => $bookings,
            'checked_in' => $checked,
            'attendance' => ($tickets + $bookings) ? round($checked / ($tickets + $bookings) * 100) : 0,
            'forfeited'   => $forfeited,
            'outstanding' => (float) $this->instalmentOrders()->where('status', 'partial')->sum('balance_due'),
            'instalments_open' => $this->instalmentOrders()->where('status', 'partial')->count(),
        ];
    }

    /** Per event: tickets sold, capacity, check-ins, gross. */
    public function byEvent(): Collection
    {
        return $this->tickets()
            ->selectRaw('events.id, events.name, events.date, count(*) as sold, sum(attendees.checked_in) as checked_in, sum(attendees.total_price) as gross')
            ->groupBy('events.id', 'events.name', 'events.date')
            ->orderByDesc('events.date')
            ->get()
            ->map(function ($row) {
                $row->capacity = (int) \App\Models\Ticket::where('event_id', $row->id)->sum('quantity');
                return $row;
            });
    }

    /** Per ticket type (for one event, or all). */
    public function byTicketType(): Collection
    {
        return $this->tickets()
            ->selectRaw('tickets.id, tickets.name, events.name as event_name, tickets.price, tickets.quantity, count(*) as sold, sum(attendees.total_price) as gross')
            ->groupBy('tickets.id', 'tickets.name', 'events.name', 'tickets.price', 'tickets.quantity')
            ->orderByDesc('gross')
            ->get();
    }

    /** Per experience: bookings, check-ins, gross. */
    public function byExperience(): Collection
    {
        return $this->bookings()
            ->selectRaw('experiences.id, experiences.name, count(*) as bookings, sum(experience_bookings.checked_in) as checked_in, sum(experience_bookings.total_price) as gross')
            ->groupBy('experiences.id', 'experiences.name')
            ->orderByDesc('gross')
            ->get();
    }

    /** Gross per day, oldest first (both tickets and bookings). */
    public function daily(): Collection
    {
        $tickets = $this->tickets()
            ->selectRaw('date(attendees.created_at) as day, sum(attendees.total_price) as gross')
            ->groupBy('day')->pluck('gross', 'day');
        $bookings = $this->bookings()
            ->selectRaw('date(experience_bookings.created_at) as day, sum(experience_bookings.total_price) as gross')
            ->groupBy('day')->pluck('gross', 'day');

        return $tickets->keys()->merge($bookings->keys())->unique()->sort()->values()
            ->mapWithKeys(fn ($day) => [$day => (float) ($tickets[$day] ?? 0) + (float) ($bookings[$day] ?? 0)]);
    }

    /**
     * What a merchant can withdraw. Earnings are net of the platform fee; with
     * payout_after_end, only events/experiences that have ended count as available.
     */
    public function balance(): array
    {
        $feeRate  = self::feeRate();
        $afterEnd = config('aiyo.payout_after_end');
        $ended    = now();

        // Forfeited lipa mdogo mdogo deposits are earned and withdrawable straight away (the place was released)
        $forfeited = $this->forfeitedDeposits();
        $earned    = (float) $this->tickets()->sum('attendees.total_price') + (float) $this->bookings()->sum('experience_bookings.total_price') + $forfeited;

        $availableGross = $afterEnd
            ? (float) $this->tickets()->where(fn ($q) => $q->where('events.end_date', '<', $ended)
                    ->orWhere(fn ($q) => $q->whereNull('events.end_date')->where('events.date', '<', $ended)))
                ->sum('attendees.total_price')
              + (float) $this->bookings()->where(fn ($q) => $q->where('experiences.end_date', '<', $ended)
                    ->orWhere(fn ($q) => $q->whereNull('experiences.end_date')->where('experiences.start_date', '<', $ended)))
                ->sum('experience_bookings.total_price')
              + $forfeited
            : $earned;

        $paidOut   = (float) Payout::where('user_id', $this->user->id)->where('status', Payout::PAID)->sum('amount');
        $requested = (float) Payout::where('user_id', $this->user->id)->whereIn('status', [Payout::REQUESTED, Payout::APPROVED])->sum('amount');

        $earnedNet    = round($earned * (1 - $feeRate), 2);
        $availableNet = round($availableGross * (1 - $feeRate), 2);

        return [
            'earned'      => $earnedNet,                                        // all paid sales, after fee
            'pending'     => round($earnedNet - $availableNet, 2),              // events not ended yet
            'paid_out'    => $paidOut,
            'in_progress' => $requested,
            'withdrawable'=> max(0, round($availableNet - $paidOut - $requested, 2)),
        ];
    }
}
