<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class StoreEventController extends Controller
{
    public function __invoke(StoreEventRequest $request)
    {
        $data = $request->safe()->except('image');
        $data['posted_by'] = $request->user()->id;
        $data['post_start_date'] = $request->date('post_start_date');
        $data['post_end_date'] = $request->date('post_end_date');

        if ($file = $request->file('image')) {
            $data['image'] = 'event_'.Str::uuid().'.'.$file->extension();
            abort_unless(PrivateUploads::store($file, 'events', $data['image']), 500, 'Image upload failed.');
        }

        try {
            $event = new Event;
            $event->forceFill($data)->save();
        } catch (\Throwable $exception) {
            PrivateUploads::delete('events', $data['image'] ?? null);
            throw $exception;
        }
        Cache::forget('feed.events.v1');

        $route = $request->user()->role === 'admin' ? 'admin.events_create' : 'alumni_officer.events_create';

        return to_route($route)->with('status', 'Event posted successfully.');
    }
}
