<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Services\SocialFeedService;
use Illuminate\View\View;

final class AlumniFeedController extends Controller
{
    public function __invoke(SocialFeedService $feed): View
    {
        return view('pages.alumni.feed', ['posts' => $feed->postsFor(request()->user()), 'sidebarJobs' => $feed->sidebarJobs(), 'mentionUsers' => $feed->mentionUsers()]);
    }
}

