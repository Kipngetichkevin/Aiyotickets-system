<?php

namespace App\Mail;

use App\Helpers\SystemHelper;
use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\Order;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once per paid order: every ticket and experience booking with its gate QR code.
 */
class TicketsIssued extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your tickets — ' . $this->order->order_no . ' | ' . SystemHelper::appName(),
        );
    }

    public function content(): Content
    {
        $items = [];

        foreach (Attendee::with('ticket.event')->where('order_id', $this->order->id)->get() as $a) {
            $event   = $a->ticket?->event;
            $items[] = [
                'title'    => $event?->name ?? 'Event',
                'subtitle' => $a->ticket?->name,
                'when'     => $event?->date ? \Carbon\Carbon::parse($event->date)->format('D, d M Y · H:i') : null,
                'where'    => $event?->location,
                'name'     => $a->name,
                'code'     => $a->ensureQrCode(),
            ];
        }

        foreach (ExperienceBooking::with(['experience', 'package', 'category'])->where('order_id', $this->order->id)->get() as $b) {
            $items[] = [
                'title'    => $b->experience?->name ?? 'Experience',
                'subtitle' => trim(($b->package?->name ?? '') . ($b->category ? ' · ' . $b->category->name : '')),
                'when'     => $b->experience?->start_date?->format('D, d M Y'),
                'where'    => $b->experience?->location,
                'name'     => $b->name,
                'code'     => $b->qr_code,
            ];
        }

        foreach ($items as &$item) {
            // base64 keeps the mailable JSON-serializable on the queue; decoded when embedded
            $item['qr_base64'] = base64_encode((new PngWriter())->write(new QrCode($item['code']))->getString());
        }

        return new Content(
            markdown: 'mail.tickets-issued',
            with: ['order' => $this->order, 'items' => $items, 'appName' => SystemHelper::appName()],
        );
    }
}
