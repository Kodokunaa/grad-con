<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\JobApplication;
use Illuminate\Http\Request;

final class AlumniMyApplicationsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('alumni');
            $alumni_id = (int) \gc_context()->session['user']['id'];
            $msg = '';
            $error = '';
            /*
            |--------------------------------------------------------------------------
            | LOAD APPLICATIONS
            |--------------------------------------------------------------------------
            */
            $apps = JobApplication::query()->with('job')->where('alumni_id', $alumni_id)->latest('id')->get()->map(function ($application) {
                $row = $application->toArray();
                $row['title'] = $application->job?->title;
                $row['company'] = $application->job?->company;
                $row['location'] = $application->job?->location;
                $row['job_type'] = $application->job?->job_type;
                return $row;
            })->all();
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.my_applications', get_defined_vars());
        });
    }
}
