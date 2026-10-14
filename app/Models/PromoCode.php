<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A merchant's discount code: percent or fixed KES off their tickets and experiences,
 * optionally limited to one event or experience, a number of uses, a date window and
 * a minimum number of tickets/places.
 */
class PromoCode extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'experience_id', 'code', 'type', 'value', 'min_tickets',
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

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }

    /** What the code covers, for display. */
    public function targetName(): string
    {
        return $this->event?->name ?? $this->experience?->name ?? 'All events & experiences';
    }

    /**
     * Apply the code to a cart. Ticket lines: [ticket_id, qty, price, subtotal];
     * experience lines: [experience_id, qty, price, subtotal] (as built at checkout).
     *
     * A code covers one event, one experience, or (neither set) every event and
     * experience of the merchant who owns it.
     *
     * Returns ['lines' => tickets, 'exp_lines' => experiences, both with discounted
     * 'price'/'subtotal', 'discount' => KES] or ['error' => reason].
     */
    public function apply(array $lines, array $expLines = []): array
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
        if ($this->isUsedUp()) {
            return ['error' => 'This promo code has been fully used.'];
        }

        // Which lines the code covers
        $eventOf  = Ticket::whereIn('id', array_column($lines, 'ticket_id'))->pluck('event_id', 'id');
        $ownEvent = fn ($eventId) => !$this->experience_id && ($this->event_id
            ? (int) $eventId === (int) $this->event_id
            : Event::whereKey($eventId)->where('user_id', $this->user_id)->exists());
        $ownExperience = fn ($expId) => !$this->event_id && ($this->experience_id
            ? (int) $expId === (int) $this->experience_id
            : Experience::whereKey($expId)->where('user_id', $this->user_id)->exists());

        $eligible = [];   // [[group, index], ...]
        foreach ($lines as $i => $line) {
            if (($eventId = $eventOf[$line['ticket_id']] ?? null) && $ownEvent($eventId)) {
                $eligible[] = ['lines', $i];
            }
        }
        foreach ($expLines as $i => $line) {
            if ($ownExperience($line['experience_id'] ?? 0)) {
                $eligible[] = ['exp_lines', $i];
            }
        }

        if (!$eligible) {
            return ['error' => 'This promo code does not apply to the items in your cart.'];
        }

        $groups        = ['lines' => $lines, 'exp_lines' => $expLines];
        $eligibleQty   = array_sum(array_map(fn ($e) => (int) ($groups[$e[0]][$e[1]]['qty'] ?? 1), $eligible));
        $eligibleTotal = array_sum(array_map(fn ($e) => (float) $groups[$e[0]][$e[1]]['subtotal'], $eligible));

        if ($eligibleQty < $this->min_tickets) {
            return ['error' => "This promo code needs at least {$this->min_tickets} tickets or places."];
        }

        $discount = $this->type === 'percent'
            ? $eligibleTotal * min(100, (float) $this->value) / 100
            : min((float) $this->value, $eligibleTotal);

        // Spread the discount over the covered lines so each records what was paid for it
        foreach ($eligible as [$group, $i]) {
            $line  = &$groups[$group][$i];
            $share = $eligibleTotal > 0 ? $line['subtotal'] / $eligibleTotal : 0;
            $qty   = max(1, (int) ($line['qty'] ?? 1));

            $line['original_price'] = $line['price'];
            $line['subtotal']       = round($line['subtotal'] - $discount * $share, 2);
            $line['price']          = round($line['subtotal'] / $qty, 2);
            unset($line);
        }

        return ['lines' => $groups['lines'], 'exp_lines' => $groups['exp_lines'], 'discount' => round($discount, 2)];
    }

    /** Paid uses plus checkouts still waiting on M-Pesa have reached the limit. */
    public function isUsedUp(): bool
    {
        return $this->max_uses !== null
            && $this->used_count + app(\App\Services\Inventory::class)->promoUsesHeld($this->id) >= $this->max_uses;
    }

    public function label(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format($this->value, 2), '0'), '.') . '% off'
            : 'KES ' . number_format($this->value) . ' off';
    }
}
