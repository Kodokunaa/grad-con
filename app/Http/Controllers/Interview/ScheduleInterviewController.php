<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleInterviewRequest;
use App\Mail\InterviewScheduledMail;
use App\Models\Interview;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class ScheduleInterviewController extends Controller
{
    public function __invoke(ScheduleInterviewRequest $request)
    {
        $application = JobApplication::with(['job', 'alumni'])->findOrFail($request->integer('application_id'));
        abort_unless($request->user()->can('update', $application), 403);
        $interview = DB::transaction(function () use ($request, $application) {
            $keys = ['application_id' => $application->id];
            $interview = Interview::firstOrNew($keys);
            if ($interview->exists) {
                abort_unless($request->user()->can('update', $interview), 403);
            }
            $interview->forceFill(array_merge($keys, ['employer_id' => $request->user()->role === 'employer' ? $request->user()->id : 0,
                'admin_id' => $request->user()->role === 'admin' ? $request->user()->id : null,
                'alumni_id' => $application->alumni_id, 'job_id' => $application->job_id,
                'interview_date' => $request->date('interview_date'), 'interview_time' => $request->input('interview_time'),
                'location' => $request->input('location'), 'message' => $request->input('message'), 'status' => 'scheduled', 'email_sent' => 1]))->save();
            $application->forceFill(['status' => 'interview'])->save();

            return $interview;
        });
        $interview->load(['alumni', 'job']);
        Mail::to($interview->alumni)->queue(new InterviewScheduledMail($interview));
        $route = $request->user()->role === 'admin' ? 'admin.interview' : 'employer.interview';

        return to_route($route, ['application_id' => $application->id])->with('status', 'Interview saved and notification queued.');
    }
}
