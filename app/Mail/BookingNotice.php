<?php

namespace App\Mail;

use App\Helpers\SystemHelper;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A change to a buyer's booking: cancelled, postponed, or their refund request received. */
class BookingNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $kind, public array $details = [])
    {
    }

    public function envelope(): Envelope
    {
        $name = $this->details['name'] ?? 'your booking';

        return new Envelope(subject: match ($this->kind) {
            'cancelled' => "Cancelled: {$name} — you'll be refunded",
            'postponed' => "New date: {$name}",
            default     => 'Refund requested — order ' . $this->order->order_no,
        } . ' | ' . SystemHelper::appName());
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking-notice', with: ['appName' => SystemHelper::appName()]);
    }
}
