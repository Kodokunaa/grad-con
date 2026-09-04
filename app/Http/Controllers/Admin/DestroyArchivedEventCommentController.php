<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PostComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class DestroyArchivedEventCommentController extends Controller
{
    public function __invoke(Event $event, PostComment $comment): RedirectResponse
    {
        abort_unless($event->is_archived && $comment->post_type === 'event' && (int) $comment->post_id === $event->id, 404);
        Gate::authorize('update', $event);
        $comment->replies()->delete();
        $comment->delete();
        return to_route('admin.admin_archive')->with('status', 'Comment deleted successfully.');
    }
}
