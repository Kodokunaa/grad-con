<?php

use App\Support\PageContext;
use App\Support\PageResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

function gc_context(): PageContext
{
    return app(PageContext::class);
}
function gc_user(): array
{
    return auth()->user()?->makeHidden(['password', 'remember_token'])->toArray() ?? [];
}
function gc_e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function gc_header(string $header, bool $replace = true, int $code = 0): void
{
    gc_context()->header($header, $replace, $code);
}
function gc_http_status(?int $status = null): int
{
    if ($status) {
        gc_context()->status = $status;
    }

    return gc_context()->status;
}
function gc_finish(mixed $body = ''): never
{
    throw new PageResponse($body);
}
function gc_noop(...$args): bool
{
    return true;
}
function gc_require_role(?string $role = null): void
{
    abort_unless(auth()->check(), 401);
    if ($role) {
        abort_unless(auth()->user()->role === $role, 403);
    }
}
function gc_verify_password(PDO $pdo, array $user, string $password): bool
{
    return Hash::check($password, (string) ($user['password'] ?? ''));
}
function gc_hash_password(string $password): string
{
    return Hash::make($password);
}
function gc_public_error(Throwable $exception, string $fallback = 'An unexpected server error occurred.'): string
{
    report($exception);

    return config('app.debug') ? $exception->getMessage() : $fallback;
}

function gc_partial(string $name, array $data = []): string
{
    unset($data['__env'], $data['app']);

    return view('partials.'.$name, $data)->render();
}
function gc_files(): array
{
    $files = [];
    foreach (request()->allFiles() as $name => $file) {
        if ($file instanceof UploadedFile) {
            $files[$name] = ['name' => $file->getClientOriginalName(), 'type' => $file->getClientMimeType(), 'tmp_name' => $file->getPathname(), 'error' => $file->getError(), 'size' => $file->getSize()];
        }
    }

    return $files;
}
