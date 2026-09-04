<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AlumniAccountApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $alumni) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Account Approval - GradConn');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.alumni-account-approved');
    }
}
