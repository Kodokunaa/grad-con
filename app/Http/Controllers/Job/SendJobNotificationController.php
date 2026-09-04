<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendJobNotificationRequest;
use App\Mail\JobOpportunityMail;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class SendJobNotificationController extends Controller
{
    public function __invoke(SendJobNotificationRequest $request)
    {
        $job = Job::findOrFail($request->integer('job_id'));
        $recipients = User::query()->where('role', 'alumni')->where('is_active', 1)
            ->where(fn ($query) => $query->where('receive_update_notifications', 1)->orWhereNull('receive_update_notifications'))
            ->whereNotNull('email')->where('email', '<>', '')
            ->when(filled($job->target_course), fn ($query) => $query->where('course', $job->target_course))
            ->get();
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->queue(new JobOpportunityMail($job, $recipient, $request->string('subject')->toString(), $request->string('message')->toString()));
        }

        return to_route('admin.jobs_notify', ['job_id' => $job->id])
            ->with('status', "Notification queued for {$recipients->count()} alumni.");
    }
}
