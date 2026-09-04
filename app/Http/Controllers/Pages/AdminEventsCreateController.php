<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminEventsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $msg = (string) session('status', '');
            $error = session('errors')?->first() ?? '';
            echo view('partials.header', get_defined_vars());
            echo view('partials.admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.events_create', get_defined_vars());
        });
    }
}
