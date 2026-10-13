<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Experience;
use App\Models\Ticket;
use Carbon\CarbonInterface;

/**
 * Lipa mdogo mdogo rules: who can pay in instalments, how much the deposit is and
 * when the balance is due.
 *
 * A cart can mix event tickets and experiences, as long as every item offers
 * instalments and everything is from the same organiser (one merchant receives the
 * deposit and handles refunds). The balance is due by the earliest item's due date.
 */
class Instalments
{
    /** Smallest instalment after the deposit, in KES (unless the balance is smaller). */
    public const MIN_INSTALMENT = 100;

    /**
     * The plan for a cart, or ['error' => reason] when instalments aren't possible.
     *
     * Ticket lines need ticket_id (+ subtotal for deposits); experience lines need
     * experience_id (+ subtotal).
     *
     * @return array{type:string,id:int,seller_id:int,name:string,percents:array,percent:int,due_date:CarbonInterface}|array{error:string}
     */
    public function planFor(array $ticketLines, array $experienceLines): array
    {
        if (!$ticketLines && !$experienceLines) {
            return ['error' => 'Your cart is empty.'];
        }

        // Every event and experience in the cart
        $sources = [];
        $eventIds = Ticket::whereIn('id', array_column($ticketLines, 'ticket_id'))->distinct()->pluck('event_id');
        foreach (Event::whereIn('id', $eventIds)->get() as $event) {
            $sources["event:{$event->id}"] = [$event, $event->date, 'event'];
        }
        foreach (Experience::whereIn('id', array_unique(array_map('intval', array_column($experienceLines, 'experience_id'))))->get() as $exp) {
            $sources["experience:{$exp->id}"] = [$exp, $exp->start_date, 'experience'];
        }

        if (!$sources) {
            return ['error' => 'Your cart is empty.'];
        }

        foreach ($sources as [$item, $start, $type]) {
            if (!$item->allow_instalments) {
                return ['error' => count($sources) > 1
                    ? "{$item->name} does not offer lipa mdogo mdogo, so the whole order must be paid in full."
                    : "This {$type} does not offer lipa mdogo mdogo."];
            }
            if (!$start) {
                return ['error' => "{$item->name} has no start date, so instalments are not available."];
            }
        }

        $sellers = collect($sources)->map(fn ($s) => (int) $s[0]->user_id)->unique();
        if ($sellers->count() > 1) {
            return ['error' => 'Lipa mdogo mdogo works when everything in your cart is from the same organiser. Pay the other items separately.'];
        }

        // The balance is due by the earliest item's due date; leave at least a day to pay it
        $due = collect($sources)->map(fn ($s) => $s[1]->copy()->subDays((int) $s[0]->instalment_due_days))->sort()->first();
        if ($due->lte(now()->addDay())) {
            return ['error' => 'It is too close to the start date for instalments. Please pay in full.'];
        }

        [$first, , $firstType] = reset($sources);

        return [
            'type'      => count($sources) === 1 ? $firstType : 'mixed',
            'id'        => $first->id,
            'seller_id' => $sellers->first(),
            'name'      => collect($sources)->map(fn ($s) => $s[0]->name)->implode(', '),
            'percents'  => collect($sources)->map(fn ($s) => (int) $s[0]->instalment_deposit_percent)->all(),
            'percent'   => (int) $first->instalment_deposit_percent,
            'due_date'  => $due,
        ];
    }

    /**
     * Deposit for the cart: each item's subtotal times its own deposit %, rounded up
     * to a whole shilling and never more than the total.
     */
    public function depositFor(array $plan, array $ticketLines, array $experienceLines, int $total): int
    {
        $eventOf = Ticket::whereIn('id', array_column($ticketLines, 'ticket_id'))->pluck('event_id', 'id');

        $deposit = 0.0;
        foreach ($ticketLines as $line) {
            $pct      = $plan['percents']['event:' . ($eventOf[$line['ticket_id']] ?? 0)] ?? $plan['percent'];
            $deposit += (float) $line['subtotal'] * $pct / 100;
        }
        foreach ($experienceLines as $line) {
            $pct      = $plan['percents']['experience:' . (int) $line['experience_id']] ?? $plan['percent'];
            $deposit += (float) ($line['subtotal'] ?? 0) * $pct / 100;
        }

        // Promo discounts are already in the line subtotals; rounding keeps it within the total
        return max(1, min($total, (int) ceil(round($deposit, 2))));
    }

    /** Single-percentage deposit (kept for direct callers). */
    public function deposit(int $total, int $percent): int
    {
        return max(1, (int) ceil($total * $percent / 100));
    }

    /** The deposit as a share of the total, for display ("30%"). */
    public function effectivePercent(int $deposit, int $total): int
    {
        return $total > 0 ? (int) round($deposit / $total * 100) : 0;
    }
}
