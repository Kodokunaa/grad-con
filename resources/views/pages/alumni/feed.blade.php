@extends('layouts.authenticated')
@section('title', 'Community Feed · GradConn')
@push('styles')<link rel="stylesheet" href="/css/alumni-feed.css">@endpush

@section('content')
@php
    $viewer = auth()->user();
    $initials = fn ($name) => collect(preg_split('/\\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('') ?: 'U';
    $avatar = fn ($photo) => $photo ? url('/uploads/profiles/'.basename($photo)) : null;
    $shorten = fn ($text, $limit = 115) => mb_strlen(trim(strip_tags((string) $text))) > $limit ? mb_substr(trim(strip_tags((string) $text)), 0, $limit).'…' : (trim(strip_tags((string) $text)) ?: 'No description provided.');
@endphp

<div class="alumni-feed-page">
    <div class="feed-layout">
        <section class="feed-column" aria-label="Community posts">
            <header class="feed-welcome">
                <div><span class="feed-eyebrow">GradConn Community</span><h1>Community Feed</h1><p>Events, announcements, and opportunities from your alumni community.</p></div>
                <a class="feed-jobs-link" href="{{ route('alumni.jobs') }}"><i class="fas fa-briefcase"></i> Browse jobs</a>
            </header>

            @forelse($posts as $post)
                @php
                    $postKey = 'event-'.$post['id'];
                    $selected = $post['user_reaction'] ?: 'like';
                    $selectedInfo = \App\Services\SocialFeedService::REACTIONS[$selected];
                    $posterAvatar = $avatar($post['poster_photo'] ?? null);
                    $category = in_array($post['category'] ?? '', ['announcement','news','event'], true) ? $post['category'] : 'announcement';
                    $categoryIcon = ['announcement' => 'fa-bullhorn', 'news' => 'fa-newspaper', 'event' => 'fa-calendar-day'][$category];
                    preg_match('~https?://[^\\s<]+~i', (string) ($post['content'] ?? ''), $postLinks);
                    $postLink = isset($postLinks[0]) ? rtrim($postLinks[0], '.,);]') : null;
                    $postLinkHost = $postLink ? parse_url($postLink, PHP_URL_HOST) : null;
                @endphp
                <article class="feed-post" id="post-{{ $postKey }}" data-post="{{ $postKey }}">
                    <header class="feed-post__header">
                        <div class="feed-person">
                            <div class="feed-avatar">@if($posterAvatar)<img src="{{ $posterAvatar }}" alt="{{ $post['poster'] }} profile photo">@else{{ $initials($post['poster']) }}@endif</div>
                            <div><strong>{{ $post['poster'] }}</strong><time datetime="{{ $post['created_at'] }}">{{ optional(\Carbon\Carbon::parse($post['created_at']))->format('F j, Y \\a\\t g:i A') }}</time></div>
                        </div>
                        <span class="feed-type"><i class="fas {{ $categoryIcon }}"></i> {{ ucfirst($category) }}</span>
                    </header>

                    <div class="feed-post__body">
                        <h2>{{ $post['title'] }}</h2>
                        @if(!empty($post['post_start_date']) || !empty($post['post_end_date']))
                            <div class="feed-schedule">
                                <span><i class="fas fa-play"></i> {{ $post['post_start_date'] ? \Carbon\Carbon::parse($post['post_start_date'])->format('M j, Y · g:i A') : 'Available now' }}</span>
                                <span><i class="fas fa-flag-checkered"></i> {{ $post['post_end_date'] ? \Carbon\Carbon::parse($post['post_end_date'])->format('M j, Y · g:i A') : 'No end date' }}</span>
                            </div>
                        @endif
                        <p>{!! nl2br(e($post['content'] ?? '')) !!}</p>
                        @if($postLink && $postLinkHost)
                            <a class="feed-link-preview" href="{{ $postLink }}" target="_blank" rel="noopener noreferrer">
                                <span><i class="fas fa-link"></i> Shared website</span>
                                <strong>{{ $postLinkHost }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($postLink, 95) }}</small>
                            </a>
                        @endif
                    </div>

                    @if(!empty($post['image']))
                        <button class="feed-image-button" type="button" data-feed-image="{{ url('/uploads/events/'.basename($post['image'])) }}" aria-label="Open event image"><img src="{{ url('/uploads/events/'.basename($post['image'])) }}" alt="{{ $post['title'] }}"></button>
                    @endif

                    <div class="feed-engagement">
                        <span class="feed-reaction-total" data-counts><span class="reaction-stack">@foreach(\App\Services\SocialFeedService::REACTIONS as $type => $info)@if(($post['counts'][$type] ?? 0) > 0)<b>{{ $info['emoji'] }}</b>@endif @endforeach</span><span data-count-label>{{ $post['counts']['total'] }} {{ \Illuminate\Support\Str::plural('reaction', $post['counts']['total']) }}</span></span>
                        <button type="button" class="feed-comment-count" data-comment-toggle="comments-{{ $postKey }}">{{ $post['comment_count'] }} {{ \Illuminate\Support\Str::plural('comment', $post['comment_count']) }}</button>
                    </div>

                    <div class="feed-actions">
                        <form method="POST" action="{{ url('/feed/event/'.$post['id'].'/reaction') }}" class="feed-reaction-form">
                            @csrf
                            <div class="reaction-control">
                                <div class="reaction-picker" role="group" aria-label="Choose a reaction">
                                    @foreach(\App\Services\SocialFeedService::REACTIONS as $type => $info)<button type="submit" name="reaction_type" value="{{ $type }}" data-reaction="{{ $type }}" title="{{ $info['label'] }}">{{ $info['emoji'] }}</button>@endforeach
                                </div>
                                <button type="submit" name="reaction_type" value="{{ $selected }}" class="feed-action reaction-main is-{{ $post['user_reaction'] ?: 'none' }}" data-main-reaction><span data-reaction-emoji>{{ $selectedInfo['emoji'] }}</span><span data-reaction-label>{{ $post['user_reaction'] ? $selectedInfo['label'] : 'Like' }}</span></button>
                            </div>
                        </form>
                        <button class="feed-action" type="button" data-comment-toggle="comments-{{ $postKey }}" data-comment-focus="comment-{{ $postKey }}"><i class="far fa-comment"></i> Comment</button>
                    </div>

                    <section class="feed-comments is-collapsed" id="comments-{{ $postKey }}">
                        <form method="POST" action="{{ url('/feed/event/'.$post['id'].'/comments') }}" class="feed-comment-form" data-comments-list="comments-list-{{ $postKey }}">
                            @csrf
                            <div class="feed-avatar feed-avatar--small">@if($avatar($viewer->profile_picture))<img src="{{ $avatar($viewer->profile_picture) }}" alt="">@else{{ $initials($viewer->fullname) }}@endif</div>
                            <div class="comment-composer"><input id="comment-{{ $postKey }}" name="comment" maxlength="3000" required autocomplete="off" placeholder="Write a comment…" data-mention-input><button type="submit" aria-label="Post comment"><i class="fas fa-paper-plane"></i></button></div>
                        </form>
                        <div class="feed-comments__list" id="comments-list-{{ $postKey }}">
                            @forelse($post['comments'] as $comment)
                                <div class="feed-comment-thread" data-thread-id="{{ $comment['id'] }}">
                                <div class="feed-comment" data-comment-id="{{ $comment['id'] }}">
                                    <div class="feed-avatar feed-avatar--small">@if($avatar($comment['profile_photo'] ?? null))<img src="{{ $avatar($comment['profile_photo']) }}" alt="">@else{{ $initials($comment['fullname'] ?? 'User') }}@endif</div>
                                    <div class="feed-comment__content"><div class="feed-comment__bubble"><strong>{{ $comment['fullname'] ?? 'User' }}</strong><p>{{ $comment['comment'] }}</p></div><div class="feed-comment__meta"><small>{{ \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() }}</small><button type="button" data-reply-toggle="reply-form-{{ $comment['id'] }}" data-reply-name="{{ $comment['fullname'] ?? 'User' }}">Reply</button></div></div>
                                </div>
                                <div class="feed-replies" data-replies-for="{{ $comment['id'] }}">
                                    @foreach($comment['replies'] as $reply)
                                        <div class="feed-comment feed-comment--reply" data-comment-id="{{ $reply['id'] }}">
                                            <div class="feed-avatar feed-avatar--small">@if($avatar($reply['profile_photo'] ?? null))<img src="{{ $avatar($reply['profile_photo']) }}" alt="">@else{{ $initials($reply['fullname'] ?? 'User') }}@endif</div>
                                            <div class="feed-comment__content"><div class="feed-comment__bubble"><strong>{{ $reply['fullname'] ?? 'User' }}</strong><p>{{ $reply['comment'] }}</p></div><small>{{ \Carbon\Carbon::parse($reply['created_at'])->diffForHumans() }}</small></div>
                                        </div>
                                    @endforeach
                                </div>
                                <form method="POST" action="{{ url('/feed/event/'.$post['id'].'/comments') }}" id="reply-form-{{ $comment['id'] }}" class="feed-comment-form feed-reply-form" data-comments-list="comments-list-{{ $postKey }}" data-replies-list="{{ $comment['id'] }}" hidden>
                                    @csrf<input type="hidden" name="parent_comment_id" value="{{ $comment['id'] }}">
                                    <div class="feed-avatar feed-avatar--small">@if($avatar($viewer->profile_picture))<img src="{{ $avatar($viewer->profile_picture) }}" alt="">@else{{ $initials($viewer->fullname) }}@endif</div>
                                    <div class="comment-composer"><input name="comment" maxlength="3000" required autocomplete="off" placeholder="Write a reply…" data-mention-input><button type="submit" aria-label="Post reply"><i class="fas fa-paper-plane"></i></button></div>
                                </form>
                                </div>
                            @empty
                                <p class="no-comments" data-empty-comments>Be the first to comment.</p>
                            @endforelse
                        </div>
                    </section>
                </article>
            @empty
                <div class="feed-empty"><div class="empty-state"><div><i class="far fa-calendar"></i></div><h2>No community posts yet</h2><p>New announcements and events will appear here.</p></div></div>
            @endforelse
        </section>

        <aside class="feed-rail">
            <section class="feed-rail-card">
                <div class="rail-heading"><h2>Job Opportunities</h2><a href="{{ route('alumni.jobs') }}">See all</a></div>
                <div class="rail-jobs" data-job-list>
                    @forelse($sidebarJobs as $job)
                        <article class="rail-job {{ $loop->index > 1 ? 'rail-job--extra' : '' }}"><span class="rail-job__icon"><i class="fas fa-building"></i></span><div><h3>{{ $job['title'] ?? 'Job opening' }}</h3><p>{{ $job['employer_company'] ?? 'Company' }}@if(!empty($job['location'])) · {{ $job['location'] }}@endif</p><small>{{ $shorten($job['description'] ?? '') }}</small><a href="{{ route('alumni.job_details', ['id' => $job['id']]) }}">View opportunity <i class="fas fa-arrow-right"></i></a></div></article>
                    @empty
                        <p class="rail-empty">No open positions right now.</p>
                    @endforelse
                </div>
                @if(count($sidebarJobs) > 2)<button class="rail-toggle" type="button" data-job-toggle>Show more</button>@endif
            </section>
            <section class="feed-rail-card rail-community"><h2>Stay connected</h2><p>Keep your profile current so employers and fellow graduates can recognize you.</p><a href="{{ route('profile') }}"><i class="fas fa-user-pen"></i> Update profile</a></section>
        </aside>
    </div>
</div>

<div class="mention-menu" data-mention-menu hidden></div>
<div class="feed-toast" data-feed-toast role="status" aria-live="polite"></div>
<div class="feed-lightbox" data-feed-lightbox aria-hidden="true"><button type="button" aria-label="Close image preview">×</button><img src="" alt="Event preview"></div>
@endsection

@push('scripts')<script>window.gradconnMentionUsers = @json($mentionUsers);</script>@endpush

