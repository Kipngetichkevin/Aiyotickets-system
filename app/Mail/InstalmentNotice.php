<?php

namespace App\Mail;

use App\Helpers\SystemHelper;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Lipa mdogo mdogo reminder ('reminder') or cancellation notice ('lapsed'). */
class InstalmentNotice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $kind, public int $daysLeft = 0)
    {
    }

    public function envelope(): Envelope
    {
        $subject = $this->kind === 'lapsed'
            ? 'Booking cancelled — balance not paid'
            : "Reminder: KES " . number_format($this->order->balance_due) . " due in {$this->daysLeft} " . ($this->daysLeft === 1 ? 'day' : 'days');

        return new Envelope(subject: $subject . ' | ' . SystemHelper::appName());
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.instalment-notice', with: ['appName' => SystemHelper::appName()]);
    }
}
