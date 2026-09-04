<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminCreateEmployerController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $success = (string) $request->session()->get('status', '');
            $error = $request->session()->get('errors')?->first() ?? '';

            return $this->pageView('pages.admin.create_employer', get_defined_vars());
        });
    }
}
