<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\JobOffer;
use Illuminate\Http\Request;

final class AlumniJobOffersController extends PageController
{
    public function __invoke(Request $request)
    {
        if ($request->filled('accept') || $request->filled('decline')) {
            $action = $request->filled('accept') ? 'accept' : 'decline';
            $token = $request->input($action);

            return redirect()->route('offers.response.confirm', ['token' => $token, 'action' => $action]);
        }

        return $this->renderPage(function () use ($request) {
            $error = '';
            $msg = session('status', '');
            $offers = JobOffer::with('employer')->where('alumni_id', $request->user()->id)->orderByDesc('created_at')->get()
                ->map(fn ($offer) => array_merge($offer->getAttributes(), ['employer_name' => $offer->employer->fullname, 'employer_email' => $offer->employer->email]))->all();
            echo gc_partial('header', get_defined_vars());
            echo gc_partial('alumni_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni.job_offers', get_defined_vars());
        });
    }
}
