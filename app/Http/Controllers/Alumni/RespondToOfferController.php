<?php

namespace App\Http\Controllers\Alumni;

use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Mail\OfferAcceptedMail;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

final class RespondToOfferController extends Controller
{
    public function confirm(Request $request, string $token, string $action)
    {
        $offer = $this->offer($token);
        Gate::authorize('respond', $offer);
        $this->assertAvailable($offer);
        $updateUrl = route('offers.response.update', ['token' => $token, 'action' => $action]);

        return view('offers.confirm-response', compact('offer', 'action', 'updateUrl'));
    }

    public function update(Request $request, string $token, string $action)
    {
        $offer = $this->offer($token);
        Gate::authorize('respond', $offer);
        $this->assertAvailable($offer);

        DB::transaction(function () use ($offer, $action): void {
            $offer->refresh();
            $this->assertAvailable($offer);
            $offer->forceFill([
                'status' => $action === 'accept' ? OfferStatus::Accepted : OfferStatus::Declined,
                $action === 'accept' ? 'accepted_at' : 'declined_at' => now(),
            ])->save();
        });

        if ($action === 'accept') {
            $offer->load(['employer', 'alumni']);
            Mail::to($offer->employer)->queue(new OfferAcceptedMail($offer));
        }

        return to_route('alumni.job_offers')->with('status', $action === 'accept'
            ? 'Offer accepted successfully.'
            : 'Offer declined successfully.');
    }

    private function offer(string $token): JobOffer
    {
        return JobOffer::where('offer_token', $token)->firstOrFail();
    }

    private function assertAvailable(JobOffer $offer): void
    {
        abort_unless($offer->status === OfferStatus::Sent && $offer->expires_at?->isFuture(), 422, 'This offer is unavailable or expired.');
    }
}
