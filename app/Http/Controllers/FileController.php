<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\User;
use App\Support\PrivateUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class FileController extends Controller
{
    public function resume(Request $request, ?string $filename = null)
    {
        $query = JobApplication::query();
        if ($request->filled('app_id')) {
            $query->whereKey((int) $request->query('app_id'));
        } else {
            $query->where('resume_file', basename($filename ?? $request->query('view_resume', '')));
        }
        $application = $query->firstOrFail();
        Gate::authorize('view', $application);
        $file = basename((string) $application->resume_file);

        return $this->serve('resumes/'.$file, true);
    }

    public function upload(Request $request, string $path)
    {
        $parts = explode('/', trim($path, '/'));
        abort_unless(count($parts) === 2 && in_array($parts[0], ['resumes', 'certificates', 'profiles', 'events'], true), 404);
        [$category] = $parts;
        if (str_starts_with($path, 'resumes/')) {
            return $this->resume($request, basename($path));
        }
        if (str_starts_with($path, 'certificates/')) {
            $ownerId = DB::table('alumni_certificates')->where('certificate_image', basename($path))->value('user_id');
            $owner = User::findOrFail($ownerId);
            Gate::authorize('viewPrivateFile', $owner);
        }
        if ($category === 'events') {
            abort_unless(in_array($request->user()->role, ['admin', 'alumni', 'alumni_officer'], true), 403);
        }

        return $this->serve($path, false);
    }

    private function serve(string $relative, bool $resume)
    {
        $relative = 'files/uploads/'.trim($relative, '/');
        try {
            $disk = PrivateUploads::disk();
            $exists = $disk->exists($relative);
            $stream = $exists ? $disk->readStream($relative) : false;
        } catch (\Throwable $exception) {
            report($exception);
            abort(503, 'File storage is temporarily unavailable.');
        }
        abort_unless($exists, 404);
        abort_if($stream === false, 404);
        $prefix = fread($stream, 8192);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($prefix) ?: 'application/octet-stream';
        $resumeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', // Some fileinfo databases identify DOCX containers as ZIP.
        ];
        if (! ($resume ? in_array($mime, $resumeTypes, true) : in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'], true))) {
            fclose($stream);
            abort(415);
        }

        if ($resume && $mime !== 'application/pdf') {
            $disposition = 'attachment';
        } else {
            $disposition = 'inline';
        }

        return response()->stream(function () use ($prefix, $stream): void {
            echo $prefix;
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.basename($relative).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
