<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Http\Requests\StoreTrainingRequest;
use Illuminate\Http\Request;

final class AdminTrainingsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('admin');
            $msg = (string) session('status', '');
            $error = session('errors')?->first() ?? '';
            $allowed_courses = StoreTrainingRequest::COURSES;
            echo \gc_partial('header', get_defined_vars());
            echo \gc_partial('admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.trainings_create', get_defined_vars());
        });
    }
}
