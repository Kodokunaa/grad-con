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
            $fullname = request()->user()?->fullname ?? 'User';
            $alumni_id = (int) (request()->user()?->id ?? 0);
            echo view('partials.header', \get_defined_vars());
            echo view('partials.alumni_sidebar', \get_defined_vars());
            $totalApplications = 0;
            $pendingApplications = 0;
            $rejectedApplications = 0;
            $hiredApplications = 0;
            $upcomingInterviews = 0;
            $totalJobOffers = 0;
            $acceptedJobOffers = 0;
            $declinedJobOffers = 0;
            $pendingJobOffers = 0;
            $applicationStats = JobApplication::query()->where('alumni_id', $alumni_id)->selectRaw("COUNT(*) total, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) pending, SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) rejected, SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) hired, SUM(CASE WHEN status IN ('interview', 'for interview') THEN 1 ELSE 0 END) interviews")->first();
            $offerStats = JobOffer::query()->where('alumni_id', $alumni_id)->selectRaw("COUNT(*) total, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) accepted, SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) declined, SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) pending")->first();
            $totalApplications = (int) $applicationStats->total;
            $pendingApplications = (int) $applicationStats->pending;
            $rejectedApplications = (int) $applicationStats->rejected;
            $hiredApplications = (int) $applicationStats->hired;
            $upcomingInterviews = (int) $applicationStats->interviews;
            $totalJobOffers = (int) $offerStats->total;
            $acceptedJobOffers = (int) $offerStats->accepted;
            $declinedJobOffers = (int) $offerStats->declined;
            $pendingJobOffers = (int) $offerStats->pending;

            return $this->pageView('pages.alumni.dashboard', get_defined_vars());
        });
    }
}
