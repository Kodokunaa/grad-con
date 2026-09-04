<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniOfficerEventsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('alumni_officer');
            $msg = (string) session('status', '');
            $error = session('errors')?->first() ?? '';
            echo \gc_partial('header', get_defined_vars());
            echo \gc_partial('alumni_officer_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni_officer.events_create', get_defined_vars());
        });
    }
}
