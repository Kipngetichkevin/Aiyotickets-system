<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A merchant's discount code for their tickets: percent or fixed KES off, optionally
 * limited to one event, a number of uses, a date window and a minimum ticket count.
 */
class PromoCode extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'code', 'type', 'value', 'min_tickets',
        'max_uses', 'used_count', 'starts_at', 'ends_at', 'is_active',
    ];

    // Same defaults as the table, so a freshly created code behaves like a loaded one
    protected $attributes = [
        'is_active'   => true,
        'min_tickets' => 1,
        'used_count'  => 0,
    ];

    protected $casts = [
        'value'     => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public static function findByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : self::where('code', $code)->first();
    }

    /**
     * Apply the code to cart lines ([ticket_id, qty, price, ...] as built at checkout).
     * Returns ['lines' => lines with discounted 'price'/'subtotal', 'discount' => KES]
     * or ['error' => reason] when the code can't be used for this cart.
     */
    public function apply(array $lines): array
    {
        if (!$this->is_active) {
            return ['error' => 'This promo code is no longer active.'];
        }
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['error' => 'This promo code is not valid yet.'];
        }
        if ($this->ends_at && now()->gt($this->ends_at)) {
            return ['error' => 'This promo code has expired.'];
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return ['error' => 'This promo code has been fully used.'];
        }

        // Tickets this code covers: one event, or any event of the merchant
        $eventIds = Ticket::whereIn('id', array_column($lines, 'ticket_id'))->pluck('event_id', 'id');
        $ownEvent = fn ($eventId) => $this->event_id
            ? (int) $eventId === (int) $this->event_id
            : Event::whereKey($eventId)->where('user_id', $this->user_id)->exists();

        $eligible = [];
        foreach ($lines as $i => $line) {
            $eventId = $eventIds[$line['ticket_id']] ?? null;
            if ($eventId && $ownEvent($eventId)) {
                $eligible[] = $i;
            }
        }

        if (!$eligible) {
            return ['error' => 'This promo code does not apply to the tickets in your cart.'];
        }

        $eligibleQty   = array_sum(array_map(fn ($i) => $lines[$i]['qty'], $eligible));
        $eligibleTotal = array_sum(array_map(fn ($i) => $lines[$i]['subtotal'], $eligible));

        if ($eligibleQty < $this->min_tickets) {
            return ['error' => "This promo code needs at least {$this->min_tickets} tickets."];
        }

        $discount = $this->type === 'percent'
            ? $eligibleTotal * min(100, (float) $this->value) / 100
            : min((float) $this->value, $eligibleTotal);

        // Spread the discount over the eligible lines so each ticket records what was paid for it
        foreach ($eligible as $i) {
            $share = $eligibleTotal > 0 ? $lines[$i]['subtotal'] / $eligibleTotal : 0;
            $lineDiscount = $discount * $share;

            $lines[$i]['original_price'] = $lines[$i]['price'];
            $lines[$i]['subtotal']       = round($lines[$i]['subtotal'] - $lineDiscount, 2);
            $lines[$i]['price']          = round($lines[$i]['subtotal'] / $lines[$i]['qty'], 2);
        }

        return ['lines' => $lines, 'discount' => round($discount, 2)];
    }

    public function label(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format($this->value, 2), '0'), '.') . '% off'
            : 'KES ' . number_format($this->value) . ' off';
    }
}
