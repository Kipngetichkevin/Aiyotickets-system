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

/** Receipt for a lipa mdogo mdogo deposit or instalment, with the remaining balance. */
class InstalmentReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public float $amount)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment received — balance KES ' . number_format($this->order->balance_due) . ' | ' . SystemHelper::appName(),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.instalment-received', with: ['appName' => SystemHelper::appName()]);
    }
}
