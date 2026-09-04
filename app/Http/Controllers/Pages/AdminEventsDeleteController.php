<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class AdminEventsDeleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $event = Event::find($request->integer('id'));
        if (! $event) {
            return redirect('/admin/events_list.php');
        }
        Gate::authorize('delete', $event);

        $event->delete();
        if ($event->image) {
            Storage::disk('local')->delete('files/uploads/events/'.basename($event->image));
        }

        return redirect('/admin/events_list.php?deleted=1');
    }
}
