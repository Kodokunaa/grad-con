<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Event;
use Illuminate\Http\Request;

final class AlumniOfficerDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $officer_id = (int) (request()->user()?->id ?? 0);
            $fullname = request()->user()?->fullname ?? request()->user()?->username ?? 'Alumni Officer';
            $totalEvents = 0;
            $activeEvents = 0;
            $scheduledEvents = 0;
            $recentEvents = [];
            $error = '';
            $totalEvents = Event::query()->count();
            $activeEvents = Event::query()->where(fn ($q) => $q->whereNull('post_start_date')->orWhere('post_start_date', '<=', now()))->where(fn ($q) => $q->whereNull('post_end_date')->orWhere('post_end_date', '>=', now()))->count();
            $scheduledEvents = Event::query()->where('post_start_date', '>', now())->count();
            $recentEvents = Event::query()->with('author')->latest('created_at')->latest('id')->limit(6)->get()->map(function ($event) {
                $row = $event->toArray();
                $row['poster_name'] = $event->author?->fullname;

                return $row;
            })->all();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.dashboard', get_defined_vars());
        });
    }
}
