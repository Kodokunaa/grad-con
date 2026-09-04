<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminJobsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $msg = session('status', '');
            $error = '';
            $mail_notice = '';
            echo gc_partial('header', get_defined_vars());
            echo gc_partial('admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.jobs_create', get_defined_vars());
        });
    }
}
