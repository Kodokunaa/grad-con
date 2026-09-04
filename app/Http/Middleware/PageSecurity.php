<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PageSecurity
{
    public function handle(Request $request, Closure $next)
    {
        $request->server->set('PHP_SELF', $request->getBaseUrl().$request->getPathInfo());
        $response = $next($request);
        if (! $request->isMethodSafe() && $request->user()) {
            try {
                DB::table('audit_logs')->insert(['user_id' => $request->user()->id, 'method' => $request->method(), 'path' => $request->path(), 'status' => $response->getStatusCode(), 'ip_address' => $request->ip()]);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
