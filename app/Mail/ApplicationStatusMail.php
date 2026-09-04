<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ApplicationStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application, public string $action, public string $customMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->action === 'accept' ? 'Application accepted' : 'Application selected for interview');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.application-status');
    }
}
