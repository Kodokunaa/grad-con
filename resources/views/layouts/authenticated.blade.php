<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GradConn')</title>
    <link rel="stylesheet" href="{{ asset('css/authenticated.css') }}">
    @stack('styles')
</head>
<body>
<aside class="app-sidebar">
    <a class="brand" href="{{ url('/') }}">GradConn</a>
    <nav>
        @php($role = auth()->user()->role)
        @if($role === 'admin')
            <a href="{{ route('admin.dashboard') }}">Dashboard</a><a href="{{ route('admin.alumni_list') }}">Alumni</a><a href="{{ route('admin.events_list') }}">Events</a><a href="{{ route('admin.jobs_list') }}">Jobs</a><a href="{{ route('admin.reports') }}">Reports</a>
        @elseif($role === 'alumni_officer')
            <a href="{{ route('alumni_officer.dashboard') }}">Dashboard</a><a href="{{ route('alumni_officer.alumni_list') }}">Alumni</a><a href="{{ route('alumni_officer.events_list') }}">Events</a><a href="{{ route('alumni_officer.archive') }}">Archive</a>
        @elseif($role === 'employer')
            <a href="{{ route('employer.dashboard') }}">Dashboard</a><a href="{{ route('employer.posted_job') }}">Jobs</a><a href="{{ route('employer.applications') }}">Applications</a><a href="{{ route('employer.job_offers') }}">Offers</a>
        @else
            <a href="{{ route('alumni.dashboard') }}">Dashboard</a><a href="{{ route('alumni.feed') }}">Feed</a><a href="{{ route('alumni.jobs') }}">Jobs</a><a href="{{ route('alumni.my_applications') }}">Applications</a><a href="{{ route('profile') }}">Profile</a>
        @endif
    </nav>
</aside>
<header class="app-header"><strong>@yield('heading', 'GradConn')</strong><div>{{ auth()->user()->fullname }} <button type="button" data-logout-open>Log out</button></div></header>
<main class="app-main">
    @if(session('status'))<div class="flash success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
    @yield('content')
</main>
@include('partials.logout-modal')
<script src="{{ asset('js/request-security.js') }}" defer></script>
<script src="{{ asset('js/feed.js') }}" defer></script>
@stack('scripts')
</body></html>
