<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\PrivateUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class DestroyEventController extends Controller
{
    public function __invoke(Event $event): RedirectResponse
    {
        Gate::authorize('delete', $event);

        $image = $event->image;
        $event->delete();
        PrivateUploads::delete('events', $image);

        $route = request()->user()->role === 'admin' ? 'admin.events_list' : 'alumni_officer.events_list';

        return to_route($route)->with('status', 'Event deleted successfully.');
    }
}
