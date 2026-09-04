<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniAddDegreeController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $model = $request->user()->load(['education' => fn ($q) => $q->orderByDesc('id')]);
            $user = $model->getAttributes();
            $msg = session('status', '');
            $error = '';
            $degree_options = ['Primary', 'Secondary', 'Tertiary', 'Masteral', 'Doctorate'];
            $education_list = $model->education->map->getAttributes()->all();
            echo view('partials.header', get_defined_vars());
            echo view('partials.alumni_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni.add_degree', get_defined_vars());
        });
    }
}
