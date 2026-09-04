<?php

namespace App\Console\Commands;

use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CheckInstallation extends Command
{
    protected $signature = 'gradconn:check {--database : Test the configured database connection}';

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
