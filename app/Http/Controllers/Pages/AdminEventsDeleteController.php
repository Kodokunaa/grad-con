<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class AdminEventsDeleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $event = DB::table('events')->select('id', 'image')->find($request->integer('id'));
        if (! $event) {
            return redirect('/admin/events_list.php');
        }

        DB::transaction(fn () => DB::table('events')->where('id', $event->id)->delete());
        if ($event->image) {
            Storage::disk('local')->delete('files/uploads/events/'.basename($event->image));
        }

        return redirect('/admin/events_list.php?deleted=1');
    }
}
