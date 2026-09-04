<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class EmployerPostJobController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $user = $request->user();
            $msg = session('status', '');
            $error = '';
            $mail_notice = '';
            $employer_fullname = $user->fullname;
            $employer_email = $user->email;
            $employer_profile_address = (string) $user->address;
            $employer_branches = \App\Support\ViewFormatter::employer_post_job_parse_branch_locations((string) $user->branch_location);
            $default_location = $employer_profile_address;
            $selected_branch_location = old('branch_location', '');
            $display_location = $selected_branch_location ?: old('location', $default_location);

            return $this->pageView('pages.employer.post_job', get_defined_vars());
        });
    }
}
