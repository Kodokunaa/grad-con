<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\User;
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
        abort_unless(count($parts) === 2 && in_array($parts[0], ['resumes', 'certificates', 'profiles', 'events', 'trainings'], true), 404);
        [$category] = $parts;
        if (str_starts_with($path, 'resumes/')) {
            return $this->resume($request, basename($path));
        }
        if (str_starts_with($path, 'certificates/')) {
            $ownerId = DB::table('alumni_certificates')->where('certificate_image', basename($path))->value('user_id');
            $owner = User::findOrFail($ownerId);
            Gate::authorize('viewPrivateFile', $owner);
        }
        if (in_array($category, ['events', 'trainings'], true)) {
            abort_unless(in_array($request->user()->role, ['admin', 'alumni', 'alumni_officer'], true), 403);
        }

        return $this->serve($path, false);
    }

    private function serve(string $relative, bool $resume)
    {
        $root = realpath(storage_path('app/private/files/uploads'));
        $path = realpath(storage_path('app/private/files/uploads/'.$relative));
        abort_unless($root && $path && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path), 404);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $resumeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', // Some fileinfo databases identify DOCX containers as ZIP.
        ];
        abort_unless($resume ? in_array($mime, $resumeTypes, true) : in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'], true), 415);

        if ($resume && $mime !== 'application/pdf') {
            return response()->download($path, basename($path), ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
        }

        return response()->file($path, ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
