<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendJobOfferRequest;
use App\Mail\JobOfferMail;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class SendJobOfferController extends Controller
{
    public function __invoke(SendJobOfferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $alumni = User::query()->where('role', 'alumni')->where('is_active', true)->where('status', 'approved')->findOrFail($data['alumni_id']);
        abort_unless(filter_var($alumni->email, FILTER_VALIDATE_EMAIL), 422, 'This alumni account has no valid email address.');

        $offer = new JobOffer;
        $offer->forceFill([
            'employer_id' => $request->user()->id,
            'alumni_id' => $alumni->id,
            'offer_token' => Str::random(64),
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'sent',
            'expires_at' => now()->addDays(30),
        ])->save();
        $offer->setRelation('employer', $request->user());
        $offer->setRelation('alumni', $alumni);
        Mail::to($alumni->email, $alumni->fullname)->queue(new JobOfferMail($offer));

        return to_route('employer.alumni_list')->with('status', 'Job offer email queued for '.$alumni->fullname.'.');
    }
}
