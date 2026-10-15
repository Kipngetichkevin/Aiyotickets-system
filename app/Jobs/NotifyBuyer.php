<?php

namespace App\Jobs;

use App\Helpers\SystemHelper;
use App\Http\Controllers\TicketViewController;
use App\Mail\BookingNotice;
use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\Order;
use App\Models\User;
use App\Services\Messaging\AfricasTalkingSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Tells a buyer their booking changed: 'cancelled', 'postponed' or 'refund_requested'.
 * Email always; SMS too when Africa's Talking is set up. One job per order, so cancelling
 * a big event doesn't hold up the organiser's request.
 */
class NotifyBuyer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $orderId, public string $kind, public array $details = [])
    {
    }

    public function handle(AfricasTalkingSms $sms): void
    {
        $order = Order::find($this->orderId);
        if (!$order) {
            return;
        }

        if ($email = $order->email ?: User::whereKey($order->user_id)->value('email')) {
            Mail::to($email)->send(new BookingNotice($order, $this->kind, $this->details));
        }

        $phone = Attendee::where('order_id', $order->id)->value('phone') ?: ExperienceBooking::where('order_id', $order->id)->value('phone');
        if ($phone && $sms->enabled()) {
            try {
                $sms->send($phone, SystemHelper::appName() . ': ' . $this->smsText($order));
            } catch (\Throwable $e) {
                Log::warning('Booking notice SMS failed: ' . $e->getMessage(), ['order_id' => $order->id]);
            }
        }
    }

    private function smsText(Order $order): string
    {
        $d = $this->details;

        return match ($this->kind) {
            'cancelled' => "{$d['name']} has been cancelled. KES " . number_format($d['amount'] ?? 0) . " will be refunded to your M-Pesa (order {$order->order_no}).",
            'postponed' => "{$d['name']} has moved to {$d['when']}. Your tickets are still valid: " . TicketViewController::linkFor($order) . " Can't make it? Request a refund there by {$d['deadline']}.",
            default     => 'refund of KES ' . number_format($d['amount'] ?? 0) . " requested for order {$order->order_no}. It will be sent to your M-Pesa shortly.",
        };
    }
}
