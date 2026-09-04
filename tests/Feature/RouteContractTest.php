<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class RouteContractTest extends TestCase
{
    public function test_role_portals_keep_their_expected_account_middleware(): void
    {
        $expected = [
            'admin/' => 'account:admin',
            'alumni/' => 'account:alumni',
            'alumni_officer/' => 'account:alumni_officer',
            'employer/' => 'account:employer',
        ];
        $failures = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($expected as $prefix => $middleware) {
                if (str_starts_with($route->uri(), $prefix) && ! in_array($middleware, $route->gatherMiddleware(), true)) {
                    $failures[] = implode('|', $route->methods()).' '.$route->uri().' lacks '.$middleware;
                }
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_post_routes_are_explicit_and_use_the_web_middleware_group(): void
    {
        $failures = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('POST', $route->methods(), true)) {
                continue;
            }
            if ($route->methods() !== ['POST']) {
                $failures[] = $route->uri().' also accepts '.implode('|', array_diff($route->methods(), ['POST']));
            }
            if (! in_array('web', $route->gatherMiddleware(), true)) {
                $failures[] = $route->uri().' lacks web middleware';
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_security_headers_are_applied_to_html_responses(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_every_blade_post_form_declares_its_own_csrf_field(): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        $failures = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            if (preg_match('/<form\b(?=[^>]*\bmethod\s*=\s*[\x22\x27]POST[\x22\x27])[^>]*>(?!\s*(?:@csrf|<input\b[^>]*\bname\s*=\s*[\x22\x27]_token[\x22\x27]))/i', $contents)) {
                $failures[] = $file->getPathname();
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }
}
