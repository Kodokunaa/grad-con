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
            $models = JobApplication::with(['job', 'alumni.education', 'alumni.employmentHistory'])->whereHas('job', fn ($q) => $q->where('posted_by', $employer_id)->orWhere('employer_id', $employer_id))->orderByDesc('id')->get();
            $applications = $models->map(fn ($a) => array_merge($a->getAttributes(), $a->alumni->getAttributes(), ['application_id' => $a->id, 'alumni_id' => $a->alumni_id, 'job_title' => $a->job->title, 'company' => $a->job->company, 'job_start_date' => $a->job->getRawOriginal('start_date'), 'job_end_date' => $a->job->getRawOriginal('end_date')]))->all();
            $educationByUser = $models->pluck('alumni')->unique('id')->mapWithKeys(fn ($u) => [$u->id => $u->education->map->getAttributes()->all()])->all();
            $employmentByUser = $models->pluck('alumni')->unique('id')->mapWithKeys(fn ($u) => [$u->id => $u->employmentHistory->map->getAttributes()->all()])->all();

            return $this->pageView('pages.employer.applications', get_defined_vars());
        });
    }
}
