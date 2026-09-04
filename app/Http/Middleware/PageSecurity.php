<?php

namespace App\Http\Middleware;

use App\Support\PageContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PageSecurity
{
    public function handle(Request $request, Closure $next)
    {
        app()->instance(PageContext::class, new PageContext);
        $context = app(PageContext::class);
        $context->session = array_replace($request->session()->get('page_state', []), ['user' => gc_user(), 'alumni_user' => gc_user()]);
        $context->post = $request->request->all();
        $context->query = $request->query();
        $request->server->set('PHP_SELF', $request->getBaseUrl().$request->getPathInfo());
        $mutationKeys = ['delete', 'restore', 'delete_certificate', 'accept', 'decline'];
        $mutatingLink = array_intersect($mutationKeys, array_keys($request->query()));
        if ($request->isMethod('GET') && ($mutatingLink || $request->is('admin/events_delete.php', 'admin/jobs_notify.php'))) {
            return response()->view('confirm-action', ['actionUrl' => $request->fullUrl()]);
        }
        // Validate every uploaded file before the retained handlers inspect it.
        foreach (Arr::flatten($request->allFiles()) as $file) {
            abort_unless($file->isValid(), 422, 'Upload failed.');
            $isResume = $request->is('alumni/apply.php');
            validator(['upload' => $file], ['upload' => $isResume ? 'required|file|mimes:pdf|max:5120' : 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120'])->validate();
        }
        if ($request->isMethod('POST') && $request->user()) {
            foreach (['password', 'new_password'] as $field) {
                if ($request->filled($field)) {
                    $request->validate([$field => ['string', 'max:1024', Password::defaults()]]);
                }
            }
            if ($request->is('alumni/job_offers.php') && ($request->filled('offer_action') || $request->hasAny(['accept', 'decline']))) {
                $query = DB::table('job_offers')->where('alumni_id', $request->user()->id);
                $offer = $request->filled('offer_id') ? $query->where('id', $request->input('offer_id'))->first() : $query->where('offer_token', $request->input('accept', $request->input('decline')))->first();
                abort_unless($offer && $offer->status === 'sent' && strtotime($offer->expires_at) > time(), 422, 'This offer is unavailable or expired.');
            }
        }
        $response = $next($request);
        $state = $context->session;
        unset($state['user'], $state['alumni_user']);
        $request->session()->put('page_state', $state);
        if ($request->isMethod('POST') && $request->user()) {
            try {
                DB::table('audit_logs')->insert(['user_id' => $request->user()->id, 'method' => 'POST', 'path' => $request->path(), 'status' => $response->getStatusCode(), 'ip_address' => $request->ip()]);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
        if (! ($response instanceof BinaryFileResponse)
            && ! ($response instanceof StreamedResponse)
            && str_contains($response->headers->get('Content-Type', 'text/html'), 'text/html')) {
            $html = $response->getContent();
            if (is_string($html) && str_contains($html, '<')) {
                if (! preg_match('/<meta\b[^>]*\bname\s*=\s*[\x22\x27]csrf-token[\x22\x27]/i', $html)) {
                    $meta = '<meta name="csrf-token" content="'.e(csrf_token()).'">';
                    $html = preg_replace('/<head\b[^>]*>/i', '$0'.$meta, $html, 1);
                }
                if (! str_contains($html, 'js/request-security.js')) {
                    $script = '<script src="'.e(asset('js/request-security.js')).'"></script>';
                    $html = preg_replace('/<\/head>/i', $script.'</head>', $html, 1, $scriptCount);
                    if ($scriptCount === 0) {
                        $html = $script.$html;
                    }
                }
                if ($request->user() && ! str_contains($html, 'id="logoutLightbox"')) {
                    $logoutModal = view('partials.logout-modal')->render();
                    $html = preg_replace('/<\/body>/i', $logoutModal.'</body>', $html, 1, $modalCount);
                    if ($modalCount === 0) {
                        $html .= $logoutModal;
                    }
                }
                $response->setContent($html);
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
