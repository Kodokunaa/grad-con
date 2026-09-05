@php
    $navbarUser = auth()->user();
    $navbarName = $currentUserName ?? $navbarUser?->fullname ?? $navbarUser?->username ?? 'GradConn User';
    $navbarRole = $currentUserRoleLabel ?? match ($navbarUser?->role) {
        'admin' => 'Administrator',
        'alumni_officer' => 'Alumni Officer',
        'employer' => 'Employer',
        'alumni' => 'Alumni',
        default => 'Account',
    };
@endphp

@once
    <link rel="stylesheet" href="/css/navbar.css">
@endonce

<header class="app-header gradconn-navbar">
    <div class="gradconn-navbar__left">
        <button class="mobile-sidebar-toggle" type="button" data-sidebar-toggle aria-controls="appSidebar" aria-expanded="false" aria-label="Open navigation">☰</button>
        <a class="gradconn-navbar__brand" href="{{ url('/') }}">
            <span>GradConn</span>
            <small>{{ $navbarRole }}</small>
        </a>
    </div>
    <div class="gradconn-navbar__right">
        <div class="gradconn-navbar__user">
            <span class="gradconn-navbar__avatar">{{ strtoupper(mb_substr(trim($navbarName), 0, 1)) }}</span>
            <span class="gradconn-navbar__name">{{ $navbarName }}</span>
        </div>
        <button class="gradconn-navbar__logout" type="button" data-logout-trigger>Logout</button>
    </div>
</header>
<div class="mobile-sidebar-overlay" aria-hidden="true"></div>

@once
    <script src="/js/sidebar.js" defer></script>
@endonce
