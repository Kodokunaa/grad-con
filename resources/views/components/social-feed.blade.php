@props(['posts', 'manageEvents' => false, 'mentionUsers' => []])
@php
    $mentionNames = collect($mentionUsers)->pluck('name')->filter()->sortByDesc(fn ($name) => mb_strlen($name))->values();
    $mentionPattern = $mentionNames->isEmpty() ? null : '/(@(?:'.implode('|', $mentionNames->map(fn ($name) => preg_quote($name, '/'))->all()).'))(?=$|[\s,.!?;:])/iu';
    $highlightMentions = fn ($text) => !$mentionPattern ? e($text) : collect(preg_split($mentionPattern, (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE))->map(fn ($part) => preg_match($mentionPattern, $part) ? '<span class="feed-mention">'.e($part).'</span>' : e($part))->join('');
@endphp
<div class="feed">
@forelse($posts as $post)
    @php($key = $post['post_type'].'-'.$post['id'])
    <article class="post" id="post-{{ $key }}">
        <header><div class="avatar">{{ strtoupper(substr($post['poster'] ?? 'G', 0, 1)) }}</div><div><strong>{{ $post['poster'] }}</strong><small>{{ optional(\Carbon\Carbon::parse($post['created_at']))->diffForHumans() }}</small></div></header>
        <h2>{{ $post['title'] }}</h2><p>{!! nl2br(e($post['content'] ?? $post['description'] ?? '')) !!}</p>
        @if(!empty($post['image']))<img class="post-image" src="{{ url('/uploads/events/'.basename($post['image'])) }}" alt="">@endif
        <div class="engagement"><span data-counts>{{ $post['counts']['total'] }} reactions</span><span>{{ $post['comment_count'] }} comments</span></div>
        <form method="POST" action="{{ url('/feed/'.$post['post_type'].'/'.$post['id'].'/reaction') }}" class="reaction-form">@csrf
            <select name="reaction_type" aria-label="Reaction">@foreach(\App\Services\SocialFeedService::REACTIONS as $type=>$reaction)<option value="{{ $type }}" @selected($post['user_reaction']===$type)>{{ $reaction['emoji'] }} {{ $reaction['label'] }}</option>@endforeach</select><button>React</button>
        </form>
        <section class="comments">
        @foreach($post['comments'] as $comment)
            <div class="comment"><strong>{{ $comment['fullname'] ?? 'User' }}</strong><p>{!! $highlightMentions($comment['comment']) !!}</p>
            <button type="button" class="link" data-reply-toggle="management-reply-{{ $comment['id'] }}" data-reply-name="{{ $comment['fullname'] ?? 'User' }}">Reply</button>
            @if((int)$comment['user_id']===auth()->id() || in_array(auth()->user()->role,['admin','alumni_officer'],true))
                <form method="POST" action="{{ url('/feed/comments/'.$comment['id']) }}">@csrf @method('DELETE')<button class="link danger">Delete</button></form>
            @endif
            @foreach($comment['replies'] as $reply)
                <div class="comment" style="margin-left:2rem"><strong>{{ $reply['fullname'] ?? 'User' }}</strong><p>{!! $highlightMentions($reply['comment']) !!}</p>
                @if((int)$reply['user_id']===auth()->id() || in_array(auth()->user()->role,['admin','alumni_officer'],true))<form method="POST" action="{{ url('/feed/comments/'.$reply['id']) }}">@csrf @method('DELETE')<button class="link danger">Delete</button></form>@endif</div>
            @endforeach
            <form method="POST" action="{{ url('/feed/'.$post['post_type'].'/'.$post['id'].'/comments') }}" id="management-reply-{{ $comment['id'] }}" class="comment-form" hidden>@csrf<input type="hidden" name="parent_comment_id" value="{{ $comment['id'] }}"><textarea name="comment" required maxlength="3000" placeholder="Write a reply" data-mention-input></textarea><button>Reply</button></form>
            </div>
        @endforeach
        </section>
        <form method="POST" action="{{ url('/feed/'.$post['post_type'].'/'.$post['id'].'/comments') }}" class="comment-form">@csrf<textarea name="comment" required maxlength="3000" placeholder="Write a comment" data-mention-input></textarea><button>Post</button></form>
        @if($manageEvents && $post['post_type']==='event')<form method="POST" action="{{ route('events.archive',$post['id']) }}">@csrf @method('PATCH')<button class="danger">Archive event</button></form>@endif
    </article>
@empty
    <div class="empty-state">
        <div class="empty-state__icon" aria-hidden="true">◇</div>
        <h2>No posts yet</h2>
        <p>Event announcements will appear here when they are published.</p>
    </div>
@endforelse
</div>
@once
    @push('scripts')<script>window.gradconnMentionUsers = @json($mentionUsers);</script>@endpush
@endonce
