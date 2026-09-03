<?php

namespace App\Mail;

use Illuminate\Support\Facades\Mail;

/** Preserves existing email templates while routing delivery through Laravel's queue. */
#[\AllowDynamicProperties]
final class PageMailer
{
    public const ENCRYPTION_STARTTLS = 'tls';

    public const ENCRYPTION_SMTPS = 'ssl';

    public const DEBUG_OFF = 0;

    public const DEBUG_SERVER = 2;

    public string $Subject = '';

    public string $Body = '';

    public string $AltBody = '';

    public string $ErrorInfo = '';

    private array $recipients = [];

    private array $attachments = [];

    private array $replies = [];

    public function __construct(...$args) {}

    public function isSMTP(): void {}

    public function isHTML(bool $html = true): void {}

    public function setFrom(string $address, string $name = '', ...$args): void {}

    public function addAddress(string $email, string $name = ''): void
    {
        validator(['email' => $email], ['email' => 'required|email'])->validate();
        $this->recipients[] = [$email, $name];
    }

    public function addBCC(string $email, string $name = ''): void
    {
        $this->addAddress($email, $name);
    }

    public function addReplyTo(string $email, string $name = ''): void
    {
        $this->replies[] = [$email, $name];
    }

    public function addAttachment(string $path, string $name = '', ...$args): void
    {
        if (! is_file($path)) {
            throw new \RuntimeException('Attachment is unavailable.');
        } $this->attachments[] = [$path, $name];
    }

    public function clearAddresses(): void
    {
        $this->recipients = [];
    }

    public function clearAllRecipients(): void
    {
        $this->recipients = [];
    }

    public function clearAttachments(): void
    {
        $this->attachments = [];
    }

    public function smtpClose(): void {}

    public function send(): bool
    {
        if (! $this->recipients) {
            throw new \RuntimeException('No email recipient.');
        }
        foreach ($this->recipients as [$email,$name]) {
            $mail = new PreservedNotification($this->Subject, $this->Body, $this->attachments, $this->replies);
            Mail::to($email, $name)->queue($mail);
        }

        return true;
    }
}
