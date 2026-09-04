<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class InterviewScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Interview $interview) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Interview invitation');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.interview-scheduled');
    }
}
