@php
    $role = auth()->user()->role;
    $pendingCount = $role === 'admin'
        ? \App\Models\User::query()->where('role', 'alumni')->where('status', 'pending')->count()
        : 0;

    $menus = [
        'admin' => [
            ['Overview', [
                ['admin.dashboard', 'fa-chart-line', 'Dashboard'],
                ['admin.graduates_stats', 'fa-chart-pie', 'Graduate Statistics'],
                ['admin.offers_history', 'fa-handshake', 'Offers History'],
            ]],
            ['Accounts', [
                ['admin.alumni_create', 'fa-user-plus', 'Create Alumni'],
                ['admin.create_employer', 'fa-building', 'Create Employer'],
                ['admin.pending_alumni', 'fa-user-clock', 'Pending Accounts', $pendingCount],
                ['admin.alumni_list', 'fa-users', 'Alumni List'],
            ]],
            ['Jobs', [
                ['admin.jobs_create', 'fa-briefcase', 'Post Job'],
                ['admin.jobs_list', 'fa-file-signature', 'Jobs & Applications'],
            ]],
            ['Events', [
                ['admin.events_create', 'fa-calendar-plus', 'Post Event'],
                ['admin.events_list', 'fa-calendar-days', 'Events Feed'],
                ['admin.admin_archive', 'fa-box-archive', 'Archive'],
            ]],
            ['Account', [
                ['admin.reports', 'fa-chart-column', 'Reports'],
                ['profile', 'fa-user-circle', 'My Profile'],
            ]],
        ],
        'alumni' => [
            ['Overview', [
                ['alumni.dashboard', 'fa-chart-line', 'Dashboard'],
                ['alumni.feed', 'fa-calendar-days', 'Community Feed'],
            ]],
            ['Career', [
                ['alumni.jobs', 'fa-briefcase', 'Browse Jobs'],
                ['alumni.job_offers', 'fa-gift', 'Job Offers'],
                ['alumni.my_applications', 'fa-file-signature', 'My Applications'],
                ['alumni.employment_history', 'fa-clock-rotate-left', 'Employment History'],
            ]],
            ['Account', [
                ['alumni.add_degree', 'fa-graduation-cap', 'Education'],
                ['profile', 'fa-user-circle', 'My Profile'],
            ]],
        ],
        'alumni_officer' => [
            ['Overview', [
                ['alumni_officer.dashboard', 'fa-chart-line', 'Dashboard'],
                ['alumni_officer.alumni_list', 'fa-users', 'Alumni List'],
            ]],
            ['Events', [
                ['alumni_officer.events_list', 'fa-calendar-days', 'Events Feed'],
                ['alumni_officer.events_create', 'fa-calendar-plus', 'Create Event'],
                ['alumni_officer.archive', 'fa-box-archive', 'Archive'],
            ]],
            ['Account', [
                ['profile', 'fa-user-circle', 'My Profile'],
            ]],
        ],
        'employer' => [
            ['Overview', [
                ['employer.dashboard', 'fa-chart-line', 'Dashboard'],
                ['employer.alumni_list', 'fa-user-graduate', 'Alumni Directory'],
            ]],
            ['Recruitment', [
                ['employer.post_job', 'fa-plus-circle', 'Create Job'],
                ['employer.posted_job', 'fa-briefcase', 'Posted Jobs'],
                ['employer.applications', 'fa-file-signature', 'Applications'],
                ['employer.job_offers', 'fa-gift', 'Job Offers'],
            ]],
            ['Account', [
                ['profile', 'fa-user-circle', 'My Profile'],
            ]],
        ],
    ];

    $titles = [
        'admin' => 'Admin Panel',
        'alumni' => 'Alumni Panel',
        'alumni_officer' => 'Alumni Officer Panel',
        'employer' => 'Employer Panel',
    ];
@endphp

@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/css/sidebar.css">
@endonce

<aside class="sidebar gradconn-sidebar" id="appSidebar" aria-label="{{ $titles[$role] ?? 'Account' }} navigation">
    <a class="sidebar-identity" href="{{ route($menus[$role][0][1][0][0]) }}">
        <img src="/ccc3d.png" alt="City College of Calapan seal">
        <span class="sidebar-brand">GradConn</span>
        <span class="sidebar-role">{{ $titles[$role] ?? 'Account' }}</span>
    </a>

    <nav class="sidebar-nav">
        @foreach($menus[$role] ?? [] as [$section, $items])
            <div class="sidebar-section">{{ $section }}</div>
            @foreach($items as $item)
                @php([$routeName, $icon, $label, $badge] = array_pad($item, 4, null))
                <a href="{{ route($routeName) }}" @class(['active' => request()->routeIs($routeName)]) @if(request()->routeIs($routeName)) aria-current="page" @endif>
                    <span class="menu-left"><i class="fas {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span></span>
                    @if($badge)<span class="badge-count" aria-label="{{ $badge }} pending">{{ $badge }}</span>@endif
                </a>
            @endforeach
        @endforeach

        <div class="sidebar-section">Session</div>
        <a href="#" class="logout" data-logout-trigger>
            <span class="menu-left"><i class="fas fa-right-from-bracket" aria-hidden="true"></i><span>Log out</span></span>
        </a>
    </nav>
</aside>
<script src="/js/sidebar.js" defer></script>
