<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\FileController;
use App\Http\Controllers\PageController;
use App\Models\JobApplication;
use Illuminate\Http\Request;

final class EmployerApplicationsController extends PageController
{
    public function __invoke(Request $request)
    {
        if ($request->filled('view_resume')) {
            return app(FileController::class)->resume($request);
        }

        return $this->renderPage(function () use ($request) {
            $employer_id = $request->user()->id;
            $success = session('status', '');
            $error = '';
            $models = JobApplication::with(['job', 'alumni:id,fullname,email,course,batch_year,career_objective,skills'])
                ->whereHas('job', fn ($q) => $q->where('posted_by', $employer_id)->orWhere('employer_id', $employer_id))
                ->orderByDesc('id')->get();
            $applications = $models->map(fn ($application) => [
                'application_id' => $application->id,
                'job_id' => $application->job_id,
                'job_title' => $application->job?->title,
                'company' => $application->job?->company,
                'fullname' => $application->applicant_fullname ?: $application->alumni?->fullname,
                'email' => $application->applicant_email ?: $application->alumni?->email,
                'course' => $application->applicant_course ?: $application->alumni?->course,
                'batch_year' => $application->applicant_batch_year ?: $application->alumni?->batch_year,
                'career_objective' => $application->applicant_career_objective ?: $application->alumni?->career_objective,
                'competencies' => $application->applicant_skills ?: $application->alumni?->skills,
                'message' => $application->message,
                'resume_file' => $application->resume_file,
                'status' => $application->status,
                'cancel_reason' => $application->cancel_reason,
                'cancelled_at' => $application->cancelled_at,
                'created_at' => $application->created_at,
            ])->all();

            return $this->pageView('pages.employer.applications', get_defined_vars());
        });
    }
}
