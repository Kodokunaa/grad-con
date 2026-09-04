<?php

namespace App\Mail;

use App\Models\JobOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OfferAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public JobOffer $offer) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Job offer accepted - '.$this->offer->alumni->fullname);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.offer-accepted');
    }
}
