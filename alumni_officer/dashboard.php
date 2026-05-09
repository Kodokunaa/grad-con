<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_alumni_officer();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function format_date($date): string {
    if (!$date) return 'N/A';
    $time = strtotime($date);
    if (!$time) return e($date);
    return date('M d, Y', $time);
}

function event_status_label($startDate, $endDate): array {
    $now = time();
    $start = $startDate ? strtotime($startDate) : null;
    $end = $endDate ? strtotime($endDate) : null;

    if ($start && $start > $now) return ['Scheduled', 'status-scheduled'];
    if ($end && $end < $now) return ['Ended', 'status-ended'];
    return ['Active', 'status-active'];
}

$officer_id = (int)($_SESSION['user']['id'] ?? 0);
$fullname = $_SESSION['user']['fullname'] ?? $_SESSION['user']['username'] ?? 'Alumni Officer';

$totalEvents = 0;
$activeEvents = 0;
$scheduledEvents = 0;
$recentEvents = [];
$error = '';

try {
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

    $activeEvents = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE (post_start_date IS NULL OR post_start_date <= NOW()) AND (post_end_date IS NULL OR post_end_date >= NOW())")->fetchColumn();

    $scheduledEvents = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE post_start_date IS NOT NULL AND post_start_date > NOW()")->fetchColumn();

    $recentStmt = $pdo->prepare("SELECT e.*, u.fullname AS poster_name
        FROM events e
        LEFT JOIN users u ON u.id = e.posted_by
        ORDER BY e.created_at DESC, e.id DESC
        LIMIT 6");
    $recentStmt->execute();
    $recentEvents = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) {
    $error = 'Unable to load dashboard data: ' . $ex->getMessage();
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/alumni_officer_sidebar.php";
?>

<style>
    * { box-sizing: border-box; }

    body {
        background: #f5f7fb;
        overflow-x: hidden;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        color: #111827;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        min-height: 100vh;
        padding: 28px 24px 44px;
    }

    .dashboard-wrapper {
        max-width: 1180px;
        margin: 0 auto;
    }

    .hero-card {
        background:
            radial-gradient(circle at 12% 20%, rgba(255,255,255,.36), transparent 28%),
            linear-gradient(135deg, #111827 0%, #334155 48%, #f97316 100%);
        border-radius: 24px;
        padding: 30px;
        color: #fff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .hero-card::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -70px;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(255,255,255,.13);
    }

    .hero-row {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.24);
        color: rgba(255,255,255,.94);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .page-title {
        font-size: 34px;
        font-weight: 950;
        line-height: 1.1;
        letter-spacing: -.04em;
        margin: 0;
    }

    .page-subtitle {
        color: rgba(255,255,255,.78);
        font-size: 14px;
        font-weight: 600;
        margin: 8px 0 0;
    }

    .hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-create,
    .btn-secondary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        border-radius: 13px;
        padding: 12px 17px;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
        border: none;
        transition: .2s ease;
        white-space: nowrap;
    }

    .btn-create {
        background: #f97316;
        color: #fff;
        box-shadow: 0 10px 24px rgba(249, 115, 22, .30);
    }

    .btn-create:hover {
        background: #ea580c;
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: rgba(255,255,255,.13);
        color: #fff;
        border: 1px solid rgba(255,255,255,.27);
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,.22);
        color: #fff;
    }

    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-left: 5px solid #ef4444;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 18px;
        font-weight: 800;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .06);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        border-color: #fed7aa;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff7ed;
        color: #ea580c;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-label {
        color: #6b7280;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 4px;
    }

    .stat-value {
        color: #111827;
        font-size: 32px;
        font-weight: 950;
        line-height: 1;
    }

    .section-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .section-header {
        padding: 18px 20px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .section-title {
        font-size: 19px;
        font-weight: 950;
        color: #111827;
        margin: 0;
        letter-spacing: -.02em;
    }

    .section-subtitle {
        color: #6b7280;
        font-size: 13px;
        font-weight: 650;
        margin-top: 3px;
    }

    .view-all-link {
        color: #f97316;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .view-all-link:hover {
        color: #ea580c;
        text-decoration: underline;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .events-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .events-table th,
    .events-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #eef2f7;
        text-align: left;
        vertical-align: middle;
    }

    .events-table th {
        background: #f9fafb;
        color: #475569;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .events-table tr:last-child td { border-bottom: none; }
    .events-table tbody tr:hover { background: #fffaf5; }

    .event-title-cell {
        font-weight: 950;
        color: #111827;
        line-height: 1.35;
        max-width: 360px;
    }

    .event-meta {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        margin-top: 4px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-active { background: #ecfdf5; color: #047857; }
    .status-scheduled { background: #eff6ff; color: #1d4ed8; }
    .status-ended { background: #fef2f2; color: #b91c1c; }

    .action-group {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 8px 12px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        transition: .2s ease;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .btn-edit {
        background: #fff7ed;
        color: #ea580c;
        border-color: #fed7aa;
    }

    .btn-edit:hover {
        background: #f97316;
        color: #fff;
        border-color: #f97316;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-delete:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    .empty-state {
        text-align: center;
        padding: 52px 24px;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 950;
        color: #111827;
        margin-bottom: 5px;
    }

    .empty-state-text {
        font-size: 14px;
        font-weight: 650;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 22px 14px 36px;
        }

        .hero-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .btn-create,
        .btn-secondary {
            flex: 1 1 180px;
        }

        .page-title { font-size: 28px; }
        .stats-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 575.98px) {
        .hero-card { padding: 22px; border-radius: 18px; }
        .page-title { font-size: 24px; }
        .section-header { flex-direction: column; align-items: flex-start; }
        .stat-card { padding: 16px; }
    }
</style>

<div class="content">
    <div class="dashboard-wrapper">
        <section class="hero-card">
            <div class="hero-row">
                <div>
                    <div class="eyebrow">👋 Alumni Officer Panel</div>
                    <h1 class="page-title">Welcome, <?php echo e($fullname); ?></h1>
                    <p class="page-subtitle">Manage alumni events, monitor recent posts, and keep graduates updated.</p>
                </div>

                <div class="hero-actions">
                    <a href="<?php echo BASE_URL; ?>/alumni_officer/events_create.php" class="btn-create">+ Create Event</a>
                    <a href="<?php echo BASE_URL; ?>/alumni_officer/events_list.php" class="btn-secondary">View Events</a>
                </div>
            </div>
        </section>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div>
                    <div class="stat-label">Total Events</div>
                    <div class="stat-value"><?php echo number_format($totalEvents); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div>
                    <div class="stat-label">Active Events</div>
                    <div class="stat-value"><?php echo number_format($activeEvents); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🕒</div>
                <div>
                    <div class="stat-label">Scheduled Events</div>
                    <div class="stat-value"><?php echo number_format($scheduledEvents); ?></div>
                </div>
            </div>
        </section>

        <section class="section-card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Recent Events</h2>
                    <div class="section-subtitle">Latest event posts created for alumni engagement.</div>
                </div>
                <a href="<?php echo BASE_URL; ?>/alumni_officer/events_list.php" class="view-all-link">View all events →</a>
            </div>

            <?php if (count($recentEvents) === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-title">No events posted yet</div>
                    <div class="empty-state-text">Create your first event to start sharing updates with alumni.</div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Posted By</th>
                                <th>Posted Date</th>
                                <th>Status</th>
                                <th style="width: 170px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvents as $event): ?>
                                <?php
                                    [$statusText, $statusClass] = event_status_label($event['post_start_date'] ?? null, $event['post_end_date'] ?? null);
                                ?>
                                <tr>
                                    <td>
                                        <div class="event-title-cell"><?php echo e($event['title'] ?? 'Untitled Event'); ?></div>
                                        <div class="event-meta">Event ID #<?php echo (int)$event['id']; ?></div>
                                    </td>
                                    <td><?php echo e($event['poster_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo e(format_date($event['created_at'] ?? null)); ?></td>
                                    <td><span class="status-pill <?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span></td>
                                    <td>
                                        <div class="action-group">
                                            <a href="<?php echo BASE_URL; ?>/alumni_officer/events_edit.php?id=<?php echo (int)$event['id']; ?>" class="btn-action btn-edit">Edit</a>
                                            <a href="<?php echo BASE_URL; ?>/alumni_officer/events_list.php?delete=<?php echo (int)$event['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this event?');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
