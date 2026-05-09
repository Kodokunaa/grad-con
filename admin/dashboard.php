<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$totalJobs = 0;
$totalEmployers = 0;
$employedCount = 0;
$unemployedCount = 0;
$alignedCount = 0;
$notAlignedCount = 0;
$totalAlumni = 0;
$employmentRate = 0;
$alignmentRate = 0;

try {
    $totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();

    $totalEmployers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer' AND is_active = 1")->fetchColumn();

    $totalAlumni = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND is_active = 1")->fetchColumn();

    $employmentStats = $pdo->query("SELECT employment_status, COUNT(*) AS total FROM users WHERE role = 'alumni' AND is_active = 1 GROUP BY employment_status")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($employmentStats as $row) {
        $status = strtolower(trim((string)($row['employment_status'] ?? '')));
        if ($status === 'employed') {
            $employedCount = (int)$row['total'];
        } elseif ($status === 'unemployed') {
            $unemployedCount = (int)$row['total'];
        }
    }

    $alignmentStats = $pdo->query("SELECT job_aligned, COUNT(*) AS total FROM users WHERE role = 'alumni' AND is_active = 1 AND employment_status = 'Employed' GROUP BY job_aligned")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($alignmentStats as $row) {
        $aligned = strtolower(trim((string)($row['job_aligned'] ?? '')));
        if ($aligned === 'yes') {
            $alignedCount = (int)$row['total'];
        } elseif ($aligned === 'no') {
            $notAlignedCount = (int)$row['total'];
        }
    }

    $employmentRate = $totalAlumni > 0 ? round(($employedCount / $totalAlumni) * 100, 1) : 0;
    $alignmentRate = $employedCount > 0 ? round(($alignedCount / $employedCount) * 100, 1) : 0;
} catch (Throwable $ex) {
    $totalJobs = 0;
    $totalEmployers = 0;
    $employedCount = 0;
    $unemployedCount = 0;
    $alignedCount = 0;
    $notAlignedCount = 0;
    $totalAlumni = 0;
    $employmentRate = 0;
    $alignmentRate = 0;
}

$employmentLabels = ['Employed', 'Unemployed'];
$employmentTotals = [$employedCount, $unemployedCount];

$alignmentLabels = ['Aligned', 'Not Aligned'];
$alignmentTotals = [$alignedCount, $notAlignedCount];

$adminName = $_SESSION['user']['fullname'] ?? 'System Admin';

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
?>

