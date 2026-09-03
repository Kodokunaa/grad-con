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

    public function __construct(public string $heading, public string $markup, public array $files = [], public array $replies = [])
    {
        $this->afterCommit();
    }

    public function build(): static
    {
        $this->subject($this->heading)->html($this->markup);
        foreach ($this->files as [$path,$name]) {
            $this->attach($path, $name !== '' ? ['as' => $name] : []);
        }
        foreach ($this->replies as [$email,$name]) {
            $this->replyTo($email, $name);
        }

        return $this;
    }
}
