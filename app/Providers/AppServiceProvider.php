<?php

namespace App\Providers;

use App\Support\PageContext;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PageContext::class);
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8));
        ResetPassword::createUrlUsing(fn ($user, $token) => url('/reset_password.php').'?'.http_build_query(['token' => $token, 'email' => $user->email]));
        RateLimiter::for('login', fn ($request) => [Limit::perMinute(20)->by($request->ip()), Limit::perMinute(5)->by(strtolower((string) $request->input('username', $request->input('student_id'))).'|'.$request->ip())]);
        RateLimiter::for('recovery', fn ($request) => Limit::perMinute(3)->by($request->ip()));
    }
}
