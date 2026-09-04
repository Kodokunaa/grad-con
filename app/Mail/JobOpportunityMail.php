<?php

namespace App\Mail;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class JobOpportunityMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Job $job, public User $recipient, public ?string $customSubject = null, public ?string $customMessage = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->customSubject ?: 'New Job Opportunity: '.$this->job->title);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.job-opportunity');
    }
}
