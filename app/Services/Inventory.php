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
 *
 * Usage at checkout: lock() the items, check what's left, send the STK push,
 * create the pending MpesaStk row (which holds the stock), then release().
 */
class Inventory
{
    /** A pending STK push holds its items this long — longer than M-Pesa's prompt timeout. */
    public const HOLD_MINUTES = 5;

    /** Pending checkouts' item lists; loaded once per instance. */
    private ?array $holds = null;

    public function ticketsLeft(int $ticketId): ?int
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            return null;
        }

        $sold = (int) Attendee::where('ticket_id', $ticketId)->whereIn('payment_status', ['paid', 'partial'])->sum('qty');

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

    /**
     * Places left for an experience item: the tightest of the experience's total
     * capacity and the package's and category's capacity. Null = unlimited.
     */
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
     * Lock the stock of everything in a checkout. Keys are sorted so concurrent
     * checkouts always lock in the same order (no deadlock).
     *
     * @throws LockTimeoutException when another checkout holds an item for too long
     * @return Lock[]
     */
    public function lock(array $ticketIds, array $experienceIds): array
    {
        $keys = array_merge(
            array_map(fn ($id) => 'inventory:ticket:' . (int) $id, $ticketIds),
            array_map(fn ($id) => 'inventory:experience:' . (int) $id, $experienceIds),
        );
        $keys = array_values(array_unique($keys));
        sort($keys);

        $locks = [];
        try {
            foreach ($keys as $key) {
                // Auto-expires after 60s even if release() is never reached
                $lock = Cache::lock($key, 60);
                $lock->block(15);
                $locks[] = $lock;
            }
        } catch (LockTimeoutException $e) {
            $this->release($locks);
            throw $e;
        }

        $this->holds = null; // re-read holds now that we hold the locks

        return $locks;
    }

    /** @param Lock[] $locks */
    public function release(array $locks): void
    {
        foreach (array_reverse($locks) as $lock) {
            $lock->release();
        }
    }

    private function holds(): array
    {
        return $this->holds ??= MpesaStk::where('status', MpesaPaymentService::PENDING)
            ->where('created_at', '>=', now()->subMinutes(self::HOLD_MINUTES))
            ->whereNotNull('pending_data')
            ->pluck('pending_data')
            ->all();
    }
}
