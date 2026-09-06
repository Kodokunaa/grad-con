@extends('layouts.authenticated')
@section('title', 'Employer Overview · GradConn')
@section('heading', 'Employer Overview')
@push('styles')
<style>
.employer-overview{max-width:1180px}.overview-hero{display:flex;justify-content:space-between;gap:22px;padding:27px;border-radius:22px;background:linear-gradient(135deg,#172033 35%,#c2410c);color:#fff}.overview-hero h1{margin:0 0 7px;font-size:29px}.overview-hero p{margin:0;color:#e2e8f0}.hero-actions{display:flex;align-items:center;gap:9px}.hero-actions a{padding:11px 15px;border-radius:10px;background:#fff;color:#c2410c;text-decoration:none;font-weight:800}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:18px 0}.stat{padding:18px;border:1px solid #e2e8f0;border-radius:15px;background:#fff}.stat strong{display:block;font-size:28px}.stat span{color:#64748b;font-size:13px}.content-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(250px,1fr);gap:18px}.panel{padding:20px;border:1px solid #e2e8f0;border-radius:16px;background:#fff}.panel h2{margin:0 0 5px;font-size:20px}.panel>p{margin:0 0 16px;color:#64748b}.application-row{display:flex;justify-content:space-between;gap:14px;padding:13px 0;border-top:1px solid #e2e8f0}.application-row a{color:#ea580c;font-weight:800;text-decoration:none}.quick-links{display:grid;gap:9px}.quick-links a{padding:12px;border-radius:10px;background:#f8fafc;color:#334155;text-decoration:none;font-weight:750}.empty{padding:28px 0;color:#64748b}.company-copy{line-height:1.6;color:#475569}@media(max-width:900px){.stats{grid-template-columns:repeat(2,1fr)}.content-grid{grid-template-columns:1fr}.overview-hero{flex-direction:column;align-items:flex-start}}@media(max-width:540px){.stats{grid-template-columns:1fr}.hero-actions{flex-wrap:wrap}}
</style>
@endpush
@section('content')
<div class="employer-overview">
    <section class="overview-hero"><div><h1>Welcome, {{ $fullname }}</h1><p>Publish specific opportunities and manage applications from interested alumni.</p></div><div class="hero-actions"><a href="{{ route('employer.post_job') }}">Post a Job</a><a href="{{ route('profile') }}">About Company</a></div></section>
    <section class="stats">
        <div class="stat"><strong>{{ number_format($jobsCount) }}</strong><span>Total job posts</span></div>
        <div class="stat"><strong>{{ number_format($openJobsCount) }}</strong><span>Open opportunities</span></div>
        <div class="stat"><strong>{{ number_format($appsCount) }}</strong><span>Applications received</span></div>
        <div class="stat"><strong>{{ number_format($hiredCount) }}</strong><span>Applicants hired</span></div>
    </section>
    <section class="content-grid">
        <div class="panel"><h2>Latest Applications</h2><p>Applications submitted to your published jobs.</p>
            @forelse($latest as $application)
                <div class="application-row"><div><strong>{{ $application['fullname'] ?: 'Applicant' }}</strong><br><small>{{ $application['title'] }} · {{ ucfirst(str_replace('_',' ',$application['status'])) }}</small></div><a href="{{ route('employer.applications', ['job_id' => $application['job_id']]) }}">Review</a></div>
            @empty<div class="empty">No applications have been submitted yet.</div>@endforelse
        </div>
        <aside class="panel"><h2>Recruitment</h2><p>Common employer actions.</p><div class="quick-links"><a href="{{ route('employer.posted_job') }}">Manage Posted Jobs</a><a href="{{ route('employer.applications') }}">Review Applications</a><a href="{{ route('profile') }}">Edit About Company</a></div></aside>
    </section>
</div>
@endsection
