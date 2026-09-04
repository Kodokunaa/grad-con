<?php

namespace App\Http\Controllers\Employer;

use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class EmployerJobOfferActionController extends Controller
{
    public function update(JobOffer $offer): RedirectResponse
    {
        Gate::authorize('update', $offer);
        $offer->update(['status' => OfferStatus::Done]);
        return to_route('employer.job_offers')->with('status', 'Offer marked as done.');
    }

    public function destroy(JobOffer $offer): RedirectResponse
    {
        Gate::authorize('delete', $offer);
        $offer->delete();
        return to_route('employer.job_offers')->with('status', 'Offer removed successfully.');
    }
}
