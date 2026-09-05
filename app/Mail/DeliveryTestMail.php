<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class DeliveryTestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'GradConn email test');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.delivery-test');
    }
}
