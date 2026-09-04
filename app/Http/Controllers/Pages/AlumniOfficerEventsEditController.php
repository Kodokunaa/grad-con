<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class AlumniOfficerEventsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $id = request()->integer('id');
            $model = Event::findOrFail($id);
            Gate::authorize('update', $model);
            $event = $model->toArray();
            $msg = (string) session('status', '');
            $error = session('errors')?->first() ?? '';
            echo view('partials.header', get_defined_vars());
            echo view('partials.alumni_officer_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni_officer.events_edit', get_defined_vars());
        });
    }
}
