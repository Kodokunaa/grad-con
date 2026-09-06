<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class EmployerDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $eid = (int) (request()->user()?->id ?? 0);
            $fullname = request()->user()?->fullname ?? 'Employer';
            $jobsCount = 0;
            $openJobsCount = 0;
            $closedJobsCount = 0;
            $appsCount = 0;
            $pendingCount = 0;
            $interviewCount = 0;
            $acceptedCount = 0;
            $hiredCount = 0;
            $rejectedCount = 0;
            $latest = [];
            $snapshot = Cache::remember('dashboard.employer.'.$eid.'.v1', config('performance.dashboard_cache_seconds'), function () use ($eid) {
                $jobs = Job::query()->where('employer_id', $eid);
                $applications = JobApplication::query()->whereHas('job', fn ($query) => $query->where('employer_id', $eid));
                $jobStats = (clone $jobs)->selectRaw('COUNT(*) total, SUM(CASE WHEN is_open = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) THEN 1 ELSE 0 END) open, SUM(CASE WHEN is_open = 0 OR end_date < ? THEN 1 ELSE 0 END) closed', [today(), today(), today()])->first()->toArray();
                $applicationStats = (clone $applications)->selectRaw("COUNT(*) total, SUM(CASE WHEN applications.status = 'pending' THEN 1 ELSE 0 END) pending, SUM(CASE WHEN applications.status IN ('interview', 'for interview') THEN 1 ELSE 0 END) interviews, SUM(CASE WHEN applications.status = 'accepted' THEN 1 ELSE 0 END) accepted, SUM(CASE WHEN applications.status = 'hired' THEN 1 ELSE 0 END) hired, SUM(CASE WHEN applications.status = 'rejected' THEN 1 ELSE 0 END) rejected")->first()->toArray();
                $latest = (clone $applications)->with(['alumni', 'job'])->latest('id')->limit(8)->get()->map(function ($application) {
                    $row = $application->toArray();
                    $row['fullname'] = $application->alumni?->fullname;
                    $row['email'] = $application->alumni?->email;
                    $row['title'] = $application->job?->title;
                    $row['job_id'] = $application->job_id;

                    return $row;
                })->all();

                return compact('jobStats', 'applicationStats', 'latest');
            });
            $jobStats = (object) $snapshot['jobStats'];
            $applicationStats = (object) $snapshot['applicationStats'];
            $jobsCount = (int) $jobStats->total;
            $openJobsCount = (int) $jobStats->open;
            $closedJobsCount = (int) $jobStats->closed;
            $appsCount = (int) $applicationStats->total;
            $pendingCount = (int) $applicationStats->pending;
            $interviewCount = (int) $applicationStats->interviews;
            $acceptedCount = (int) $applicationStats->accepted;
            $hiredCount = (int) $applicationStats->hired;
            $rejectedCount = (int) $applicationStats->rejected;
            $latest = $snapshot['latest'];

            return $this->pageView('pages.employer.dashboard', get_defined_vars());
        });
    }
}
