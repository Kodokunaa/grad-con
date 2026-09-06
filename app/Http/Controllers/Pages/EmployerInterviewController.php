<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Interview;
use App\Models\JobApplication;
use Illuminate\Http\Request;

final class EmployerInterviewController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $application_id = $request->integer('application_id');
            $success = session('status', '');
            $error = '';
            if ($application_id) {
                $model = JobApplication::with(['alumni', 'job'])->findOrFail($application_id);
                abort_unless($request->user()->can('update', $model), 403);
                $application = array_merge($model->getAttributes(), ['application_id' => $model->id, 'fullname' => $model->alumni->fullname, 'email' => $model->alumni->email, 'job_title' => $model->job->title, 'company' => $model->job->company, 'employer_company' => $model->job->employer_company, 'posted_by' => $model->job->posted_by]);
                $interview = Interview::where('application_id', $model->id)->first()?->getAttributes();
            } else {
                abort(404);
            }

            return $this->pageView('pages.employer.interview', get_defined_vars());
        });
    }
}
