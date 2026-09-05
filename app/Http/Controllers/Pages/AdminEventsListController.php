<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Services\SocialFeedService;
use Illuminate\View\View;

final class AdminEventsListController extends Controller
{
    public function __invoke(SocialFeedService $feed): View
    {
        return view('pages.admin.events_list', ['posts' => $feed->postsFor(request()->user()), 'mentionUsers' => $feed->mentionUsers()]);
    }
}
