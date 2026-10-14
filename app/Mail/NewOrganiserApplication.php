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

/** To admins: an organiser application is waiting for review. */
class NewOrganiserApplication extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Merchant $merchant)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New organiser application: ' . $this->merchant->name . ' | ' . SystemHelper::appName());
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.organiser-application', with: ['appName' => SystemHelper::appName()]);
    }
}
