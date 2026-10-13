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
 * An instalment order covers a single event or a single experience, so it has one
 * merchant, one due date and one refund rule.
 */
class Instalments
{
    /** Smallest instalment after the deposit, in KES (unless the balance is smaller). */
    public const MIN_INSTALMENT = 100;

    /**
     * The plan for a cart, or ['error' => reason] when instalments aren't possible.
     *
     * @return array{type:string,id:int,seller_id:int,name:string,percent:int,due_date:CarbonInterface}|array{error:string}
     */
    public function planFor(array $ticketLines, array $experienceLines): array
    {
        if ($ticketLines && $experienceLines) {
            return ['error' => 'Lipa mdogo mdogo works for one event or one experience per order. Pay the rest separately.'];
        }

        if ($ticketLines) {
            $eventIds = Ticket::whereIn('id', array_column($ticketLines, 'ticket_id'))->distinct()->pluck('event_id');
            if ($eventIds->count() !== 1) {
                return ['error' => 'Lipa mdogo mdogo works for one event per order.'];
            }
            $source = Event::find($eventIds->first());
            $start  = $source?->date;
            $type   = 'event';
        } elseif ($experienceLines) {
            $ids = array_unique(array_map('intval', array_column($experienceLines, 'experience_id')));
            if (count($ids) !== 1) {
                return ['error' => 'Lipa mdogo mdogo works for one experience per order.'];
            }
            $source = Experience::find($ids[0]);
            $start  = $source?->start_date;
            $type   = 'experience';
        } else {
            return ['error' => 'Your cart is empty.'];
        }

        if (!$source || !$source->allow_instalments) {
            return ['error' => 'This ' . $type . ' does not offer lipa mdogo mdogo.'];
        }

        if (!$start) {
            return ['error' => 'This ' . $type . ' has no start date, so instalments are not available.'];
        }

        $due = $start->copy()->subDays((int) $source->instalment_due_days);

        // Leave at least a day to pay the balance
        if ($due->lte(now()->addDay())) {
            return ['error' => 'It is too close to the start date for instalments. Please pay in full.'];
        }

        return [
            'type'      => $type,
            'id'        => $source->id,
            'seller_id' => (int) $source->user_id,
            'name'      => $source->name,
            'percent'   => (int) $source->instalment_deposit_percent,
            'due_date'  => $due,
        ];
    }

    public function deposit(int $total, int $percent): int
    {
        return max(1, (int) ceil($total * $percent / 100));
    }
}
