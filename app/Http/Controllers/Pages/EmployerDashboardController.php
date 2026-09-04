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
            \gc_require_role('employer');
            $eid = (int) (\gc_context()->session['user']['id'] ?? 0);
            $fullname = \gc_context()->session['user']['fullname'] ?? 'Employer';
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
            $jobsCount = (clone $jobs)->count();
            $openJobsCount = (clone $jobs)->where('is_open', true)->where(fn ($query) => $query->whereNull('start_date')->orWhere('start_date', '<=', today()))->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', today()))->count();
            $closedJobsCount = (clone $jobs)->where(fn ($query) => $query->where('is_open', false)->orWhere('end_date', '<', today()))->count();
            $appsCount = (clone $applications)->count();
            $pendingCount = (clone $applications)->where('status', 'pending')->count();
            $interviewCount = (clone $applications)->whereIn('status', ['interview', 'for interview'])->count();
            $acceptedCount = (clone $applications)->where('status', 'accepted')->count();
            $hiredCount = (clone $applications)->where('status', 'hired')->count();
            $rejectedCount = (clone $applications)->where('status', 'rejected')->count();
            $offersCount = (clone $offers)->count();
            $offersAcceptedCount = (clone $offers)->where('status', 'accepted')->count();
            $offersDeclinedCount = (clone $offers)->where('status', 'declined')->count();
            $offersPendingCount = (clone $offers)->where('status', 'sent')->count();
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
            })->all();            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('employer_sidebar', \get_defined_vars());

            return $this->pageView('pages.employer.dashboard', get_defined_vars());
        });
    }
}
