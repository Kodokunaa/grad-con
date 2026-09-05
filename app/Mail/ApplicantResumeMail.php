<?php

namespace App\Mail;

use App\Models\JobApplication;
use App\Support\PrivateUploads;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ApplicantResumeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public JobApplication $application)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Applicant Resume - '.$this->application->alumni->fullname);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.applicant-resume');
    }

    public function attachments(): array
    {
        return [Attachment::fromStorageDisk(PrivateUploads::diskName(), PrivateUploads::path('resumes', $this->application->resume_file))
            ->as(basename($this->application->resume_file))];
    }
}
