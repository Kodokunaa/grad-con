<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Training;
use Illuminate\Http\Request;

final class AdminTrainingsListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $trainings = Training::query()->with('author')->latest('id')->get()->map(function ($training) {
                $row = $training->toArray();
                $row['fullname'] = $training->author?->fullname;

                return $row;
            })->all();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.trainings_list', get_defined_vars());
        });
    }
}