<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        background: #f3f4f6;
        overflow-x: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        color: #111827;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        min-height: 100vh;
        padding: 28px 24px 42px;
    }

    .dashboard-wrap {
        max-width: 1240px;
        margin: 0 auto;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        margin-bottom: 18px;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 10px;
    }

    .dashboard-title {
        margin: 0;
        font-size: 30px;
        line-height: 1.1;
        font-weight: 950;
        letter-spacing: -0.03em;
        color: #111827;
    }

    .dashboard-subtitle {
        margin-top: 6px;
        color: #6b7280;
        font-size: 14px;
        font-weight: 600;
    }

    .top-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-primary,
    .btn-soft {
        border: none;
        text-decoration: none;
        border-radius: 12px;
        padding: 11px 15px;
        font-size: 14px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: .2s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-primary {
        background: #f97316;
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(249, 115, 22, .20);
    }

    .btn-primary:hover {
        background: #ea580c;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-soft {
        background: #ffffff;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .btn-soft:hover {
        border-color: #fdba74;
        color: #ea580c;
        background: #fff7ed;
    }

    .hero-card {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 18px;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.20), transparent 26%),
            linear-gradient(135deg, #111827 0%, #334155 52%, #f97316 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
        color: #ffffff;
    }

    .hero-card::after {
        content: "";
        position: absolute;
        right: -70px;
        bottom: -80px;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
    }

    .hero-content {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.25fr .75fr;
        gap: 18px;
        align-items: center;
    }

    .hero-title {
        margin: 0 0 8px;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 950;
        letter-spacing: -0.03em;
    }

    .hero-text {
        margin: 0;
        max-width: 760px;
        color: rgba(255,255,255,.82);
        font-size: 14px;
        line-height: 1.7;
        font-weight: 600;
    }

    .hero-mini-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .hero-mini {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 16px;
        padding: 14px;
        backdrop-filter: blur(6px);
    }

    .hero-mini-label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(255,255,255,.72);
        margin-bottom: 6px;
    }

    .hero-mini-value {
        font-size: 24px;
        font-weight: 950;
        color: #ffffff;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
        transition: .18s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: #fdba74;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
    }

    .stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #fff7ed;
        color: #f97316;
        flex-shrink: 0;
    }

    .stat-label {
        color: #6b7280;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 950;
        color: #111827;
        line-height: 1;
        margin-bottom: 7px;
    }

    .stat-note {
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.45;
        font-weight: 600;
    }

    .main-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 360px;
        gap: 14px;
    }

    .panel-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
    }

    .panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: #111827;
        letter-spacing: -0.02em;
    }

    .panel-text {
        margin-top: 5px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
        font-weight: 600;
    }

    .chart-container {
        width: 100%;
        height: 280px;
        position: relative;
    }

    .summary-list {
        display: grid;
        gap: 10px;
        margin-bottom: 14px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .summary-label {
        color: #374151;
        font-size: 13px;
        font-weight: 800;
    }

    .summary-value {
        color: #f97316;
        font-size: 18px;
        font-weight: 950;
    }

    .quick-links {
        display: grid;
        gap: 10px;
    }

    .quick-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 900;
        transition: .18s ease;
    }

    .quick-link:hover {
        background: #f97316;
        border-color: #f97316;
        color: #ffffff;
        transform: translateX(3px);
    }

    .quick-arrow {
        font-size: 16px;
        font-weight: 950;
    }

    .empty-chart {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #94a3b8;
        font-weight: 800;
        font-size: 13px;
        background: #f9fafb;
        border-radius: 16px;
        border: 1px dashed #d1d5db;
    }

    .chart-container.is-empty canvas {
        display: none;
    }

    .chart-container.is-empty .empty-chart {
        display: flex;
    }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .main-grid { grid-template-columns: 1fr; }
        .hero-content { grid-template-columns: 1fr; }
    }

    @media (max-width: 992px) {
        .content { margin-left: 0; width: 100%; padding: 22px 14px 34px; }
        .topbar { align-items: flex-start; flex-direction: column; }
        .top-actions { width: 100%; justify-content: flex-start; }
        .dashboard-title { font-size: 25px; }
        .hero-title { font-size: 24px; }
    }

    @media (max-width: 576px) {
        .stats-grid { grid-template-columns: 1fr; }
        .hero-card, .stat-card, .panel-card { padding: 18px; border-radius: 18px; }
        .hero-mini-grid { grid-template-columns: 1fr; }
        .top-actions, .btn-primary, .btn-soft { width: 100%; }
        .chart-container { height: 250px; }
    }
</style>

