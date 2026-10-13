<?php

namespace App\Console\Commands;

use App\Helpers\SystemHelper;
use App\Mail\InstalmentNotice;
use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\Order;
use App\Services\Messaging\AfricasTalkingSms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Lipa mdogo mdogo housekeeping (hourly): reminders before the due date, and
 * cancelling plans that missed it (deposit kept, later payments refunded).
 */
class ProcessInstalments extends Command
{
    /** Days before the due date at which a reminder is sent (each once). */
    public const REMINDER_DAYS = [14, 7, 3, 1];

    protected $signature = 'instalments:process';

    protected $description = 'Send lipa mdogo mdogo reminders and cancel plans past their due date';

    public function handle(): int
    {
        $reminded = $lapsed = 0;

        foreach (Order::where('status', 'partial')->where('due_date', '>', now())->get() as $order) {
            if ($this->remind($order)) {
                $reminded++;
            }
        }

        foreach (Order::where('status', 'partial')->where('due_date', '<=', now())->pluck('id') as $id) {
            if ($this->lapse($id)) {
                $lapsed++;
            }
        }

        $this->info("Sent {$reminded} reminder(s), cancelled {$lapsed} plan(s).");

        return self::SUCCESS;
    }

    private function remind(Order $order): bool
    {
        $daysLeft = (int) ceil(now()->diffInHours($order->due_date) / 24);
        $sent     = $order->reminders_sent ?? [];

        // The nearest threshold reached and not yet sent (at most one reminder per run)
        $due = collect(self::REMINDER_DAYS)->filter(fn ($d) => $daysLeft <= $d && !in_array($d, $sent, true))->min();
        if ($due === null) {
            return false;
        }

        // Mark every larger threshold as covered too, so a late start doesn't send a burst
        $order->update(['reminders_sent' => array_values(array_unique(array_merge(
            $sent, array_filter(self::REMINDER_DAYS, fn ($d) => $d >= $due)
        )))]);

        $this->notify($order, new InstalmentNotice($order, 'reminder', max(1, $daysLeft)),
            SystemHelper::appName() . ": KES " . number_format($order->balance_due) . " left on order {$order->order_no}, due "
            . $order->due_date->format('d M Y') . '. Pay any amount: ' . \App\Http\Controllers\InstalmentController::payLinkFor($order));

        return true;
    }

    private function lapse(int $orderId): bool
    {
        $order = DB::transaction(function () use ($orderId) {
            $order = Order::lockForUpdate()->find($orderId);
            if (!$order || $order->status !== 'partial') {
                return null;   // paid in the meantime
            }

            // Deposit kept; anything paid after it is refunded
            $refundable = max(0, (float) $order->amount_paid - (float) $order->deposit_amount);

            $order->update([
                'status'    => 'lapsed',
                'lapsed_at' => now(),
                'refund'    => (float) $order->refund + $refundable,
            ]);

            // Release the held places
            Attendee::where('order_id', $order->id)->update(['payment_status' => 'overdue']);
            ExperienceBooking::where('order_id', $order->id)->update(['payment_status' => 'overdue']);

            return $order;
        });

        if (!$order) {
            return false;
        }

        $this->notify($order, new InstalmentNotice($order, 'lapsed'),
            SystemHelper::appName() . ": order {$order->order_no} was cancelled because the balance wasn't paid by "
            . $order->due_date->format('d M Y') . '.' . ($order->refund > 0 ? ' KES ' . number_format($order->refund) . ' will be refunded.' : ''));

        return true;
    }

    private function notify(Order $order, InstalmentNotice $mail, string $sms): void
    {
        try {
            if ($order->email) {
                Mail::to($order->email)->queue($mail);
            }
            $phone = Attendee::where('order_id', $order->id)->value('phone') ?? ExperienceBooking::where('order_id', $order->id)->value('phone');
            if ($phone && app(AfricasTalkingSms::class)->enabled()) {
                app(AfricasTalkingSms::class)->send($phone, $sms);
            }
        } catch (\Throwable $e) {
            Log::error('Instalment notice failed: ' . $e->getMessage(), ['order_id' => $order->id]);
        }
    }
}
