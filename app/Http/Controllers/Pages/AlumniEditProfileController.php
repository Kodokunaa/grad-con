<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniEditProfileController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $user = $request->user()->only(['fullname', 'email', 'course', 'batch_year', 'employment_status', 'job_aligned']);
            $msg = session('status', '');
            $error = '';
            echo view('partials.header', get_defined_vars());
            echo view('partials.navbar', get_defined_vars());

            return $this->pageView('pages.alumni.edit_profile', get_defined_vars());
        });
    }
}
