<?php

namespace App\Mail;

use App\Helpers\SystemHelper;
use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** To the organiser: their application was approved or rejected, or their account suspended. */
class OrganiserApplicationDecided extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Merchant $merchant)
    {
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->merchant->status) {
            Merchant::APPROVED  => "You're approved to sell on " . SystemHelper::appName(),
            Merchant::SUSPENDED => 'Your organiser account has been suspended',
            default             => 'About your organiser application',
        };

        return new Envelope(subject: $subject . ' | ' . SystemHelper::appName());
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.organiser-decided', with: ['appName' => SystemHelper::appName()]);
    }
}
