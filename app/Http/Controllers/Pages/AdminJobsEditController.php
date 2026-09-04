<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use Illuminate\Http\Request;

final class AdminJobsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $model = Job::findOrFail($request->integer('id'));
            abort_unless($request->user()->can('update', $model), 403);
            $job = $model->getAttributes();
            $id = $model->id;
            $msg = session('status', '');
            $error = '';
            echo view('partials.header', get_defined_vars());
            echo view('partials.admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.jobs_edit', get_defined_vars());
        });
    }
}
