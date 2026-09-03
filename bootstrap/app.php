<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['account' => \App\Http\Middleware\AccountAccess::class]);
        $middleware->web(append: [\App\Http\Middleware\PageSecurity::class]);
        $middleware->redirectGuestsTo('/');
        $middleware->validateCsrfTokens();
        $middleware->trimStrings(except: ['password','password_confirmation','confirm_password','old_password','new_password']);
    })->withExceptions(function (Exceptions $exceptions): void {})->create();