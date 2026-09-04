<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminAlumniCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $msg = (string) $request->session()->get('status', '');
            $error = $request->session()->get('errors')?->first() ?? '';
            $course_options = config('gradconn.courses');
            echo view('partials.header', get_defined_vars());
            echo view('partials.admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.alumni_create', get_defined_vars());
        });
    }
}
