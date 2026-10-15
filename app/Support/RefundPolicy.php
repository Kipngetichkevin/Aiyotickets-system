<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Experience;
use Illuminate\Support\Carbon;

/**
 * Refund policy chosen per event or experience, shown on the listing, at checkout and on
 * tickets. Whatever the policy, buyers are refunded when the organiser cancels, and may
 * ask for a refund for POSTPONED_DAYS after a postponement.
 */
class RefundPolicy
{
    public const OPTIONS = [
        'none'     => 'No refunds',
        '7_days'   => 'Refunds up to 7 days before',
        '48_hours' => 'Refunds up to 48 hours before',
    ];

    public const POSTPONED_DAYS = 14;

    public static function start(Event|Experience $item): ?Carbon
    {
        return $item instanceof Event ? $item->date : $item->start_date;
    }

    /** Until when a buyer may ask for their money back, or null if they can't. */
    public static function deadline(Event|Experience $item): ?Carbon
    {
        $start = self::start($item);

        $byPolicy = match ($item->refund_policy) {
            '7_days'   => $start?->copy()->subDays(7),
            '48_hours' => $start?->copy()->subHours(48),
            default    => null,
        };
        $byPostponement = $item->postponed_at?->copy()->addDays(self::POSTPONED_DAYS);

        return collect([$byPolicy, $byPostponement])->filter()->max();
    }

    public static function refundableNow(Event|Experience $item): bool
    {
        $deadline = self::deadline($item);

        return !$item->cancelled_at && $deadline && $deadline->isFuture();
    }

    /** One sentence for buyers. */
    public static function text(Event|Experience $item): string
    {
        $start = self::start($item);
        $base  = match ($item->refund_policy) {
            '7_days'   => 'Full refund if you cancel at least 7 days before' . ($start ? ' (by ' . $start->copy()->subDays(7)->format('d M') . ')' : '') . '.',
            '48_hours' => 'Full refund if you cancel at least 48 hours before' . ($start ? ' (by ' . $start->copy()->subHours(48)->format('d M, H:i') . ')' : '') . '.',
            default    => 'Tickets are non-refundable.',
        };

        return $base . ' If the organiser cancels, you get your money back; if it is postponed, you can ask for a refund within ' . self::POSTPONED_DAYS . ' days.';
    }
}
