<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\JobApplication;
use App\Models\JobOffer;
use Illuminate\Http\Request;

final class AlumniDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('alumni');
            $fullname = \gc_context()->session['user']['fullname'] ?? 'User';
            $alumni_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());
            $totalApplications = 0;
            $pendingApplications = 0;
            $rejectedApplications = 0;
            $hiredApplications = 0;
            $upcomingInterviews = 0;
            $totalJobOffers = 0;
            $acceptedJobOffers = 0;
            $declinedJobOffers = 0;
            $pendingJobOffers = 0;
            $applications = JobApplication::query()->where('alumni_id', $alumni_id);
            $offers = JobOffer::query()->where('alumni_id', $alumni_id);
            $totalApplications = (clone $applications)->count();
            $pendingApplications = (clone $applications)->where('status', 'pending')->count();
            $rejectedApplications = (clone $applications)->where('status', 'rejected')->count();
            $hiredApplications = (clone $applications)->where('status', 'hired')->count();
            $upcomingInterviews = (clone $applications)->whereIn('status', ['interview', 'for interview'])->count();
            $totalJobOffers = (clone $offers)->count();
            $acceptedJobOffers = (clone $offers)->where('status', 'accepted')->count();
            $declinedJobOffers = (clone $offers)->where('status', 'declined')->count();
            $pendingJobOffers = (clone $offers)->where('status', 'sent')->count();

            return $this->pageView('pages.alumni.dashboard', get_defined_vars());
        });
    }
}
