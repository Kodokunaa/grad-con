<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;

final class AlumniJobDetailsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $job_id = (int) (request()->query('id') ?? 0);
            if ($job_id <= 0) {
                abort(404, 'Invalid job ID.');
            }
            $success = '';
            $error = '';
            $alumni_id = (int) (request()->user()?->id ?? 0);
            // Get job details
            $jobModel = Job::query()->whereKey($job_id)->where('is_open', true)
                ->where(fn ($query) => $query->whereNull('start_date')->orWhere('start_date', '<=', today()))
                ->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', today()))->first();
            $job = $jobModel?->toArray();
            if (! $job) {
                abort(404, 'Job not found or no longer open.');
            }
            // Check if already applied
            $alreadyApplied = JobApplication::query()->where('job_id', $job_id)->where('alumni_id', $alumni_id)->exists();

            return $this->pageView('pages.alumni.job_details', get_defined_vars());
        });
    }
}
