<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Mail\ApplicationStatusMail;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Mail;

final class UpdateApplicationStatusController extends Controller
{
    public function __invoke(UpdateApplicationStatusRequest $request)
    {
        $application = JobApplication::with(['job', 'alumni'])->findOrFail($request->integer('application_id'));
        abort_unless($request->user()->can('update', $application), 403);
        abort_if($request->user()->role === 'admin' && $application->job->poster?->role === 'employer', 403);
        abort_if(in_array(strtolower($application->status), ['cancelled', 'canceled'], true), 422, 'This application was cancelled.');
        $action = $request->string('action')->toString();
        $application->forceFill(['status' => ['accept' => 'hired', 'interview' => 'interview', 'reject' => 'rejected'][$action]])->save();
        if ($action !== 'reject') {
            Mail::to($application->alumni)->queue(new ApplicationStatusMail($application, $action, $request->string('action_message')->toString()));
        }
        $route = $request->user()->role === 'admin' ? 'admin.applications' : 'employer.applications';

        return to_route($route, $request->user()->role === 'admin' ? ['job_id' => $application->job_id] : [])->with('status', 'Application status updated.');
    }
}
