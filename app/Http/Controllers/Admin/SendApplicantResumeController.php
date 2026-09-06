<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendApplicantResumeRequest;
use App\Mail\ApplicantResumeMail;
use App\Models\JobApplication;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

final class SendApplicantResumeController extends Controller
{
    public function __invoke(SendApplicantResumeRequest $request, JobApplication $application)
    {
        Gate::authorize('view', $application);
        $application->loadMissing(['alumni', 'job']);
        abort_unless($application->resume_file && PrivateUploads::exists('resumes', $application->resume_file), 422, 'Application letter is unavailable.');

        Mail::to($request->validated('company_email'))->queue(new ApplicantResumeMail($application));

        return back()->with('status', 'Application letter queued for delivery.');
    }

    public function legacy(SendApplicantResumeRequest $request)
    {
        $application = JobApplication::findOrFail($request->integer('app_id'));

        return $this($request, $application);
    }
}
