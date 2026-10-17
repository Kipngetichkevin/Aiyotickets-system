<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperiencePackage;
use App\Models\MpesaStk;
use App\Models\Ticket;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

/**
 * Ticket and experience stock, counting both sales and checkouts still waiting on
 * M-Pesa, so two buyers can't both pay for the last place.
 */
class Inventory
{
    /** A pending STK push holds its items this long — longer than M-Pesa's prompt timeout. */
    public const HOLD_MINUTES = 5;

    /** Cached hold list — avoid re-querying on every availability check. */
    private const HOLDS_CACHE_KEY = 'inventory:holds';
    private const HOLDS_CACHE_SECONDS = 3;

    /** Pending checkouts' item lists; loaded once per instance. */
    private ?array $holds = null;

    public function ticketsLeft(int $ticketId): ?int
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            return null;
        }

        $sold = (int) Attendee::where('ticket_id', $ticketId)
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('qty');

        $held = 0;
        foreach ($this->holds() as $hold) {
            foreach ($hold['cart_items'] ?? [] as $item) {
                if ((int) ($item['ticket_id'] ?? 0) === $ticketId) {
                    $held += (int) ($item['qty'] ?? 0);
                }
            }
        }

        return max(0, (int) $ticket->quantity - $sold - $held);
    }

    public function experienceSpotsLeft(int $experienceId, ?int $packageId, ?int $categoryId): ?int
    {
        $held = ['experience' => 0, 'package' => 0, 'category' => 0];

        foreach ($this->holds() as $hold) {
            foreach ($hold['experience_items'] ?? [] as $item) {
                $qty = (int) ($item['qty'] ?? 0);
                if ((int) ($item['experience_id'] ?? 0) === $experienceId) {
                    $held['experience'] += $qty;
                }
                if ($packageId && (int) ($item['package_id'] ?? 0) === $packageId) {
                    $held['package'] += $qty;
                }
                if ($categoryId && (int) ($item['category_id'] ?? 0) === $categoryId) {
                    $held['category'] += $qty;
                }
            }
        }

        $limits = [];

        $spots = Experience::find($experienceId)?->availableSpots();
        if ($spots !== null) {
            $limits[] = $spots - $held['experience'];
        }

        if ($packageId && ($spots = ExperiencePackage::find($packageId)?->availableSpots()) !== null) {
            $limits[] = $spots - $held['package'];
        }

        if ($categoryId && ($spots = ExperienceCategory::find($categoryId)?->availableSpots()) !== null) {
            $limits[] = $spots - $held['category'];
        }

        return $limits ? max(0, min($limits)) : null;
    }

    /**
     * @throws LockTimeoutException
     * @return Lock[]
     */
    public function lock(array $ticketIds, array $experienceIds, array $promoCodeIds = []): array
    {
        $keys = array_merge(
            array_map(fn ($id) => 'inventory:ticket:' . (int) $id, $ticketIds),
            array_map(fn ($id) => 'inventory:experience:' . (int) $id, $experienceIds),
            array_map(fn ($id) => 'inventory:promo:' . (int) $id, $promoCodeIds),
        );
        $keys = array_values(array_unique($keys));
        sort($keys);

        $locks = [];
        try {
            foreach ($keys as $key) {
                $lock = Cache::lock($key, 150);
                $lock->block(15);
                $locks[] = $lock;
            }
        } catch (LockTimeoutException $e) {
            $this->release($locks);
            throw $e;
        }

        $this->forgetHolds();

        return $locks;
    }

    /** Places of a ticket type held by checkouts still waiting on M-Pesa. */
    public function ticketsHeld(int $ticketId): int
    {
        return (int) collect($this->holds())->flatMap(fn ($h) => $h['cart_items'] ?? [])
            ->where('ticket_id', $ticketId)->sum('qty');
    }

    /** Places of an experience (optionally one package) held by checkouts still waiting on M-Pesa. */
    public function experienceHeld(int $experienceId, ?int $packageId = null): int
    {
        return (int) collect($this->holds())->flatMap(fn ($h) => $h['experience_items'] ?? [])
            ->filter(fn ($i) => (int) ($i['experience_id'] ?? 0) === $experienceId && (!$packageId || (int) ($i['package_id'] ?? 0) === $packageId))
            ->sum('qty');
    }

    /** Uses of a promo code by checkouts still waiting on M-Pesa. */
    public function promoUsesHeld(int $promoCodeId): int
    {
        return count(array_filter($this->holds(), fn ($hold) => (int) ($hold['promo_code_id'] ?? 0) === $promoCodeId));
    }

    /** @param Lock[] $locks */
    public function release(array $locks): void
    {
        foreach (array_reverse($locks) as $lock) {
            $lock->release();
        }
    }

    /** Force the next holds() call to re-read (e.g. after a payment is fulfilled). */
    public function forgetHolds(): void
    {
        $this->holds = null;
        Cache::forget(self::HOLDS_CACHE_KEY);
    }

    private function holds(): array
    {
        if ($this->holds !== null) {
            return $this->holds;
        }

        return $this->holds = Cache::remember(
            self::HOLDS_CACHE_KEY,
            now()->addSeconds(self::HOLDS_CACHE_SECONDS),
            fn () => MpesaStk::where('status', MpesaPaymentService::PENDING)
                ->where('created_at', '>=', now()->subMinutes(self::HOLD_MINUTES))
                ->whereNotNull('pending_data')
                ->pluck('pending_data')
                ->all()
        );
    }
}