<div class="content">
    <div class="dashboard-wrap">
        <div class="topbar">
            <div>
                <div class="eyebrow">📊 Admin Analytics</div>
                <h1 class="dashboard-title">Admin Dashboard</h1>
                <div class="dashboard-subtitle">Welcome, <?php echo e($adminName); ?></div>
            </div>

            <div class="top-actions">
                <a href="<?php echo BASE_URL; ?>/admin/alumni_list.php" class="btn-soft">Manage Alumni</a>
                <button type="button" class="btn-primary" onclick="printEmploymentReport()">🖨 Print Report</button>
            </div>
        </div>

        <section class="hero-card">
            <div class="hero-content">
                <div>
                    <h2 class="hero-title">Employment Analytics Overview</h2>
                    <p class="hero-text">
                        Monitor alumni employment status, job alignment, employer participation, and job posting records in one clean dashboard.
                    </p>
                </div>

                <div class="hero-mini-grid">
                    <div class="hero-mini">
                        <div class="hero-mini-label">Employment Rate</div>
                        <div class="hero-mini-value"><?php echo e($employmentRate); ?>%</div>
                    </div>
                    <div class="hero-mini">
                        <div class="hero-mini-label">Alignment Rate</div>
                        <div class="hero-mini-value"><?php echo e($alignmentRate); ?>%</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Total Jobs</div>
                    </div>
                    <div class="stat-icon">💼</div>
                </div>
                <div class="stat-number"><?php echo number_format($totalJobs); ?></div>
                <div class="stat-note">All job opportunities posted in the system.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Employers</div>
                    </div>
                    <div class="stat-icon">🏢</div>
                </div>
                <div class="stat-number"><?php echo number_format($totalEmployers); ?></div>
                <div class="stat-note">Active employer accounts registered in the system.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Employed Alumni</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-number"><?php echo number_format($employedCount); ?></div>
                <div class="stat-note">Graduates currently marked as employed.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Unemployed Alumni</div>
                    </div>
                    <div class="stat-icon">📌</div>
                </div>
                <div class="stat-number"><?php echo number_format($unemployedCount); ?></div>
                <div class="stat-note">Graduates currently marked as unemployed.</div>
            </div>
        </section>

        <section class="main-grid">
            <div class="panel-card">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Alumni Employment Status</h3>
                        <div class="panel-text">Comparison of employed and unemployed alumni.</div>
                    </div>
                </div>
                <div class="chart-container" id="employmentChartWrap">
                    <canvas id="employmentChart"></canvas>
                    <div class="empty-chart">No employment data available.</div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Job Alignment to Degree</h3>
                        <div class="panel-text">Shows whether employed alumni jobs are aligned with their degree.</div>
                    </div>
                </div>
                <div class="chart-container" id="alignmentChartWrap">
                    <canvas id="alignmentChart"></canvas>
                    <div class="empty-chart">No job alignment data available.</div>
                </div>
            </div>

            <aside class="panel-card">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Summary & Quick Access</h3>
                        <div class="panel-text">Important figures and useful admin shortcuts.</div>
                    </div>
                </div>

                <div class="summary-list">
                    <div class="summary-item"><span class="summary-label">Total Alumni</span><span class="summary-value"><?php echo number_format($totalAlumni); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Employers</span><span class="summary-value"><?php echo number_format($totalEmployers); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Total Jobs</span><span class="summary-value"><?php echo number_format($totalJobs); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Aligned Jobs</span><span class="summary-value"><?php echo number_format($alignedCount); ?></span></div>
                    <div class="summary-item"><span class="summary-label">Not Aligned</span><span class="summary-value"><?php echo number_format($notAlignedCount); ?></span></div>
                </div>

                <div class="quick-links">
                    <a class="quick-link" href="<?php echo BASE_URL; ?>/admin/alumni_list.php"><span>Manage Alumni</span><span class="quick-arrow">→</span></a>
                    <a class="quick-link" href="<?php echo BASE_URL; ?>/admin/employer_list.php"><span>Manage Employers</span><span class="quick-arrow">→</span></a>
                    <a class="quick-link" href="<?php echo BASE_URL; ?>/admin/jobs_list.php"><span>View Jobs</span><span class="quick-arrow">→</span></a>
                    <a class="quick-link" href="<?php echo BASE_URL; ?>/admin/graduates_stats.php"><span>Employment Statistics</span><span class="quick-arrow">→</span></a>
                </div>
            </aside>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const employmentLabels = <?php echo json_encode($employmentLabels); ?>;
const employmentTotals = <?php echo json_encode($employmentTotals); ?>;
const alignmentLabels = <?php echo json_encode($alignmentLabels); ?>;
const alignmentTotals = <?php echo json_encode($alignmentTotals); ?>;

const totalJobs = <?php echo (int)$totalJobs; ?>;
const totalEmployers = <?php echo (int)$totalEmployers; ?>;
const totalAlumni = <?php echo (int)$totalAlumni; ?>;
const employedCount = <?php echo (int)$employedCount; ?>;
const unemployedCount = <?php echo (int)$unemployedCount; ?>;
const alignedCount = <?php echo (int)$alignedCount; ?>;
const notAlignedCount = <?php echo (int)$notAlignedCount; ?>;
const employmentRate = <?php echo json_encode($employmentRate); ?>;
const alignmentRate = <?php echo json_encode($alignmentRate); ?>;

function hasChartData(values) {
    return Array.isArray(values) && values.reduce((sum, val) => sum + Number(val || 0), 0) > 0;
}

let employmentChart = null;
let alignmentChart = null;

if (!hasChartData(employmentTotals)) {
    document.getElementById('employmentChartWrap')?.classList.add('is-empty');
} else {
    employmentChart = new Chart(document.getElementById('employmentChart'), {
        type: 'doughnut',
        data: {
            labels: employmentLabels,
            datasets: [{
                data: employmentTotals,
                backgroundColor: ['#22c55e', '#ef4444'],
                borderWidth: 4,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 16, font: { weight: 'bold' } }
                }
            }
        }
    });
}

