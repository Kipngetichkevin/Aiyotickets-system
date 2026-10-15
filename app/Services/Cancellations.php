<?php

namespace App\Services;

use App\Jobs\NotifyBuyer;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\Order;
use App\Support\RefundPolicy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * When plans change: an organiser cancels or postpones, or a buyer asks for a refund
 * within the listing's policy. Cancelled tickets stop working at the gate, the money is
 * recorded as a refund due on the order (Admin → Organiser payouts → Refunds), and
 * buyers are told by email and SMS.
 */
class Cancellations
{
    /** Cancel an event or experience: every paid or part-paid place is refunded. */
    public function cancel(Event|Experience $item, string $reason): array
    {
        $result = DB::transaction(function () use ($item, $reason) {
            $item->forceFill(['status' => $item instanceof Event ? 'cancelled' : 'archived', 'cancelled_at' => now(), 'cancel_reason' => $reason])->save();

            return $this->refundPlaces($this->places($item), 'Cancelled by organiser: ' . $reason, wholeInstalmentOrders: true);
        });

        foreach ($result['orders'] as $orderId => $amount) {
            NotifyBuyer::dispatch($orderId, 'cancelled', [
                'name'   => $item->name,
                'reason' => $reason,
                'amount' => $amount,
            ])->afterCommit();
        }

        return $result;
    }

    /** Move to new dates; tickets stay valid and buyers may ask for a refund for a while. */
    public function postpone(Event|Experience $item, Carbon $start, ?Carbon $end, ?string $note): int
    {
        // No new end given: keep the same length, shifted to the new start
        $oldStart = $item instanceof Event ? $item->date : $item->start_date;
        if (!$end && $item->end_date) {
            $end = $oldStart ? $start->copy()->add($oldStart->diff($item->end_date)) : $start->copy()->addHours(6);
        }

        $item->forceFill($item instanceof Event
            ? ['date' => $start, 'end_date' => $end, 'postponed_at' => now()]
            : ['start_date' => $start, 'end_date' => $end, 'postponed_at' => now()])->save();

        $orderIds = $this->places($item)->pluck('order_id')->filter()->unique();
        foreach ($orderIds as $orderId) {
            NotifyBuyer::dispatch($orderId, 'postponed', [
                'name'     => $item->name,
                'when'     => $start->format('l d M Y, H:i'),
                'note'     => $note,
                'deadline' => now()->addDays(RefundPolicy::POSTPONED_DAYS)->format('d M Y'),
            ]);
        }

        return $orderIds->count();
    }

    /**
     * A buyer's refund request for an order: every place whose listing still allows
     * refunds and that hasn't been used at the gate. Lipa mdogo mdogo orders follow
     * their own rules (deposit kept), so they're not refundable this way.
     */
    public function requestRefund(Order $order): array
    {
        if ($order->payment_plan === 'instalments') {
            return ['amount' => 0, 'message' => 'Lipa mdogo mdogo bookings can\'t be refunded here. Please contact us.'];
        }

        $eligible = $this->refundablePlaces($order);
        if ($eligible->isEmpty()) {
            return ['amount' => 0, 'message' => 'Nothing on this order can be refunded any more.'];
        }

        $result = DB::transaction(fn () => $this->refundPlaces($eligible, 'Refund requested by buyer', wholeInstalmentOrders: false));
        $amount = $result['orders'][$order->id] ?? 0;

        NotifyBuyer::dispatch($order->id, 'refund_requested', ['amount' => $amount])->afterCommit();

        return ['amount' => $amount, 'message' => 'Refund of KES ' . number_format($amount) . ' requested. It will be sent to your M-Pesa within a few working days.'];
    }

    /** Places on this order a buyer could still get refunded. */
    public function refundablePlaces(Order $order): Collection
    {
        if ($order->payment_plan === 'instalments') {
            return collect();
        }

        $tickets = Attendee::with('ticket.event')->where('order_id', $order->id)->where('payment_status', 'paid')
            ->where(fn ($q) => $q->where('checked_in', false)->orWhereNull('checked_in'))->get()
            ->filter(fn ($a) => $a->ticket?->event && RefundPolicy::refundableNow($a->ticket->event));

        $bookings = ExperienceBooking::with('experience')->where('order_id', $order->id)->where('payment_status', 'paid')
            ->where(fn ($q) => $q->where('checked_in', false)->orWhereNull('checked_in'))->get()
            ->filter(fn ($b) => $b->experience && RefundPolicy::refundableNow($b->experience));

        return $tickets->concat($bookings)->values();
    }

    /** Paid and part-paid places of an event or experience. */
    private function places(Event|Experience $item): Collection
    {
        $query = $item instanceof Event
            ? Attendee::whereHas('ticket', fn ($q) => $q->where('event_id', $item->id))
            : ExperienceBooking::where('experience_id', $item->id);

        return $query->whereIn('payment_status', ['paid', 'partial'])->get();
    }

    /**
     * Cancel the places and record what each order is owed: the price of each place,
     * or everything paid so far on a lipa mdogo mdogo order.
     *
     * @return array{orders: array<int, float>, places: int, total: float}
     */
    private function refundPlaces(Collection $places, string $reason, bool $wholeInstalmentOrders): array
    {
        $owed = [];

        foreach ($places->groupBy('order_id') as $orderId => $group) {
            foreach ($group as $place) {
                $place->forceFill(['payment_status' => 'cancelled', 'status' => 'cancelled'])->save();
            }

            $order = $orderId ? Order::lockForUpdate()->find($orderId) : null;
            if (!$order) {
                continue;
            }

            $amount = $wholeInstalmentOrders && $order->payment_plan === 'instalments'
                ? (float) $order->amount_paid
                : (float) $group->sum('total_price');

            $stillActive = Attendee::where('order_id', $order->id)->whereIn('payment_status', ['paid', 'partial'])->exists()
                || ExperienceBooking::where('order_id', $order->id)->whereIn('payment_status', ['paid', 'partial'])->exists();

            // A new amount owed reopens the refund, even if an earlier one was sent
            $order->forceFill(['refund' => round((float) $order->refund + $amount, 2), 'refund_reason' => $reason, 'refunded_at' => null]);
            if (!$stillActive) {
                $order->forceFill(['status' => 'cancelled'] + ($order->payment_plan === 'instalments' ? ['balance_due' => 0] : []));
            }
            $order->save();

            $owed[$order->id] = $amount;
        }

        return ['orders' => $owed, 'places' => $places->count(), 'total' => array_sum($owed)];
    }
}
