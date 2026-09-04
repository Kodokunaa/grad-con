<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\JobOffer;
use Illuminate\Http\Request;

final class EmployerJobOffersController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('employer');
            $id = (int) \gc_context()->session['user']['id'];
            $error = '';
            $msg = '';
            $offers = JobOffer::query()->with('alumni')->where('employer_id', $id)->latest('created_at')->get()->map(function ($offer) {
                $row = $offer->toArray();
                $row['alumni_name'] = $offer->alumni?->fullname;
                $row['alumni_email'] = $offer->alumni?->email;
                $row['course'] = $offer->alumni?->course;
                return $row;
            })->all();
            // Calculate statistics
            $stats = ['total' => 0, 'sent' => 0, 'accepted' => 0, 'declined' => 0, 'done' => 0, 'expired' => 0];
            foreach ($offers as $offer) {
                $stats['total']++;
                if (isset($stats[$offer['status']])) {
                    $stats[$offer['status']]++;
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('employer_sidebar', \get_defined_vars());

            return $this->pageView('pages.employer.job_offers', get_defined_vars());
        });
    }
}
