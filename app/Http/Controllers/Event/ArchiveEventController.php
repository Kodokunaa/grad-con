<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\SocialFeedService;
use Illuminate\Support\Facades\Gate;

final class ArchiveEventController extends Controller
{
    public function __invoke(Event $event)
    {
        Gate::authorize('update', $event);
        abort_if($event->is_archived, 422, 'Event is already archived.');

        $event->forceFill(['is_archived' => true, 'archived_at' => now()])->save();
        SocialFeedService::forgetEventCache();
        $route = request()->user()->role === 'admin' ? 'admin.events_list' : 'alumni_officer.events_list';

        return to_route($route)->with('status', 'Event archived successfully.');
    }
}
