<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class PreservedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $heading, public string $markup, public string $plainText = '', public array $files = [], public array $replies = [], public ?array $sender = null)
    {
        $this->afterCommit();
    }

    public function build(): static
    {
        $this->subject($this->heading)->html($this->markup);
        if ($this->plainText !== '') {
            $this->text('mail.plain', ['content' => $this->plainText]);
        }
        if ($this->sender) {
            $this->from($this->sender[0], $this->sender[1]);
        }
        foreach ($this->files as [$path,$name]) {
            $this->attach($path, $name !== '' ? ['as' => $name] : []);
        }
        foreach ($this->replies as [$email,$name]) {
            $this->replyTo($email, $name);
        }

        return $this;
    }
}
