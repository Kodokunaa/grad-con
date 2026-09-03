<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class HashLegacyPasswords extends Command
{
    protected $signature = 'gradconn:hash-passwords {--dry-run : Count plaintext passwords without changing records}';

    protected $description = 'Convert existing plaintext passwords without displaying or logging their values';

    public function handle(): int
    {
        $count = 0;
        DB::table('users')->orderBy('id')->chunkById(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                if (password_get_info((string) $user->password)['algo'] !== null) {
                    continue;
                }
                $count++;
                if (! $this->option('dry-run')) {
                    DB::table('users')->where('id', $user->id)->where('password', $user->password)->update(['password' => Hash::make($user->password)]);
                }
            }
        });
        $this->info($this->option('dry-run') ? "$count accounts require conversion." : "$count passwords converted. Existing hashes were preserved.");

        return self::SUCCESS;
    }
}
