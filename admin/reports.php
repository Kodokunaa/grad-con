<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

function e($value): string
{
	return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$selectedMonth = trim((string)($_GET['month'] ?? date('Y-m')));
if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth) || !strtotime($selectedMonth . '-01')) {
	$selectedMonth = date('Y-m');
}

$report = [
	'vacancies' => 0,
	'employer_jobs' => 0,
	'admin_jobs' => 0,
	'enrolled_alumni' => 0,
	'applicants' => 0,
	'using_alumni' => 0,
	'monthly_active_users' => 0,
	'monthly_employers' => 0,
	'hired_alumni' => 0,
];
$error = '';

try {
	$report['vacancies'] = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
	$posterBreakdown = $pdo->query("SELECT u.role AS poster_role, COUNT(*) AS total FROM jobs j LEFT JOIN users u ON u.id = j.posted_by GROUP BY u.role")->fetchAll(PDO::FETCH_ASSOC);
	foreach ($posterBreakdown as $row) {
		$role = strtolower(trim((string)($row['poster_role'] ?? '')));
		if ($role === 'employer') {
			$report['employer_jobs'] = (int)$row['total'];
		} elseif ($role === 'admin') {
			$report['admin_jobs'] = (int)$row['total'];
		}
	}
	$report['enrolled_alumni'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND COALESCE(is_active, 0) = 1")->fetchColumn();
	$report['applicants'] = (int)$pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
	$report['using_alumni'] = (int)$pdo->query("SELECT COUNT(DISTINCT alumni_id) FROM applications")->fetchColumn();
	$report['hired_alumni'] = (int)$pdo->query("SELECT COUNT(DISTINCT alumni_id) FROM applications WHERE LOWER(TRIM(status)) = 'hired'")->fetchColumn();

	$userColumns = [];
	$colStmt = $pdo->query("SHOW COLUMNS FROM users");
	$userColumns = $colStmt->fetchAll(PDO::FETCH_COLUMN);
	$monthStart = $selectedMonth . '-01';
	$monthEnd = date('Y-m-d', strtotime('+1 month', strtotime($monthStart)));

	if (in_array('last_login', $userColumns, true)) {
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE last_login >= ? AND last_login < ?");
		$stmt->execute([$monthStart, $monthEnd]);
		$report['monthly_active_users'] = (int)$stmt->fetchColumn();
	} elseif (in_array('created_at', $userColumns, true)) {
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at < ?");
		$stmt->execute([$monthStart, $monthEnd]);
		$report['monthly_active_users'] = (int)$stmt->fetchColumn();
	}

	$monthlyEmployers = $pdo->prepare("
		SELECT COUNT(DISTINCT j.posted_by)
		FROM jobs j
		INNER JOIN users u ON u.id = j.posted_by AND u.role = 'employer'
		WHERE j.created_at >= ? AND j.created_at < DATE_ADD(?, INTERVAL 1 MONTH)
	");
	$monthlyEmployers->execute([$monthStart, $monthStart]);
	$report['monthly_employers'] = (int)$monthlyEmployers->fetchColumn();
} catch (Throwable $ex) {
	$error = 'Some report figures could not be loaded. Please check the database connection and table structure.';
}

$monthLabel = date('F Y', strtotime($selectedMonth . '-01'));

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
?>

<style>
	* { box-sizing: border-box; }
	body { margin: 0; background: #f3f4f6; color: #111827; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
	.content { min-height: 100vh; margin-left: 290px; padding: 34px 24px 48px; }
	.reports-wrap { width: min(1120px, 100%); margin: 0 auto; }
	.reports-header { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; margin-bottom: 28px; }
	.eyebrow { margin: 0 0 8px; color: #ea580c; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
	h1 { margin: 0; font-size: clamp(28px, 4vw, 40px); line-height: 1.05; letter-spacing: -.04em; }
	.subtitle { margin: 10px 0 0; color: #6b7280; font-size: 15px; }
	.month-form { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 8px 22px rgba(15, 23, 42, .06); }
	.month-form label { color: #4b5563; font-size: 13px; font-weight: 700; }
	.month-form input { padding: 7px 8px; border: 1px solid #d1d5db; border-radius: 7px; color: #111827; font: inherit; }
	.metric-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
	.metric-card { min-height: 176px; padding: 24px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(15, 23, 42, .06); }
	.metric-card.featured { background: #fff7ed; border-color: #fed7aa; }
	.metric-icon { display: inline-grid; width: 38px; height: 38px; place-items: center; margin-bottom: 22px; border-radius: 9px; background: #f3f4f6; color: #374151; font-size: 18px; }
	.featured .metric-icon { background: #ffedd5; color: #c2410c; }
	.metric-label { margin: 0; color: #4b5563; font-size: 14px; font-weight: 700; }
	.metric-value { margin: 5px 0 0; color: #111827; font-size: clamp(28px, 4vw, 38px); font-weight: 850; line-height: 1; }
	.metric-note { margin: 10px 0 0; color: #9ca3af; font-size: 12px; line-height: 1.5; }
	.notice { margin-bottom: 18px; padding: 12px 14px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 14px; }
	@media (max-width: 992px) {
		.content { margin-left: 0; padding: 24px 16px 40px; }
		.reports-header { flex-direction: column; align-items: stretch; }
		.month-form { width: 100%; justify-content: space-between; }
		.metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
	}
	@media (max-width: 640px) {
		.reports-header { align-items: stretch; flex-direction: column; gap: 18px; }
		.month-form { flex-direction: column; align-items: stretch; }
		.month-form label { text-align: center; }
		.metric-grid { grid-template-columns: 1fr; }
		.metric-card { padding: 20px 16px; }
		.metric-note { font-size: 11px; }
	}
</style>

<main class="content">
	<div class="reports-wrap">
		<header class="reports-header">
			<div>
				<p class="eyebrow">Admin reporting</p>
				<h1>System Report</h1>
				<p class="subtitle">A current view of vacancies, participation, employers, and placements.</p>
			</div>
			<form class="month-form" method="get">
				<label for="month">Employer activity</label>
				<input id="month" name="month" type="month" value="<?php echo e($selectedMonth); ?>" onchange="this.form.submit()">
			</form>
		</header>

		<?php if ($error !== ''): ?>
			<div class="notice" role="alert"><?php echo e($error); ?></div>
		<?php endif; ?>

		<section class="metric-grid" aria-label="Report metrics">
			<article class="metric-card"><div class="metric-icon" aria-hidden="true"><i class="fas fa-briefcase"></i></div><p class="metric-label">No. of job vacancies collected</p><p class="metric-value"><?php echo number_format($report['vacancies']); ?></p><p class="metric-note">Posted by employers: <?php echo number_format($report['employer_jobs']); ?> | Posted by admin: <?php echo number_format($report['admin_jobs']); ?></p></article>
			<article class="metric-card"><div class="metric-icon" aria-hidden="true"><i class="fas fa-user-graduate"></i></div><p class="metric-label">No. of alumni applicants enrolled</p><p class="metric-value"><?php echo number_format($report['enrolled_alumni']); ?></p><p class="metric-note">Active alumni accounts</p></article>
			<article class="metric-card"><div class="metric-icon" aria-hidden="true"><i class="fas fa-file-alt"></i></div><p class="metric-label">No. of applicants who applied to a job</p><p class="metric-value"><?php echo number_format($report['applicants']); ?></p><p class="metric-note">Total student applications recorded</p></article>
			<article class="metric-card"><div class="metric-icon" aria-hidden="true"><i class="fas fa-users"></i></div><p class="metric-label">No. of alumni applicants using the system</p><p class="metric-value"><?php echo number_format($report['using_alumni']); ?></p><p class="metric-note">Distinct alumni who submitted an application</p></article>
			<article class="metric-card"><div class="metric-icon" aria-hidden="true"><i class="fas fa-clock"></i></div><p class="metric-label">No. of users who logged in / used the system this month</p><p class="metric-value"><?php echo number_format($report['monthly_active_users']); ?></p><p class="metric-note">Users active in <?php echo e($monthLabel); ?>. If login tracking is not enabled, this will use the available user activity fields.</p></article>
			<article class="metric-card featured"><div class="metric-icon" aria-hidden="true"><i class="fas fa-building"></i></div><p class="metric-label">No. of employers using the system</p><p class="metric-value"><?php echo number_format($report['monthly_employers']); ?></p><p class="metric-note">Distinct employers posting vacancies in <?php echo e($monthLabel); ?></p></article>
			<article class="metric-card"><div class="metric-icon" aria-hidden="true"><i class="fas fa-user-check"></i></div><p class="metric-label">No. of users hired by using the system</p><p class="metric-value"><?php echo number_format($report['hired_alumni']); ?></p><p class="metric-note">Distinct alumni users whose application status is hired</p></article>
		</section>
	</div>
</main>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
