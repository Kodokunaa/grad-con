<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobOffer;
use Illuminate\Http\Request;

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
            $offersCount = 0;
            $offersAcceptedCount = 0;
            $offersDeclinedCount = 0;
            $offersPendingCount = 0;
            $latest = [];
            $latestOffers = [];
            $jobs = Job::query()->where('employer_id', $eid);
            $applications = JobApplication::query()->whereHas('job', fn ($query) => $query->where('employer_id', $eid));
            $offers = JobOffer::query()->where('employer_id', $eid);
            $jobStats = (clone $jobs)->selectRaw('COUNT(*) total, SUM(CASE WHEN is_open = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) THEN 1 ELSE 0 END) open, SUM(CASE WHEN is_open = 0 OR end_date < ? THEN 1 ELSE 0 END) closed', [today(), today(), today()])->first();
            $applicationStats = (clone $applications)->selectRaw("COUNT(*) total, SUM(CASE WHEN applications.status = 'pending' THEN 1 ELSE 0 END) pending, SUM(CASE WHEN applications.status IN ('interview', 'for interview') THEN 1 ELSE 0 END) interviews, SUM(CASE WHEN applications.status = 'accepted' THEN 1 ELSE 0 END) accepted, SUM(CASE WHEN applications.status = 'hired' THEN 1 ELSE 0 END) hired, SUM(CASE WHEN applications.status = 'rejected' THEN 1 ELSE 0 END) rejected")->first();
            $offerStats = (clone $offers)->selectRaw("COUNT(*) total, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) accepted, SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) declined, SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) pending")->first();
            $jobsCount = (int) $jobStats->total;
            $openJobsCount = (int) $jobStats->open;
            $closedJobsCount = (int) $jobStats->closed;
            $appsCount = (int) $applicationStats->total;
            $pendingCount = (int) $applicationStats->pending;
            $interviewCount = (int) $applicationStats->interviews;
            $acceptedCount = (int) $applicationStats->accepted;
            $hiredCount = (int) $applicationStats->hired;
            $rejectedCount = (int) $applicationStats->rejected;
            $offersCount = (int) $offerStats->total;
            $offersAcceptedCount = (int) $offerStats->accepted;
            $offersDeclinedCount = (int) $offerStats->declined;
            $offersPendingCount = (int) $offerStats->pending;
            $latest = (clone $applications)->with(['alumni', 'job'])->latest('id')->limit(8)->get()->map(function ($application) {
                $row = $application->toArray();
                $row['fullname'] = $application->alumni?->fullname;
                $row['email'] = $application->alumni?->email;
                $row['title'] = $application->job?->title;
                $row['job_id'] = $application->job_id;

                return $row;
            })->all();
            $latestOffers = (clone $offers)->with('alumni')->latest('id')->limit(5)->get()->map(function ($offer) {
                $row = $offer->toArray();
                $row['fullname'] = $offer->alumni?->fullname;
                $row['email'] = $offer->alumni?->email;

                return $row;
            })->all();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.employer_sidebar', \get_defined_vars());

            return $this->pageView('pages.employer.dashboard', get_defined_vars());
        });
    }
}
