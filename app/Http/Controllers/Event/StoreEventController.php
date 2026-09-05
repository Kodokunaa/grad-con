<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class StoreEventController extends Controller
{
    public function __invoke(StoreEventRequest $request)
    {
        $data = $request->safe()->except('image');
        $data['posted_by'] = $request->user()->id;
        $data['post_start_date'] = $request->filled('post_start_date') ? $request->date('post_start_date') : null;
        $data['post_end_date'] = $request->filled('post_end_date') ? $request->date('post_end_date') : null;

        if ($file = $request->file('image')) {
            $data['image'] = 'event_'.Str::uuid().'.'.$file->extension();
            if (! PrivateUploads::store($file, 'events', $data['image'])) {
                throw ValidationException::withMessages(['image' => 'The event image could not be stored. Please try again.']);
            }
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
