<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AccountAccess
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        $user = $request->user()?->fresh();
        if (! $user) {
            return redirect()->guest(route('login'));
        }
        Auth::setUser($user);
        gc_context()->session = array_replace(gc_context()->session, ['user' => gc_user(), 'alumni_user' => gc_user()]);
        if (! $user->is_active || ($user->role === 'alumni' && $user->status !== 'approved')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Your account is not active or approved.']);
        }
        if ($role) {
            abort_unless($user->role === $role, 403);
        }

        return $next($request);
    }
}
