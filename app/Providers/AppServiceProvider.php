<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Mail::extend('brevo', function (array $config) {
            $key = (string) ($config['key'] ?? config('services.brevo.key'));

            return (new BrevoTransportFactory)->create(new Dsn('brevo+api', 'default', $key));
        });

        Password::defaults(fn () => Password::min(8));
        ResetPassword::createUrlUsing(fn ($user, $token) => url('/reset_password.php').'?'.http_build_query(['token' => $token, 'email' => $user->email]));
        RateLimiter::for('login', fn ($request) => [Limit::perMinute(20)->by($request->ip()), Limit::perMinute(5)->by(strtolower((string) $request->input('username', $request->input('student_id'))).'|'.$request->ip())]);
        RateLimiter::for('recovery', fn ($request) => Limit::perMinute(3)->by($request->ip()));
    }
}
