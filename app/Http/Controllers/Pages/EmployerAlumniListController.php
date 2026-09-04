<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\User;
use Illuminate\Http\Request;

final class EmployerAlumniListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $msg = session('status', '');
            $error = '';
            $employmentHistoryError = '';
            $models = User::query()->where('role', 'alumni')->where('is_active', 1)
                ->with(['education' => fn ($q) => $q->orderByDesc('end_year')->orderByDesc('start_year')->orderByDesc('id'),
                    'certificates' => fn ($q) => $q->orderByDesc('issue_date')->orderByDesc('id'),
                    'employmentHistory' => fn ($q) => $q->orderByDesc('end_date')->orderByDesc('start_date')->orderByDesc('id'),
                    'degrees' => fn ($q) => $q->orderByDesc('id')])->orderByDesc('id')->get();
            $alumni = $models->map->getAttributes()->all();
            $educationByUser = $models->mapWithKeys(fn ($user) => [$user->id => $user->education->map->getAttributes()->all()])->all();
            $certificatesByUser = $models->mapWithKeys(fn ($user) => [$user->id => $user->certificates->map->getAttributes()->all()])->all();
            $employmentByUser = $models->mapWithKeys(fn ($user) => [$user->id => $user->employmentHistory->map->getAttributes()->all()])->all();
            $degreesByUser = $models->mapWithKeys(fn ($user) => [$user->id => $user->degrees->map->getAttributes()->all()])->all();
            $courseOptions = $models->pluck('course')->filter()->unique()->sort()->values()->all();
            $batchOptions = $models->pluck('batch_year')->filter()->unique()->sort()->values()->all();
            echo gc_partial('header', get_defined_vars());
            echo gc_partial('employer_sidebar', get_defined_vars());

            return $this->pageView('pages.employer.alumni_list', get_defined_vars());
        });
    }
}
