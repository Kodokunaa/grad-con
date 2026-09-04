<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniChangePasswordController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $msg = (string) $request->session()->get('status', '');
            $error = $request->session()->get('errors')?->first() ?? '';

            echo view('partials.header', get_defined_vars());
            echo view('partials.alumni_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni.change_password', get_defined_vars());
        });
    }
}
