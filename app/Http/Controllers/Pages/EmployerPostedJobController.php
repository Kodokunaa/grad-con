<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use Illuminate\Http\Request;

final class EmployerPostedJobController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $employer_id = (int) (request()->user()?->id ?? 0);
            $today = date('Y-m-d');
            $error = '';
            $posted_jobs = [];
            $posted_jobs = Job::query()->where('posted_by', $employer_id)->withCount('applications')->latest('id')->get()->map(function ($job) {
                $row = $job->toArray();
                $row['total_applications'] = $job->applications_count;

                return $row;
            })->all();

            return $this->pageView('pages.employer.posted_job', get_defined_vars());
        });
    }
}
