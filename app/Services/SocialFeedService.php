<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Job;
use App\Models\PostComment;
use App\Models\PostNotification;
use App\Models\PostReaction;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SocialFeedService
{
    public const REACTIONS = ['like' => ['emoji' => '👍', 'label' => 'Like'], 'love' => ['emoji' => '❤️', 'label' => 'Love'], 'haha' => ['emoji' => '😂', 'label' => 'Haha'], 'angry' => ['emoji' => '😡', 'label' => 'Angry']];

    public function postsFor(User $user, bool $eventsOnly = false): array
    {
        $events = Event::query()->where('is_archived', false)
            ->where(fn ($q) => $q->whereNull('post_start_date')->orWhere('post_start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('post_end_date')->orWhere('post_end_date', '>=', now()))
            ->with(['author', 'reactions', 'comments.author'])->get()->map(fn ($event) => $this->postArray($event, 'event'));
        $posts = $events;
        if (! $eventsOnly) {
            $trainings = Training::query()->where(fn ($q) => $q->where('target_course', $user->course)->orWhere('target_course', 'Open for All'))
                ->with('author')->get()->map(function ($training) {
                    $training->setRelation('reactions', PostReaction::where('post_type', 'training')->where('post_id', $training->id)->get());
                    $training->setRelation('comments', PostComment::where('post_type', 'training')->where('post_id', $training->id)->with('author')->get());
                    return $this->postArray($training, 'training');
                });
            $posts = $posts->concat($trainings);
        }
        return $posts->sortByDesc('created_at')->values()->map(function ($post) use ($user) {
            $post['user_reaction'] = collect($post['reactions'])->firstWhere('user_id', $user->id)['reaction_type'] ?? '';
            unset($post['reactions']);
            return $post;
        })->all();
    }

    public function sidebarJobs(): array
    {
        return Job::query()->where('is_open', true)->latest('id')->limit(5)->get(['id', 'title', 'employer_company', 'location', 'description'])->toArray();
    }

    public function mentionUsers(): array
    {
        return User::query()->whereNotNull('fullname')->where('fullname', '<>', '')->orderBy('fullname')->get(['id', 'fullname'])->map(fn ($u) => ['id' => $u->id, 'name' => $u->fullname])->all();
    }

    public function toggleReaction(User $user, string $type, int $id, string $reaction): array
    {
        $this->post($type, $id);
        return DB::transaction(function () use ($user, $type, $id, $reaction) {
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
    }

    public function comment(User $user, string $type, int $id, string $text, ?int $parentId): PostComment
    {
        $post = $this->post($type, $id);
        return DB::transaction(function () use ($user, $type, $id, $text, $parentId, $post) {
            $parent = $parentId ? PostComment::where(['id' => $parentId, 'post_type' => $type, 'post_id' => $id])->firstOrFail() : null;
            if ($parent?->parent_comment_id) $parent = $parent->parent;
            $comment = new PostComment;
            $comment->forceFill(['post_type' => $type, 'post_id' => $id, 'parent_comment_id' => $parent?->id, 'user_id' => $user->id, 'comment' => $text, 'created_at' => now()])->save();
            $recipients = collect([$post->posted_by, $parent?->user_id])->merge($this->mentionedUserIds($text))->filter(fn ($id) => $id && (int) $id !== $user->id)->unique();
            foreach ($recipients as $recipient) {
                $notification = new PostNotification;
                $notification->forceFill(['recipient_user_id' => $recipient, 'sender_user_id' => $user->id, 'post_type' => $type, 'post_id' => $id, 'notification_type' => 'comment', 'message' => $user->fullname.' commented on '.$type.': '.$post->title, 'created_at' => now()])->save();
            }
            return $comment;
        });
    }

    public function deleteComment(User $user, PostComment $comment): void
    {
        abort_unless((int) $comment->user_id === $user->id || in_array($user->role, ['admin', 'alumni_officer'], true), 403);
        DB::transaction(function () use ($comment) { $comment->replies()->delete(); $comment->delete(); });
    }

    private function post(string $type, int $id): Event|Training
    {
        abort_unless(in_array($type, ['event', 'training'], true), 404);
        $post = ($type === 'event' ? Event::query() : Training::query())->findOrFail($id);
        if ($type === 'event') abort_unless(! $post->is_archived && (! $post->post_start_date || $post->post_start_date <= now()) && (! $post->post_end_date || $post->post_end_date >= now()), 404);
        return $post;
    }

    private function reactionCounts(string $type, int $id): array
    {
        $counts = array_fill_keys(array_keys(self::REACTIONS), 0);
        PostReaction::where(['post_type' => $type, 'post_id' => $id])->selectRaw('reaction_type, count(*) total')->groupBy('reaction_type')->pluck('total', 'reaction_type')->each(fn ($total, $reaction) => $counts[$reaction] = (int) $total);
        $counts['total'] = array_sum($counts);
        return $counts;
    }

    private function postArray($post, string $type): array
    {
        $reactions = $post->reactions ?? collect();
        $comments = $post->comments ?? collect();
        return array_merge($post->toArray(), ['post_type' => $type, 'poster' => $post->author?->fullname ?? 'GradConn', 'poster_photo' => $post->author?->profile_picture, 'reactions' => $reactions->toArray(), 'counts' => $this->reactionCounts($type, $post->id), 'comments' => $comments->map(fn ($c) => array_merge($c->toArray(), ['fullname' => $c->author?->fullname, 'profile_photo' => $c->author?->profile_picture]))->all()]);
    }

    private function mentionedUserIds(string $text): Collection
    {
        preg_match_all('/@([A-Za-z0-9_ .\-]+)/u', $text, $matches);
        $names = collect($matches[1] ?? [])->map(fn ($n) => mb_strtolower(trim(preg_replace('/\s+/', ' ', $n))))->filter();
        if ($names->isEmpty()) return collect();
        return User::whereNotNull('fullname')->get(['id', 'fullname'])->filter(function ($user) use ($names) {
            $full = mb_strtolower(trim(preg_replace('/\s+/', ' ', $user->fullname)));
            return $names->contains(fn ($name) => $full === $name || str_contains($full, $name) || str_contains($name, $full));
        })->pluck('id');
    }
}
