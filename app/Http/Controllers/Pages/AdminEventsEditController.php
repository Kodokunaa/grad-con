<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Event;
use Illuminate\Http\Request;

final class AdminEventsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $id = request()->integer('id');
            $event = Event::findOrFail($id)->toArray();
            $msg = (string) session('status', '');
            $error = session('errors')?->first() ?? '';
            echo view('partials.header', get_defined_vars());
            echo view('partials.admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.events_edit', get_defined_vars());
        });
    }
}
