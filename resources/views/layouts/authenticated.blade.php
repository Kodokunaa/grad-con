<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GradConn')</title>
    <link rel="stylesheet" href="/css/authenticated.css">
    @stack('styles')
</head>
<body>
@include('partials.role-sidebar')
<header class="app-header"><div class="header-left"><button class="mobile-sidebar-toggle" type="button" data-sidebar-toggle aria-controls="appSidebar" aria-expanded="false">☰</button><strong>@yield('heading', 'GradConn')</strong></div><div class="app-user">{{ auth()->user()->fullname }} <button type="button" data-logout-trigger>Log out</button></div></header>
<div class="mobile-sidebar-overlay" aria-hidden="true"></div>
<main class="app-main">
    @if(session('status'))<div class="flash success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
    @yield('content')
</main>
@include('partials.logout-modal')
<script src="/js/request-security.js" defer></script>
<script src="/js/feed.js" defer></script>
@stack('scripts')
</body></html>
