<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;

final class RestoreEventController extends Controller
{
    public function __invoke(Event $event)
    {
        Gate::authorize('update', $event);
        abort_unless($event->is_archived, 422, 'Event is not archived.');

        $event->forceFill(['is_archived' => false, 'archived_at' => null])->save();

        $route = request()->user()->role === 'admin' ? 'admin.admin_archive' : 'alumni_officer.archive';

        return to_route($route)->with('status', 'Event restored successfully.');
    }
}
