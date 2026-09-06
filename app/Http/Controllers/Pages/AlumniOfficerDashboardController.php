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
            $officer_id = (int) request()->user()->id;
            $fullname = request()->user()?->fullname ?? request()->user()?->username ?? 'Alumni Officer';
            $totalEvents = 0;
            $activeEvents = 0;
            $scheduledEvents = 0;
            $recentEvents = [];
            $error = '';
            $snapshot = Cache::remember('dashboard.officer.'.$officer_id.'.v2', config('performance.dashboard_cache_seconds'), function () use ($officer_id) {
                $eventStats = Event::query()->selectRaw('COUNT(*) total, SUM(CASE WHEN posted_by = ? THEN 1 ELSE 0 END) mine, SUM(CASE WHEN is_archived = 0 AND (post_start_date IS NULL OR post_start_date <= ?) AND (post_end_date IS NULL OR post_end_date >= ?) THEN 1 ELSE 0 END) active, SUM(CASE WHEN is_archived = 0 AND post_start_date > ? THEN 1 ELSE 0 END) scheduled, SUM(CASE WHEN is_archived = 1 THEN 1 ELSE 0 END) archived', [$officer_id, now(), now(), now()])->first()->toArray();
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
            $myEvents = (int) $eventStats->mine;
            $archivedEvents = (int) $eventStats->archived;
            $recentEvents = $snapshot['recentEvents'];

            return $this->pageView('pages.alumni_officer.dashboard', get_defined_vars());
        });
    }
}