if (!hasChartData(alignmentTotals)) {
    document.getElementById('alignmentChartWrap')?.classList.add('is-empty');
} else {
    alignmentChart = new Chart(document.getElementById('alignmentChart'), {
        type: 'doughnut',
        data: {
            labels: alignmentLabels,
            datasets: [{
                data: alignmentTotals,
                backgroundColor: ['#3b82f6', '#f59e0b'],
                borderWidth: 4,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 16, font: { weight: 'bold' } }
                }
            }
        }
    });
}

function printEmploymentReport() {
    const employmentImage = employmentChart ? employmentChart.toBase64Image() : '';
    const alignmentImage = alignmentChart ? alignmentChart.toBase64Image() : '';
    const printWindow = window.open('', '_blank', 'width=1000,height=800');

    if (!printWindow) {
        alert('Please allow pop-ups to print the report.');
        return;
    }

    printWindow.document.write(`
        <html>
        <head>
            <title>Employment Analytics Report</title>
            <style>
                *{box-sizing:border-box;}
                body{font-family:Arial,sans-serif;padding:30px;color:#111827;}
                h1{margin:0 0 6px;font-size:26px;}
                .subtitle{color:#6b7280;margin-bottom:22px;font-size:14px;line-height:1.6;}
                .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px;}
                .card{border:1px solid #d1d5db;border-radius:12px;padding:16px;}
                .label{color:#6b7280;font-size:12px;font-weight:bold;text-transform:uppercase;margin-bottom:8px;}
                .value{font-size:26px;font-weight:bold;color:#f97316;}
                .chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
                .chart-card{border:1px solid #d1d5db;border-radius:12px;padding:18px;min-height:250px;}
                .chart-card h3{margin-top:0;font-size:18px;}
                img{max-width:100%;height:auto;}
                .empty{color:#6b7280;font-weight:bold;padding:70px 10px;text-align:center;border:1px dashed #d1d5db;border-radius:10px;background:#f9fafb;}
                .footer{margin-top:24px;font-size:12px;color:#6b7280;}
                @media print{body{padding:10px;} .summary-grid{grid-template-columns:repeat(3,1fr);} }
            </style>
        </head>
        <body>
            <h1>Alumni Employment Analytics Report</h1>
            <div class="subtitle">Generated report showing alumni employment, job alignment, employer count, and job posting records.</div>
            <div class="summary-grid">
                <div class="card"><div class="label">Total Alumni</div><div class="value">${totalAlumni}</div></div>
                <div class="card"><div class="label">Total Jobs</div><div class="value">${totalJobs}</div></div>
                <div class="card"><div class="label">Total Employers</div><div class="value">${totalEmployers}</div></div>
                <div class="card"><div class="label">Employed Alumni</div><div class="value">${employedCount}</div></div>
                <div class="card"><div class="label">Unemployed Alumni</div><div class="value">${unemployedCount}</div></div>
                <div class="card"><div class="label">Employment Rate</div><div class="value">${employmentRate}%</div></div>
                <div class="card"><div class="label">Aligned Jobs</div><div class="value">${alignedCount}</div></div>
                <div class="card"><div class="label">Not Aligned</div><div class="value">${notAlignedCount}</div></div>
                <div class="card"><div class="label">Alignment Rate</div><div class="value">${alignmentRate}%</div></div>
            </div>
            <div class="chart-grid">
                <div class="chart-card"><h3>Employment Status Chart</h3>${employmentImage ? `<img src="${employmentImage}" alt="Employment Chart">` : `<div class="empty">No employment data available.</div>`}</div>
                <div class="chart-card"><h3>Job Alignment Chart</h3>${alignmentImage ? `<img src="${alignmentImage}" alt="Alignment Chart">` : `<div class="empty">No job alignment data available.</div>`}</div>
            </div>
            <div class="footer">Printed from Admin Dashboard.</div>
            <script>
                window.onload = function(){ window.print(); window.onafterprint = function(){ window.close(); }; };
            <\/script>
        </body>
        </html>
    `);

    printWindow.document.close();
}
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
