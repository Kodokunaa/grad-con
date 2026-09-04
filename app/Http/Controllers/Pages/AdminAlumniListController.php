<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\User;
use Illuminate\Http\Request;

final class AdminAlumniListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('admin');
            $msg = '';
            $error = '';
            $alumniModels = User::query()->where('role', 'alumni')->where('is_active', true)
                ->with(['education' => fn ($query) => $query->orderByRaw('COALESCE(end_year, 9999) DESC')->orderByRaw('COALESCE(start_year, 9999) DESC')->latest('id'), 'certificates' => fn ($query) => $query->orderByRaw("COALESCE(issue_date, '0000-00-00') DESC")->latest('id'), 'employmentHistory' => fn ($query) => $query->orderByRaw("COALESCE(end_date, '9999-12-31') DESC")->orderByDesc('start_date')->latest('id'), 'degrees' => fn ($query) => $query->latest('id')])
                ->latest('id')->get();
            $alumni = $alumniModels->map->toArray()->all();
            $educationByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->education->map->toArray()->all()])->all();
            $certificatesByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->certificates->map->toArray()->all()])->all();
            $employmentByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->employmentHistory->map->toArray()->all()])->all();
            $degreesByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->degrees->map->toArray()->all()])->all();
            $employmentHistoryError = '';
            $courseOptions = ['BSIS', 'BSTM', 'BSHM', 'BSED Math', 'BSED Science', 'BSNED', 'BPA'];
            $batchOptions = [];
            foreach ($alumni as $a) {
                $course = trim((string) ($a['course'] ?? ''));
                $batch = trim((string) ($a['batch_year'] ?? ''));
                if ($batch !== '') {
                    $batchOptions[] = $batch;
                }
            }
            $batchOptions = array_values(array_unique($batchOptions));
            sort($courseOptions, SORT_NATURAL | SORT_FLAG_CASE);
            sort($batchOptions, SORT_NATURAL | SORT_FLAG_CASE);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_list', get_defined_vars());
        });
    }
}
