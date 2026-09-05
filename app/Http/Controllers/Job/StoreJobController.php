<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Mail\JobOpportunityMail;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

final class StoreJobController extends Controller
{
    public function __invoke(StoreJobRequest $request)
    {
        $user = $request->user();
        $data = $request->safe()->except(['profile_location', 'branch_location']);
        $data['is_open'] = $request->boolean('is_open');
        $data['posted_by'] = $user->id;
        if ($user->role === 'admin') {
            $data['company'] = $data['employer_company'] = 'City College of Calapan';
        } else {
            $data['company'] = $data['employer_company'] = $user->fullname;
            $data['email_address'] = $user->email;
            $data['employer_id'] = $user->id;
            $data['location'] = $request->input('branch_location') ?: $request->input('profile_location') ?: $request->input('location');
            abort_if(blank($data['location']), 422, 'Complete the employer address or select a branch location.');
        }
        $job = new Job;
        $job->forceFill($data)->save();
        Cache::forget('feed.sidebar-jobs.v1');
        User::query()->where('role', 'alumni')->where('is_active', 1)
            ->where(fn ($query) => $query->where('receive_update_notifications', 1)->orWhereNull('receive_update_notifications'))
            ->whereNotNull('email')->where('email', '<>', '')->eachById(function (User $recipient) use ($job) {
                Mail::to($recipient)->queue(new JobOpportunityMail($job, $recipient));
            });

        return to_route($user->role === 'admin' ? 'admin.jobs_create' : 'employer.post_job')->with('status', 'Job posted and notifications queued.');
    }
}
