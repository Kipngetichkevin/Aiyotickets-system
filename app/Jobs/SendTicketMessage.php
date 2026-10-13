<?php

namespace App\Jobs;

use App\Helpers\SystemHelper;
use App\Http\Controllers\TicketViewController;
use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\Order;
use App\Services\Messaging\AfricasTalkingSms;
use App\Services\Messaging\WhatsAppCloud;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Delivers a paid order's tickets on one channel ('sms' or 'whatsapp').
 * One job per channel, so a WhatsApp retry never re-sends the SMS.
 */
class SendTicketMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public Order $order, public string $channel, public string $phone)
    {
    }

    public function handle(AfricasTalkingSms $sms, WhatsAppCloud $whatsapp): void
    {
        $items = $this->items();
        if ($items === []) {
            return;
        }

        $link = TicketViewController::linkFor($this->order);

        if ($this->channel === 'sms') {
            $codes = implode(', ', array_column($items, 'code'));
            $sms->send($this->phone, SystemHelper::appName() . ': payment received for ' . $items[0]['title']
                . (count($items) > 1 ? ' (' . count($items) . ' tickets)' : '')
                . ". Code(s): {$codes}. Show at the gate or open: {$link}");
            return;
        }

        foreach ($items as $item) {
            $whatsapp->sendTicket($this->phone, TicketViewController::qrLinkFor($item['code']), [
                $item['name'], $item['title'], $item['code'], $link,
            ]);
        }
    }

    private function items(): array
    {
        $items = [];
        foreach (Attendee::with('ticket.event')->where('order_id', $this->order->id)->get() as $a) {
            $items[] = ['title' => $a->ticket?->event?->name ?? 'your event', 'code' => $a->ensureQrCode(), 'name' => $a->name];
        }
        foreach (ExperienceBooking::with('experience')->where('order_id', $this->order->id)->get() as $b) {
            $items[] = ['title' => $b->experience?->name ?? 'your experience', 'code' => $b->qr_code, 'name' => $b->name];
        }
        return $items;
    }
}
