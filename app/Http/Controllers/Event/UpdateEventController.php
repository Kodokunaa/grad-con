<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\SocialFeedService;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateEventController extends Controller
{
    public function __invoke(UpdateEventRequest $request, Event $event)
    {
        Gate::authorize('update', $event);
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['post_start_date'] = $request->filled('post_start_date') ? $request->date('post_start_date') : null;
        $data['post_end_date'] = $request->filled('post_end_date') ? $request->date('post_end_date') : null;
        $oldImage = $event->image;
        $newImage = null;

        if ($file = $request->file('image')) {
            $newImage = 'event_'.$event->id.'_'.Str::uuid().'.'.$file->extension();
            if (! PrivateUploads::store($file, 'events', $newImage)) {
                throw ValidationException::withMessages(['image' => 'The event image could not be stored. Please try again.']);
            }
            $data['image'] = $newImage;
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        try {
            $event->update($data);
        } catch (\Throwable $exception) {
            PrivateUploads::delete('events', $newImage);
            throw $exception;
        }

        if (array_key_exists('image', $data) && $data['image'] !== $oldImage) {
            PrivateUploads::delete('events', $oldImage);
        }
        SocialFeedService::forgetEventCache();

        $route = $request->user()->role === 'admin' ? 'admin.events_edit' : 'alumni_officer.events_edit';

        return to_route($route, ['id' => $event->id])->with('status', 'Event updated successfully.');
    }

    public function legacy(UpdateEventRequest $request)
    {
        return $this($request, Event::findOrFail($request->integer('id')));
    }
}
