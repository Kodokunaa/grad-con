<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EventIndexController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $route = match ($request->user()->role) {
            'admin' => 'admin.events_list',
            'alumni_officer' => 'alumni_officer.events_list',
            'alumni' => 'alumni.feed',
            'employer' => 'employer.dashboard',
            default => null,
        };

        abort_unless($route, 403);

        return redirect()->route($route);
    }
}
