<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

final class AlumniApplyController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('alumni');
            $job_id = (int) (\gc_context()->query['job_id'] ?? 0);
            if ($job_id <= 0) {
                \gc_finish('Invalid job.');
            }
            // Get job info
            $job = Job::find($job_id)?->toArray();
            if (! $job) {
                \gc_finish('Job not found.');
            }
            // Block applications if job is closed
            if ((int) $job['is_open'] !== 1) {
                \gc_finish('This job is closed and no longer accepting applications.');
            }
            $today = date('Y-m-d');
            if ((! empty($job['start_date']) && $job['start_date'] > $today)
                || (! empty($job['end_date']) && $job['end_date'] < $today)) {
                \gc_finish('This job is not currently accepting applications.');
            }
            $msg = '';
            $error = '';
            $alumni_id = (int) \gc_context()->session['user']['id'];
            // Load alumni profile data from users table
            $alumni = User::query()->whereKey($alumni_id)->where('role', 'alumni')->first()?->toArray();
            if (! $alumni) {
                \gc_finish('Alumni profile not found.');
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.apply', get_defined_vars());
        });
    }
}
