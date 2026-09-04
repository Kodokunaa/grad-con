<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\JobApplication;
use Illuminate\Http\Request;

final class AdminForwardToCompanyController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $app_id = (int) (request()->query('app_id') ?? 0);
            $application = JobApplication::with(['alumni', 'job'])->findOrFail($app_id);
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.forward_to_company', get_defined_vars());
        });
    }
}
