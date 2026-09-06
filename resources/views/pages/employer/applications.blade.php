@extends('layouts.authenticated')
@section('title', 'Applications · GradConn')
@section('heading', 'Applications')
@push('styles')
<style>
.applications-page{max-width:1180px}.page-intro{margin-bottom:20px;padding:22px 24px;border-radius:18px;background:linear-gradient(135deg,#172033,#334155);color:#fff}.page-intro h1{margin:0 0 6px;font-size:26px}.page-intro p{margin:0;color:#e2e8f0}.privacy-note{margin:0 0 18px;padding:12px 15px;border:1px solid #fed7aa;border-radius:12px;background:#fff7ed;color:#9a3412;font-size:13px}.application-list{display:grid;gap:16px}.application-card{padding:20px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 5px 18px rgba(15,23,42,.06)}.application-head{display:flex;justify-content:space-between;gap:18px}.application-head h2{margin:0;font-size:19px}.application-meta{margin-top:5px;color:#64748b;font-size:13px}.status{align-self:flex-start;padding:6px 10px;border-radius:999px;background:#fff7ed;color:#c2410c;font-size:12px;font-weight:800;text-transform:capitalize}.application-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:17px}.field{padding:12px;border-radius:11px;background:#f8fafc}.field.full{grid-column:1/-1}.field strong{display:block;margin-bottom:4px;color:#64748b;font-size:11px;letter-spacing:.05em;text-transform:uppercase}.field span{white-space:pre-line;color:#1e293b}.application-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}.application-actions a,.application-actions button{display:inline-flex;align-items:center;justify-content:center;padding:9px 13px;border:0;border-radius:9px;text-decoration:none;font-weight:800;cursor:pointer}.letter{background:#2563eb;color:#fff}.interview{background:#fff7ed;color:#c2410c}.accept{background:#16a34a;color:#fff}.reject{background:#dc2626;color:#fff}.empty{padding:55px 25px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;text-align:center;color:#64748b}@media(max-width:700px){.application-grid{grid-template-columns:1fr}.application-head{flex-direction:column}}
</style>
@endpush
@section('content')
<div class="applications-page">
    <section class="page-intro"><h1>Job Applications</h1><p>Review applications submitted directly to your published opportunities.</p></section>
    <p class="privacy-note">Only recruitment information supplied with an application is shown. Private alumni profile details are not available here.</p>
    @if(session('status'))<div class="flash success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
    <div class="application-list">
    @forelse($applications as $application)
        @php($status = strtolower(trim((string) ($application['status'] ?? 'pending'))))
        <article class="application-card">
            <div class="application-head">
                <div><h2>{{ $application['fullname'] ?: 'Applicant' }}</h2><div class="application-meta">{{ $application['job_title'] }} · Applied {{ optional(\Carbon\Carbon::parse($application['created_at']))->format('M d, Y') }}</div></div>
                <span class="status">{{ str_replace('_', ' ', $status) }}</span>
            </div>
            <div class="application-grid">
                <div class="field"><strong>Email</strong><span>{{ $application['email'] ?: 'Not provided' }}</span></div>
                <div class="field"><strong>Course and batch</strong><span>{{ $application['course'] ?: 'Not provided' }}{{ $application['batch_year'] ? ' · '.$application['batch_year'] : '' }}</span></div>
                <div class="field full"><strong>Professional competencies</strong><span>{{ $application['competencies'] ?: 'Not provided' }}</span></div>
                <div class="field full"><strong>Career objective</strong><span>{{ $application['career_objective'] ?: 'Not provided' }}</span></div>
                <div class="field full"><strong>Application message</strong><span>{{ $application['message'] ?: 'No message included.' }}</span></div>
            </div>
            <div class="application-actions">
                @if($application['resume_file'])<a class="letter" target="_blank" rel="noopener" href="{{ route('applications.letter', $application['application_id']) }}">View Application Letter</a>@endif
                @if(in_array($status, ['pending','under_review','interview'], true))
                    <a class="interview" href="{{ route('employer.interview', ['application_id' => $application['application_id']]) }}">Schedule Interview</a>
                    <form method="POST" action="{{ route('applications.status.update', $application['application_id']) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="accept"><input type="hidden" name="action_message" value="Congratulations. Your application has been accepted."><button class="accept">Accept Application</button></form>
                    <form method="POST" action="{{ route('applications.status.update', $application['application_id']) }}" onsubmit="return confirm('Reject this application?')">@csrf @method('PATCH')<input type="hidden" name="action" value="reject"><button class="reject">Reject</button></form>
                @endif
            </div>
        </article>
    @empty
        <div class="empty"><h2>No applications yet</h2><p>Applications will appear after alumni apply to one of your posted jobs.</p></div>
    @endforelse
    </div>
</div>
@endsection
