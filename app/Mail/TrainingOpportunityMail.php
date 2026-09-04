<?php

namespace App\Mail;

use App\Models\Training;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TrainingOpportunityMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Training $training, public User $recipient)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Training Opportunity Available');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.training-opportunity', text: 'mail.training-opportunity-text');
    }
}
