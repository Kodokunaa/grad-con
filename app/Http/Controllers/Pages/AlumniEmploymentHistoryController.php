<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniEmploymentHistoryController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $model = $request->user()->load(['employmentHistory' => fn ($q) => $q->orderByRaw('end_date IS NULL DESC')->orderByDesc('end_date')->orderByDesc('start_date')->orderByDesc('id')]);
            $user = $model->getAttributes();
            $alumniCourse = \App\Support\ViewFormatter::alumni_employment_history_get_alumni_course($user);
            $msg = session('status', '');
            $error = '';
            $employment_list = $model->employmentHistory->map->getAttributes()->all();
            echo view('partials.header', get_defined_vars());
            echo view('partials.alumni_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni.employment_history', get_defined_vars());
        });
    }
}
