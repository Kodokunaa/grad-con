<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Job;
use App\Models\JobApplication;
use App\Support\PrivateUploads;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

final class StoreApplicationController extends Controller
{
    public function __invoke(StoreApplicationRequest $request, Job $job): RedirectResponse
    {
        abort_unless($job->is_open && (! $job->start_date || $job->start_date->isPast()) && (! $job->end_date || $job->end_date->isFuture() || $job->end_date->isToday()), 422, 'This job is not currently accepting applications.');
        $alumni = $request->user();
        if (JobApplication::where('job_id', $job->id)->where('alumni_id', $alumni->id)->exists()) {
            throw ValidationException::withMessages(['resume' => 'You already applied to this job.']);
        }
        foreach (['fullname', 'email', 'course'] as $field) {
            if (blank($alumni->{$field})) {
                throw ValidationException::withMessages([$field => 'Your profile is incomplete. Please update it before applying.']);
            }
        }
        $filename = 'resume_job'.$job->id.'_u'.$alumni->id.'_'.now()->timestamp.'_'.bin2hex(random_bytes(4)).'.pdf';
        if (! PrivateUploads::store($request->file('resume'), 'resumes', $filename)) {
            throw ValidationException::withMessages(['resume' => 'The résumé could not be stored. Please try again.']);
        }
        try {
            $application = new JobApplication;
            $application->forceFill([
                'job_id' => $job->id, 'alumni_id' => $alumni->id, 'message' => $request->string('message')->trim()->toString(), 'resume_file' => $filename, 'status' => 'pending',
                'applicant_fullname' => $alumni->fullname, 'applicant_email' => $alumni->email, 'applicant_course' => $alumni->course, 'applicant_batch_year' => $alumni->batch_year,
                'applicant_birthdate' => $alumni->birthdate, 'applicant_age' => $alumni->age, 'applicant_gender' => $alumni->gender, 'applicant_civil_status' => $alumni->civil_status,
                'applicant_contact_number' => $alumni->contact_number, 'applicant_address' => $alumni->address, 'applicant_indigenous_tribe' => $alumni->indigenous_tribe,
                'applicant_special_needs' => $alumni->special_needs, 'applicant_employment_status' => $alumni->employment_status, 'applicant_job_aligned' => $alumni->job_aligned,
                'applicant_profile_picture' => $alumni->profile_picture, 'applicant_career_objective' => $alumni->career_objective, 'applicant_skills' => $alumni->skills,
            ])->save();
        } catch (QueryException $exception) {
            PrivateUploads::delete('resumes', $filename);
            if ((string) $exception->getCode() === '23000') {
                throw ValidationException::withMessages(['resume' => 'You already applied to this job.']);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            PrivateUploads::delete('resumes', $filename);
            throw $exception;
        }

        return to_route('alumni.apply', ['job_id' => $job->id])->with('status', 'Application submitted successfully!');
    }
}
