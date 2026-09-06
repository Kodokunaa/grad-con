<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

final class AlumniOfficerSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = config('gradconn.alumni_officer_seed');
        $validator = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            throw new RuntimeException('Invalid alumni officer seed configuration: '.$validator->errors()->first());
        }

        $officer = User::query()->where('role', 'alumni_officer')->first()
            ?? User::firstOrNew(['username' => $attributes['username']]);
        $officer->forceFill([
            'fullname' => $attributes['name'],
            'username' => $attributes['username'],
            'email' => $attributes['email'],
            'password' => $attributes['password'],
            'role' => 'alumni_officer',
            'is_active' => true,
            'status' => 'approved',
        ])->save();

        $this->command?->info("Alumni Officer account ready: {$officer->username}");
    }
}
