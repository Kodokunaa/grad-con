<?php

namespace App\Services;

use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UpdatePassword
{
    public function handle(User $user, string $currentPassword, string $newPassword, Request $request): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = $newPassword;
        $user->save();
        SecurityLog::forceCreate([
            'user_id' => $user->id,
            'action' => 'PASSWORD_CHANGED',
            'details' => 'Password changed',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        DB::table('sessions')->where('user_id', $user->id)->where('id', '<>', $request->session()->getId())->delete();
        $request->session()->regenerate();

        return true;
    }
}
