<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            $snapshot = Cache::remember('dashboard.officer.v1', config('performance.dashboard_cache_seconds'), function () {
                $eventStats = Event::query()->selectRaw('COUNT(*) total, SUM(CASE WHEN (post_start_date IS NULL OR post_start_date <= ?) AND (post_end_date IS NULL OR post_end_date >= ?) THEN 1 ELSE 0 END) active, SUM(CASE WHEN post_start_date > ? THEN 1 ELSE 0 END) scheduled', [now(), now(), now()])->first()->toArray();
                $recentEvents = Event::query()->with('author')->latest('created_at')->latest('id')->limit(6)->get()->map(function ($event) {
                    $row = $event->toArray();
                    $row['poster_name'] = $event->author?->fullname;

                    return $row;
                })->all();

                return compact('eventStats', 'recentEvents');
            });
            $eventStats = (object) $snapshot['eventStats'];
            $totalEvents = (int) $eventStats->total;
            $activeEvents = (int) $eventStats->active;
            $scheduledEvents = (int) $eventStats->scheduled;
            $recentEvents = $snapshot['recentEvents'];
            echo view('partials.header', \get_defined_vars());
            echo view('partials.alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.dashboard', get_defined_vars());
        });
    }
}
