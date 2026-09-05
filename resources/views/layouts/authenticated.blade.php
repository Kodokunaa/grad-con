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
@include('partials.role-navbar')
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
