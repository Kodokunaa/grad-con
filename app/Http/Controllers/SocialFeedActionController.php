<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostCommentRequest;
use App\Http\Requests\ToggleReactionRequest;
use App\Models\PostComment;
use App\Services\SocialFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class SocialFeedActionController extends Controller
{
    public function reaction(ToggleReactionRequest $request, string $type, int $post, SocialFeedService $feed): JsonResponse|RedirectResponse
    {
        $result = $feed->toggleReaction($request->user(), $type, $post, $request->string('reaction_type')->toString());

        return $request->expectsJson() ? response()->json(['success' => true] + $result) : back();
    }

    public function comment(StorePostCommentRequest $request, string $type, int $post, SocialFeedService $feed): RedirectResponse
    {
        $feed->comment($request->user(), $type, $post, $request->string('comment')->trim()->toString(), $request->integer('parent_comment_id') ?: null);

        return back()->with('status', 'Comment posted successfully.');
    }

    public function destroyComment(PostComment $comment, SocialFeedService $feed): RedirectResponse
    {
        $feed->deleteComment(request()->user(), $comment);

        return back()->with('status', 'Comment deleted successfully.');
    }
}
