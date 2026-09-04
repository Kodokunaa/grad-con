<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Http\Requests\StoreTrainingRequest;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class AdminTrainingsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('admin');
            $id = request()->integer('id');
            $model = Training::findOrFail($id);
            Gate::authorize('update', $model);
            $training = $model->toArray();
            $msg = (string) session('status', '');
            $error = session('errors')?->first() ?? '';
            $allowed_courses = StoreTrainingRequest::COURSES;
            echo \gc_partial('header', get_defined_vars());
            echo \gc_partial('admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.trainings_edit', get_defined_vars());
        });
    }
}
