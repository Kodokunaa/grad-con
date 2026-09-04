<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\User;
use Illuminate\Http\Request;

final class AlumniOfficerAlumniListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $alumniModels = User::query()->where('role', 'alumni')->with(['education', 'certificates', 'employmentHistory', 'degrees'])->latest('id')->get();
            $alumni = $alumniModels->map->toArray()->all();
            $educationByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->education->map->toArray()->all()])->all();
            $certificatesByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->certificates->map->toArray()->all()])->all();
            $employmentByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->employmentHistory->map->toArray()->all()])->all();
            $degreesByUser = $alumniModels->mapWithKeys(fn ($user) => [$user->id => $user->degrees->map->toArray()->all()])->all();
            $courseOptions = [];
            $batchOptions = [];
            foreach ($alumni as $a) {
                if (! empty($a['course'])) {
                    $courseOptions[] = trim($a['course']);
                }
                if (! empty($a['batch_year'])) {
                    $batchOptions[] = trim($a['batch_year']);
                }
            }
            $courseOptions = array_values(array_unique($courseOptions));
            $batchOptions = array_values(array_unique($batchOptions));
            sort($courseOptions, SORT_NATURAL | SORT_FLAG_CASE);
            sort($batchOptions, SORT_NATURAL | SORT_FLAG_CASE);
            echo view('partials.header', \get_defined_vars());
            echo view('partials.alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.alumni_list', get_defined_vars());
        });
    }
}
