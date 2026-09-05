<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

final class SendTestMail extends Command
{
    protected $signature = 'gradconn:test-mail {recipient : Address that should receive the test message}';

    protected $description = 'Send a real GradConn delivery test using the configured mailer';

    public function handle(): int
    {
        $recipient = (string) $this->argument('recipient');
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('The recipient must be a valid email address.');

            return self::FAILURE;
        }

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->components->error('The configured mailer does not deliver externally. Set MAIL_MAILER=brevo, resend, or smtp.');

            return self::FAILURE;
        }

        try {
            Mail::raw('GradConn email delivery is working.', function ($message) use ($recipient) {
                $message->to($recipient)->subject('GradConn email test');
            });
        } catch (\Throwable $exception) {
            report($exception);
            $this->components->error('Delivery failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Test email sent to {$recipient}.");

        return self::SUCCESS;
    }
}
