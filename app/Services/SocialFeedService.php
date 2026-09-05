<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Job;
use App\Models\PostComment;
use App\Models\PostNotification;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SocialFeedService
{
    public const REACTIONS = ['like' => ['emoji' => '👍', 'label' => 'Like'], 'love' => ['emoji' => '❤️', 'label' => 'Love'], 'haha' => ['emoji' => '😂', 'label' => 'Haha'], 'angry' => ['emoji' => '😡', 'label' => 'Angry']];

    public function postsFor(User $user): array
    {
        $events = Cache::remember('feed.events.v1', config('performance.feed_cache_seconds'), fn () => Event::query()
            ->where('is_archived', false)
            ->where(fn ($q) => $q->whereNull('post_start_date')->orWhere('post_start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('post_end_date')->orWhere('post_end_date', '>=', now()))
            ->with(['author', 'reactions', 'comments.author'])
            ->latest('created_at')
            ->limit(config('performance.feed_limit'))
            ->get()
            ->map(fn ($event) => $this->postArray($event, 'event'))
            ->all());

        return collect($events)->map(function ($post) use ($user) {
            $post['user_reaction'] = collect($post['reactions'])->firstWhere('user_id', $user->id)['reaction_type'] ?? '';
            unset($post['reactions']);

            return $post;
        })->all();
    }

    public function sidebarJobs(): array
    {
        return Cache::remember('feed.sidebar-jobs.v1', config('performance.directory_cache_seconds'), fn () => Job::query()
            ->where('is_open', true)->latest('id')->limit(5)->get(['id', 'title', 'employer_company', 'location', 'description'])->toArray());
    }

    public function mentionUsers(): array
    {
        return Cache::remember('feed.mention-users.v1', config('performance.directory_cache_seconds'), fn () => User::query()
            ->whereNotNull('fullname')->where('fullname', '<>', '')->orderBy('fullname')->get(['id', 'fullname'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->fullname])->all());
    }

    public function toggleReaction(User $user, string $type, int $id, string $reaction): array
    {
        $this->post($type, $id);

        $result = DB::transaction(function () use ($user, $type, $id, $reaction) {
            $existing = PostReaction::where(['post_type' => $type, 'post_id' => $id, 'user_id' => $user->id])->first();
            if ($existing?->reaction_type === $reaction) {
                $existing->delete();
                $selected = '';
            } else {
                $existing ??= new PostReaction;
                $existing->forceFill(['post_type' => $type, 'post_id' => $id, 'user_id' => $user->id, 'reaction_type' => $reaction, 'created_at' => now()])->save();
                $selected = $reaction;
            }

            return ['reaction' => $selected, 'counts' => $this->reactionCounts($type, $id)];
        });
        Cache::forget('feed.events.v1');

        return $result;
    }

    public function comment(User $user, string $type, int $id, string $text, ?int $parentId): PostComment
    {
        $post = $this->post($type, $id);

        $comment = DB::transaction(function () use ($user, $type, $id, $text, $parentId, $post) {
            $parent = $parentId ? PostComment::where(['id' => $parentId, 'post_type' => $type, 'post_id' => $id])->firstOrFail() : null;
            if ($parent?->parent_comment_id) {
                $parent = $parent->parent;
            }
            $comment = new PostComment;
            $comment->forceFill(['post_type' => $type, 'post_id' => $id, 'parent_comment_id' => $parent?->id, 'user_id' => $user->id, 'comment' => $text, 'created_at' => now()])->save();
            $recipients = collect([$post->posted_by, $parent?->user_id])->merge($this->mentionedUserIds($text))->filter(fn ($id) => $id && (int) $id !== $user->id)->unique();
            foreach ($recipients as $recipient) {
                $notification = new PostNotification;
                $notification->forceFill(['recipient_user_id' => $recipient, 'sender_user_id' => $user->id, 'post_type' => $type, 'post_id' => $id, 'notification_type' => 'comment', 'message' => $user->fullname.' commented on '.$type.': '.$post->title, 'created_at' => now()])->save();
            }

            return $comment;
        });
        Cache::forget('feed.events.v1');

        return $comment;
    }

    public function deleteComment(User $user, PostComment $comment): void
    {
        abort_unless((int) $comment->user_id === $user->id || in_array($user->role, ['admin', 'alumni_officer'], true), 403);
        DB::transaction(function () use ($comment) {
            $comment->replies()->delete();
            $comment->delete();
        });
        Cache::forget('feed.events.v1');
    }

    private function post(string $type, int $id): Event
    {
        abort_unless($type === 'event', 404);
        $post = Event::query()->findOrFail($id);
        abort_unless(! $post->is_archived && (! $post->post_start_date || $post->post_start_date <= now()) && (! $post->post_end_date || $post->post_end_date >= now()), 404);

        return $post;
    }

    private function reactionCounts(string $type, int $id): array
    {
        $counts = array_fill_keys(array_keys(self::REACTIONS), 0);
        foreach (PostReaction::where(['post_type' => $type, 'post_id' => $id])->selectRaw('reaction_type, count(*) total')->groupBy('reaction_type')->pluck('total', 'reaction_type') as $reaction => $total) {
            if (array_key_exists($reaction, $counts)) {
                $counts[$reaction] = (int) $total;
            }
        }
        $counts['total'] = array_sum($counts);

        return $counts;
    }

    private function postArray($post, string $type): array
    {
        $reactions = $post->reactions ?? collect();
        $comments = $post->comments ?? collect();

        $counts = array_fill_keys(array_keys(self::REACTIONS), 0);
        foreach ($reactions->countBy('reaction_type') as $reaction => $total) {
            if (array_key_exists($reaction, $counts)) {
                $counts[$reaction] = $total;
            }
        }
        $counts['total'] = array_sum($counts);

        return array_merge($post->toArray(), ['post_type' => $type, 'poster' => $post->author?->fullname ?? 'GradConn', 'poster_photo' => $post->author?->profile_picture, 'reactions' => $reactions->toArray(), 'counts' => $counts, 'comments' => $comments->map(fn ($c) => array_merge($c->toArray(), ['fullname' => $c->author?->fullname, 'profile_photo' => $c->author?->profile_picture]))->all()]);
    }

    private function mentionedUserIds(string $text): Collection
    {
        preg_match_all('/@([A-Za-z0-9_ .\-]+)/u', $text, $matches);
        $names = collect($matches[1] ?? [])->map(fn ($n) => mb_strtolower(trim(preg_replace('/\s+/', ' ', $n))))->filter();
        if ($names->isEmpty()) {
            return collect();
        }

        return User::whereNotNull('fullname')->get(['id', 'fullname'])->filter(function ($user) use ($names) {
            $full = mb_strtolower(trim(preg_replace('/\s+/', ' ', $user->fullname)));

            return $names->contains(fn ($name) => $full === $name || str_contains($full, $name) || str_contains($name, $full));
        })->pluck('id');
    }
}
