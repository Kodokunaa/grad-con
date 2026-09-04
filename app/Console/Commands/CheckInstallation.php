<?php

namespace App\Console\Commands;

use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CheckInstallation extends Command
{
    protected $signature = 'gradconn:check {--database : Test the configured database connection} {--mail : Validate outbound mail configuration}';

    protected $description = 'Check whether this device is ready to run GradConn';

    public function handle(): int
    {
        $failures = [];
        $requiredExtensions = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'mbstring', 'openssl', 'pdo', 'pdo_mysql', 'xml'];

        foreach ($requiredExtensions as $extension) {
            if (! extension_loaded($extension)) {
                $failures[] = "Missing PHP extension: {$extension}";
            }
        }

        if (! config('app.key')) {
            $failures[] = 'APP_KEY is missing. Run php artisan key:generate.';
        }

        if (! in_array(config('app.timezone'), DateTimeZone::listIdentifiers(), true)) {
            $failures[] = 'APP_TIMEZONE is invalid.';
        }

        foreach ([storage_path(), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache')] as $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $failures[] = "Directory must exist and be writable: {$path}";
            }
        }

        if ($this->option('database')) {
            try {
                DB::connection()->getPdo();
                $this->components->info('Database connection succeeded.');
            } catch (\Throwable $exception) {
                $failures[] = 'Database connection failed: '.$exception->getMessage();
            }
        }

        if ($this->option('mail')) {
            $mailer = (string) config('mail.default');
            if ($mailer === 'log' || $mailer === 'array') {
                $failures[] = "MAIL_MAILER={$mailer} does not deliver email to recipients. Use resend or smtp.";
            } elseif ($mailer === 'resend') {
                if (blank(config('services.resend.key'))) {
                    $failures[] = 'RESEND_API_KEY is missing.';
                }
                $from = (string) config('mail.from.address');
                if (! filter_var($from, FILTER_VALIDATE_EMAIL)) {
                    $failures[] = 'MAIL_FROM_ADDRESS must be a valid email address.';
                } elseif (str_ends_with(strtolower($from), '@gmail.com')) {
                    $failures[] = 'MAIL_FROM_ADDRESS must use your Resend-verified domain, not gmail.com.';
                }
            } elseif ($mailer === 'smtp') {
                foreach (['host', 'port', 'username', 'password'] as $key) {
                    if (blank(config("mail.mailers.smtp.{$key}"))) {
                        $failures[] = 'SMTP '.strtoupper($key).' is missing.';
                    }
                }
                if (! filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)) {
                    $failures[] = 'MAIL_FROM_ADDRESS must be a valid email address.';
                }
                if (config('mail.mailers.smtp.host') === 'smtp-relay.brevo.com'
                    && ! str_ends_with((string) config('mail.mailers.smtp.username'), '@smtp-brevo.com')) {
                    $failures[] = 'Brevo MAIL_USERNAME must be the SMTP login from Settings > SMTP & API, usually ending in @smtp-brevo.com.';
                }
            } else {
                $failures[] = "Unsupported delivery mailer: {$mailer}.";
            }
        }

        if ($failures !== []) {
            foreach ($failures as $failure) {
                $this->components->error($failure);
            }

            return self::FAILURE;
        }

        $this->components->info('GradConn installation checks passed.');

        return self::SUCCESS;
    }
}
