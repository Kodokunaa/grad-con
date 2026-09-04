<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Mail\JobOfferMail;
use App\Models\EmployerActivityLog;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class AlumniDirectoryActionController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->input('log_action') === 'search') {
            $data = $request->validate([
                'course_filter' => ['nullable', 'string', 'max:120'], 'batch_filter' => ['nullable', 'string', 'max:20'],
                'skills_search' => ['nullable', 'string', 'max:255'], 'result_count' => ['required', 'integer', 'min:0'],
            ]);
            $log = new EmployerActivityLog;
            $log->forceFill(['employer_id' => $request->user()->id, 'action' => 'SEARCH_ALUMNI', 'details' => 'Alumni directory search',
                'course_filter' => $data['course_filter'] ?? '', 'batch_filter' => $data['batch_filter'] ?? '',
                'skill_search' => $data['skills_search'] ?? '', 'result_count' => $data['result_count']])->save();

            return response()->json(['status' => 'ok']);
        }

        $data = $request->validate([
            'email_alumni_id' => ['required', 'integer', 'exists:users,id'],
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_message' => ['required', 'string', 'max:5000'],
        ]);
        $alumni = User::query()->where('role', 'alumni')->where('is_active', 1)->findOrFail($data['email_alumni_id']);
        abort_unless(filter_var($alumni->email, FILTER_VALIDATE_EMAIL), 422, 'The alumni email address is invalid.');
        $offer = new JobOffer;
        $offer->forceFill(['employer_id' => $request->user()->id, 'alumni_id' => $alumni->id,
            'offer_token' => Str::random(64), 'subject' => $data['email_subject'] ?: 'Job Offer - '.$request->user()->fullname,
            'message' => $data['email_message'], 'status' => 'sent', 'expires_at' => now()->addDays(30)])->save();
        $offer->load(['employer', 'alumni']);
        Mail::to($alumni)->queue(new JobOfferMail($offer));
        $log = new EmployerActivityLog;
        $log->forceFill(['employer_id' => $request->user()->id, 'alumni_id' => $alumni->id, 'offer_id' => $offer->id,
            'action' => 'JOB_OFFER_SENT', 'details' => 'Subject: '.$offer->subject])->save();

        return to_route('employer.alumni_list')->with('status', 'Job offer saved and notification queued.');
    }
}
