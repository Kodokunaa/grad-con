<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use Illuminate\Http\Request;

final class AdminApplicationsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $model = Job::with('poster')->findOrFail($request->integer('job_id'));
            $job = array_merge($model->getAttributes(), ['poster_role' => $model->poster?->role, 'poster_name' => $model->poster?->fullname]);
            $job_id = $model->id;
            $isEmployerPosted = $model->poster?->role === 'employer';
            $isAdminPosted = $model->poster?->role === 'admin';
            $msg = session('status', '');
            $error = '';
            $models = $model->applications()->with(['alumni.education', 'alumni.employmentHistory'])->orderByDesc('id')->get();
            $applications = $models->map(fn ($a) => array_merge($a->getAttributes(), $a->alumni->getAttributes(), ['application_id' => $a->id, 'alumni_id' => $a->alumni_id, 'resume' => null, 'resume_path' => null, 'cv' => null, 'cv_file' => null, 'file' => null, 'attachment' => null]))->all();
            $educationByUser = $models->pluck('alumni')->unique('id')->mapWithKeys(fn ($u) => [$u->id => $u->education->map->getAttributes()->all()])->all();
            $employmentByUser = $models->pluck('alumni')->unique('id')->mapWithKeys(fn ($u) => [$u->id => $u->employmentHistory->map->getAttributes()->all()])->all();
            echo gc_partial('header', get_defined_vars());
            echo gc_partial('admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.applications', get_defined_vars());
        });
    }
}
