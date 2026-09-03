<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'fullname' => (string) config('gradconn.admin_seed.name'),
            'username' => (string) config('gradconn.admin_seed.username'),
            'email' => (string) config('gradconn.admin_seed.email'),
            'password' => (string) config('gradconn.admin_seed.password'),
        ];

        $validator = Validator::make($attributes, [
            'fullname' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            throw new RuntimeException('Invalid admin seed configuration: '.$validator->errors()->first());
        }

        $admin = User::firstOrNew(['username' => $attributes['username']]);
        $admin->forceFill([
            'fullname' => $attributes['fullname'],
            'email' => $attributes['email'],
            'password' => $attributes['password'],
            'role' => 'admin',
            'is_active' => true,
            'status' => 'approved',
        ])->save();

        $this->command?->info("Admin account ready: {$admin->username}");
    }
}
