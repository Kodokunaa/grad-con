<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

require_employer();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function initials($name): string {
    $name = trim((string)$name);
    if ($name === '') return 'U';
    $parts = preg_split('/\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';
    return e($first . $last);
}


$eid = (int)($_SESSION['user']['id'] ?? 0);
$fullname = $_SESSION['user']['fullname'] ?? 'Employer';

$jobsCount = 0;
$openJobsCount = 0;
$closedJobsCount = 0;
$appsCount = 0;
$pendingCount = 0;
$interviewCount = 0;
$acceptedCount = 0;
$hiredCount = 0;
$rejectedCount = 0;
$latest = [];

try {
    $statsStmt = $pdo->prepare("
        SELECT
            (SELECT COUNT(*) FROM jobs WHERE employer_id = ?) AS jobs_count,

            (SELECT COUNT(*) FROM jobs 
             WHERE employer_id = ? 
             AND is_open = 1
             AND (start_date IS NULL OR start_date <= CURDATE())
             AND (end_date IS NULL OR end_date >= CURDATE())) AS open_jobs_count,

            (SELECT COUNT(*) FROM jobs 
             WHERE employer_id = ? 
             AND (
                is_open = 0
                OR (end_date IS NOT NULL AND end_date < CURDATE())
             )) AS closed_jobs_count,

            (SELECT COUNT(*)
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ?) AS apps_count,

            (SELECT COUNT(*)
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? AND a.status = 'pending') AS pending_count,

            (SELECT COUNT(*)
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? AND a.status IN ('interview','for interview')) AS interview_count,

            (SELECT COUNT(*)
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? AND a.status = 'accepted') AS accepted_count,

            (SELECT COUNT(*)
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? AND a.status = 'hired') AS hired_count,

            (SELECT COUNT(*)
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? AND a.status = 'rejected') AS rejected_count
    ");

    $statsStmt->execute([
        $eid, $eid, $eid, $eid, $eid,
        $eid, $eid, $eid, $eid
    ]);

    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $jobsCount = (int)($stats['jobs_count'] ?? 0);
    $openJobsCount = (int)($stats['open_jobs_count'] ?? 0);
    $closedJobsCount = (int)($stats['closed_jobs_count'] ?? 0);
    $appsCount = (int)($stats['apps_count'] ?? 0);
    $pendingCount = (int)($stats['pending_count'] ?? 0);
    $interviewCount = (int)($stats['interview_count'] ?? 0);
    $acceptedCount = (int)($stats['accepted_count'] ?? 0);
    $hiredCount = (int)($stats['hired_count'] ?? 0);
    $rejectedCount = (int)($stats['rejected_count'] ?? 0);

    $latestStmt = $pdo->prepare("
        SELECT 
            a.id,
            a.status,
            a.created_at,
            u.fullname,
            u.email,
            j.title,
            j.id AS job_id
        FROM applications a
        JOIN users u ON u.id = a.alumni_id
        JOIN jobs j ON j.id = a.job_id
        WHERE j.employer_id = ?
        ORDER BY a.id DESC
        LIMIT 8
    ");
    $latestStmt->execute([$eid]);
    $latest = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $jobsCount = 0;
    $openJobsCount = 0;
    $closedJobsCount = 0;
    $appsCount = 0;
    $pendingCount = 0;
    $interviewCount = 0;
    $acceptedCount = 0;
    $hiredCount = 0;
    $rejectedCount = 0;
    $latest = [];
}

function statusBadge($status) {
    $status = strtolower(trim((string)$status));

    if ($status === 'accepted') {
        return '<span class="status-badge status-accepted">Accepted</span>';
    }

    if ($status === 'hired') {
        return '<span class="status-badge status-hired">Hired</span>';
    }

    if ($status === 'rejected') {
        return '<span class="status-badge status-rejected">Rejected</span>';
    }

    if ($status === 'interview' || $status === 'for interview') {
        return '<span class="status-badge status-interview">For Interview</span>';
    }

    return '<span class="status-badge status-pending">Pending</span>';
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/employer_sidebar.php";
?>

<style>
*{box-sizing:border-box}
body{margin:0;background:#f5f7fb;overflow-x:hidden;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;color:#111827}.content{margin-left:290px;width:calc(100% - 290px);min-height:100vh;padding:28px}.dashboard-shell{max-width:1180px;margin:0 auto}.topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px}.eyebrow{font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#f97316;margin-bottom:6px}.page-title{font-size:30px;line-height:1.15;font-weight:950;margin:0;color:#0f172a;letter-spacing:-.03em}.page-subtitle{margin-top:7px;color:#64748b;font-size:14px}.top-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.btn-main,.btn-soft{display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;border-radius:12px;padding:11px 15px;font-size:13px;font-weight:900;transition:.2s ease;white-space:nowrap}.btn-main{background:#f97316;color:#fff;box-shadow:0 10px 22px rgba(249,115,22,.20)}.btn-main:hover{background:#ea580c;color:#fff;transform:translateY(-1px)}.btn-soft{background:#fff;color:#9a3412;border:1px solid #fed7aa}.btn-soft:hover{background:#fff7ed;color:#ea580c}.hero-card{position:relative;overflow:hidden;border-radius:22px;background:linear-gradient(135deg,#111827 0%,#334155 55%,#f97316 100%);color:#fff;padding:28px;margin-bottom:18px;box-shadow:0 18px 40px rgba(15,23,42,.16)}.hero-card:after{content:"";position:absolute;right:-70px;top:-70px;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.13)}.hero-content{position:relative;z-index:1;display:flex;justify-content:space-between;align-items:flex-end;gap:20px}.hero-title{font-size:28px;font-weight:950;margin:0 0 8px;letter-spacing:-.02em}.hero-text{max-width:740px;margin:0;color:rgba(255,255,255,.82);font-size:14px;line-height:1.7}.hero-chip{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:10px 13px;font-size:13px;font-weight:900;white-space:nowrap}.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.stats-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:18px;box-shadow:0 8px 22px rgba(15,23,42,.055);transition:.18s ease}.stats-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(15,23,42,.085);border-color:#fed7aa}.stats-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}.stats-icon{width:42px;height:42px;border-radius:13px;background:#fff7ed;color:#f97316;display:flex;align-items:center;justify-content:center;font-size:20px}.stats-label{color:#64748b;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}.stats-number{font-size:32px;font-weight:950;color:#111827;line-height:1}.stats-note{color:#94a3b8;font-size:12px;line-height:1.45;margin-top:8px}.dashboard-grid{display:grid;grid-template-columns:1.45fr .85fr;gap:18px}.panel-card{background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:20px;box-shadow:0 8px 22px rgba(15,23,42,.055)}.panel-header{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:16px}.panel-title{font-size:19px;font-weight:950;color:#111827;margin:0;letter-spacing:-.01em}.panel-text{color:#64748b;font-size:13px;margin-top:4px;line-height:1.5}.table-wrap{width:100%;overflow:auto;border:1px solid #eef2f7;border-radius:16px}.clean-table{width:100%;border-collapse:separate;border-spacing:0;min-width:680px}.clean-table thead th{background:#f8fafc;color:#475569;font-size:12px;font-weight:950;text-align:left;padding:13px 14px;border-bottom:1px solid #e5e7eb;white-space:nowrap}.clean-table tbody td{padding:14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;font-size:13px;color:#111827}.clean-table tbody tr:last-child td{border-bottom:0}.clean-table tbody tr:hover{background:#fffaf5}.applicant-cell{display:flex;align-items:center;gap:10px}.mini-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#f97316,#16a34a);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:950;flex:0 0 auto}.alumni-name{font-weight:900;color:#111827}.alumni-email{color:#64748b;font-size:12px;margin-top:2px}.job-title{font-weight:800;color:#334155}.date-text{color:#64748b;font-size:12px;white-space:nowrap}.manage-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;background:#111827;color:#fff;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:900;transition:.2s ease}.manage-btn:hover{background:#f97316;color:#fff}.status-badge{display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:950;white-space:nowrap}.status-pending{background:#fff7ed;color:#ea580c;border:1px solid #fdba74}.status-interview{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe}.status-accepted{background:#dcfce7;color:#16a34a;border:1px solid #86efac}.status-hired{background:#ecfdf5;color:#047857;border:1px solid #6ee7b7}.status-rejected{background:#fee2e2;color:#dc2626;border:1px solid #fecaca}.empty-state{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:16px;padding:28px;text-align:center;color:#64748b}.empty-title{color:#111827;font-size:17px;font-weight:950;margin-bottom:6px}.summary-list{display:grid;gap:10px;margin-top:16px}.summary-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:13px 14px;display:flex;justify-content:space-between;align-items:center;gap:10px}.summary-label{color:#334155;font-size:13px;font-weight:850}.summary-value{color:#f97316;font-size:18px;font-weight:950}.quick-links{display:grid;gap:10px;margin-top:16px}.quick-link{display:flex;justify-content:space-between;align-items:center;text-decoration:none;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;padding:13px 14px;border-radius:14px;font-size:13px;font-weight:950;transition:.2s ease}.quick-link:hover{background:#f97316;color:#fff;transform:translateX(3px)}
@media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.dashboard-grid{grid-template-columns:1fr}}
@media(max-width:992px){.content{margin-left:0;width:100%;padding:20px 14px}.topbar,.hero-content,.panel-header{align-items:flex-start;flex-direction:column}.page-title{font-size:25px}.hero-title{font-size:24px}.top-actions{width:100%}.btn-main,.btn-soft{flex:1}}
@media(max-width:576px){.stats-grid{grid-template-columns:1fr}.hero-card,.panel-card{padding:18px}.btn-main,.btn-soft{width:100%}.top-actions{flex-direction:column}.stats-number{font-size:30px}}
</style>

<div class="content">
    <div class="dashboard-shell">
        <div class="topbar">
            <div>
                <div class="eyebrow">Employer Panel</div>
                <h2 class="page-title">Employer Dashboard</h2>
                <div class="page-subtitle">Welcome, <?php echo e($fullname); ?>. Manage your job posts and applications in one place.</div>
            </div>

            <div class="top-actions">
                <a href="<?php echo BASE_URL; ?>/employer/post_job.php" class="btn-main">+ Post Job</a>
                <a href="<?php echo BASE_URL; ?>/employer/posted_job.php" class="btn-soft">View My Jobs</a>
            </div>
        </div>

        <section class="hero-card">
            <div class="hero-content">
                <div>
                    <h3 class="hero-title">Recruitment Overview</h3>
                    <p class="hero-text">Track posted jobs, monitor applicant progress, and review the latest alumni applications with a cleaner and easier dashboard layout.</p>
                </div>
                <div class="hero-chip"><?php echo number_format($appsCount); ?> Total Applications</div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Job Posts</div><div class="stats-number"><?php echo number_format($jobsCount); ?></div></div><div class="stats-icon">💼</div></div><div class="stats-note">All jobs created by your account.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Open Jobs</div><div class="stats-number"><?php echo number_format($openJobsCount); ?></div></div><div class="stats-icon">🟢</div></div><div class="stats-note">Currently visible and active jobs.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Applications</div><div class="stats-number"><?php echo number_format($appsCount); ?></div></div><div class="stats-icon">📥</div></div><div class="stats-note">Total applications received.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Pending</div><div class="stats-number"><?php echo number_format($pendingCount); ?></div></div><div class="stats-icon">⏳</div></div><div class="stats-note">Waiting for your review.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">For Interview</div><div class="stats-number"><?php echo number_format($interviewCount); ?></div></div><div class="stats-icon">📅</div></div><div class="stats-note">Applicants selected for interview.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Accepted</div><div class="stats-number"><?php echo number_format($acceptedCount); ?></div></div><div class="stats-icon">✅</div></div><div class="stats-note">Accepted for the next step.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Hired</div><div class="stats-number"><?php echo number_format($hiredCount); ?></div></div><div class="stats-icon">🎉</div></div><div class="stats-note">Officially hired applicants.</div></div>
            <div class="stats-card"><div class="stats-top"><div><div class="stats-label">Rejected</div><div class="stats-number"><?php echo number_format($rejectedCount); ?></div></div><div class="stats-icon">❌</div></div><div class="stats-note">Applications not selected.</div></div>
        </section>

        <section class="dashboard-grid">
            <div class="panel-card">
                <div class="panel-header">
                    <div>
                        <h4 class="panel-title">Latest Applications</h4>
                        <div class="panel-text">Newest alumni applications submitted to your job posts.</div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/employer/my_jobs.php" class="btn-soft">Manage Jobs</a>
                </div>

                <?php if (count($latest) === 0): ?>
                    <div class="empty-state">
                        <div class="empty-title">No applications yet</div>
                        Applicants will appear here once alumni apply to your posted jobs.
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="clean-table">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Job Position</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($latest as $a): ?>
                                    <tr>
                                        <td>
                                            <div class="applicant-cell">
                                                <div class="mini-avatar"><?php echo initials($a['fullname'] ?? 'A'); ?></div>
                                                <div>
                                                    <div class="alumni-name"><?php echo e($a['fullname']); ?></div>
                                                    <div class="alumni-email"><?php echo e($a['email'] ?? ''); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><div class="job-title"><?php echo e($a['title']); ?></div></td>
                                        <td><?php echo statusBadge($a['status'] ?? 'pending'); ?></td>
                                        <td><span class="date-text"><?php echo e(date('M d, Y', strtotime($a['created_at']))); ?></span></td>
                                        <td><a class="manage-btn" href="<?php echo BASE_URL; ?>/employer/applications.php?job_id=<?php echo (int)$a['job_id']; ?>">Manage</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="panel-card">
                <h4 class="panel-title">Recruitment Summary</h4>
                <div class="panel-text">Quick view of your application workflow.</div>

                <div class="summary-list">
                    <div class="summary-item"><span class="summary-label">Open Jobs</span><span class="summary-value"><?php echo number_format($openJobsCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Closed Jobs</span><span class="summary-value"><?php echo number_format($closedJobsCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Pending</span><span class="summary-value"><?php echo number_format($pendingCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">For Interview</span><span class="summary-value"><?php echo number_format($interviewCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Accepted</span><span class="summary-value"><?php echo number_format($acceptedCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Hired</span><span class="summary-value"><?php echo number_format($hiredCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Rejected</span><span class="summary-value"><?php echo number_format($rejectedCount); ?></span></div>
                </div>

                <div class="quick-links">
                    <a class="quick-link" href="<?php echo BASE_URL; ?>/employer/post_job.php"><span>Post New Job</span><span>→</span></a>
                    <a class="quick-link" href="<?php echo BASE_URL; ?>/employer/posted_job.php"><span>View My Jobs</span><span>→</span></a>
                </div>
            </aside>
        </section>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
