<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Services\SocialFeedService;
use Illuminate\View\View;

final class AlumniOfficerEventsListController extends Controller
{
    public function __invoke(SocialFeedService $feed): View
    {
        return view('pages.alumni_officer.events_list', ['posts' => $feed->postsFor(request()->user(), true), 'mentionUsers' => $feed->mentionUsers()]);
    }
}

