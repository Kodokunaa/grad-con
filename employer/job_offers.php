<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

require_employer();

$id = (int)$_SESSION['user']['id'];
$error = "";
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_action'], $_POST['offer_id'])) {
    $offerId = (int)($_POST['offer_id'] ?? 0);
    $offerAction = trim((string)($_POST['offer_action'] ?? ''));

    if ($offerId > 0 && in_array($offerAction, ['done', 'remove'], true)) {
        try {
            $offerStmt = $pdo->prepare("SELECT * FROM job_offers WHERE id = ? AND employer_id = ? LIMIT 1");
            $offerStmt->execute([$offerId, $id]);
            $offer = $offerStmt->fetch(PDO::FETCH_ASSOC);

            if ($offer) {
                if ($offerAction === 'done') {
                    try {
                        $pdo->exec("ALTER TABLE job_offers MODIFY COLUMN status ENUM('sent', 'accepted', 'declined', 'expired', 'done') DEFAULT 'sent'");
                    } catch (PDOException $e) {
                        // ignore if column already includes done or cannot be altered
                    }
                    $updateOffer = $pdo->prepare("UPDATE job_offers SET status = 'done' WHERE id = ? AND employer_id = ?");
                    $updateOffer->execute([$offerId, $id]);
                    $msg = "Offer marked as done.";
                } elseif ($offerAction === 'remove') {
                    $deleteOffer = $pdo->prepare("DELETE FROM job_offers WHERE id = ? AND employer_id = ?");
                    $deleteOffer->execute([$offerId, $id]);
                    $msg = "Offer removed successfully.";
                }
            } else {
                $error = "Offer not found or you do not have permission.";
            }
        } catch (Throwable $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
// Fetch all job offers sent by this employer
$offersStmt = $pdo->prepare("
    SELECT jo.*, u.fullname as alumni_name, u.email as alumni_email, u.course
    FROM job_offers jo
    JOIN users u ON jo.alumni_id = u.id
    WHERE jo.employer_id = ?
    ORDER BY jo.created_at DESC
");
$offersStmt->execute([$id]);
$offers = $offersStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total' => 0,
    'sent' => 0,
    'accepted' => 0,
    'declined' => 0,
    'done' => 0,
    'expired' => 0
];

foreach ($offers as $offer) {
    $stats['total']++;
    if (isset($stats[$offer['status']])) {
        $stats[$offer['status']]++;
    }
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/employer_sidebar.php";
?>

<style>
body {
    background: #f8fafc;
}
.content {
    margin-left: 290px;
    width: calc(100% - 290px);
    max-width: 100%;
    padding: 30px 24px;
}
.page-title {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 8px;
}
.page-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 24px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    text-align: center;
}
.stat-number {
    font-size: 32px;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 8px;
}
.stat-label {
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}
.offers-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.table-wrapper {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: #f9fafb;
    padding: 14px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb;
}
td {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
    color: #374151;
}
tr:last-child td {
    border-bottom: none;
}
.alumni-name-col {
    font-weight: 600;
    color: #1f2937;
}
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-sent {
    background: #dbeafe;
    color: #1e40af;
}
.status-accepted {
    background: #dcfce7;
    color: #166534;
}
.status-declined {
    background: #fee2e2;
    color: #991b1b;
}
.status-expired {
    background: #f3f4f6;
    color: #6b7280;
}
.status-done {
    background: #d1fae5;
    color: #047857;
}
.btn-action,
.view-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-done {
    background: #10b981;
    color: white;
}
.btn-done:hover {
    background: #059669;
}
.btn-remove {
    background: #ef4444;
    color: white;
}
.btn-remove:hover {
    background: #dc2626;
}
.view-btn {
    background: #f97316;
    color: white;
}
.view-btn:hover {
    background: #ea580c;
}
.actions-cell {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
@media (max-width: 900px) {
    .content {
        margin-left: 0;
        width: 100%;
        padding: 20px 15px;
    }
    .offers-table {
        border-radius: 0;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    table {
        min-width: 800px;
    }
    th, td {
        padding: 12px 10px;
    }
    .actions-cell {
        justify-content: flex-start;
    }
}
@media (max-width: 600px) {
    th:nth-child(3),
    th:nth-child(4),
    th:nth-child(6),
    td:nth-child(3),
    td:nth-child(4),
    td:nth-child(6) {
        display: none;
    }
    th, td {
        white-space: normal;
    }
    .actions-cell {
        width: 100%;
        flex-direction: column;
    }
    .btn-action,
    .view-btn {
        width: 100%;
    }
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}
.empty-state-icon {
    font-size: 48px;
    margin-bottom: 16px;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.page-header-content {
    flex: 1;
    min-width: 0;
}
.view-offers-btn {
    padding: 10px 16px;
    background: #f97316;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    align-self: center;
}
.view-offers-btn:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}
</style>

<div class="content">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-title">Job Offers Management</div>
            <div class="page-subtitle">Track all job offers sent to alumni and their responses</div>
        </div>
        <button type="button" class="view-offers-btn" onclick="scrollToOffers()">
            📋 View All Offers
        </button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo (int)$stats['total']; ?></div>
            <div class="stat-label">Total Offers</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #3b82f6;"><?php echo (int)$stats['sent']; ?></div>
            <div class="stat-label">Pending Response</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #10b981;"><?php echo (int)$stats['accepted']; ?></div>
            <div class="stat-label">Accepted</div>
        </div>
    </div>

    <?php if (empty($offers)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📮</div>
            <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No job offers sent yet</p>
            <p>Start sending job offers to alumni from the Alumni List page.</p>
        </div>
    <?php else: ?>
        <div class="offers-table" id="offersSection">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Alumni Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Offer Subject</th>
                            <th>Status</th>
                            <th>Sent Date</th>
                            <th>Response Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offers as $offer): ?>
                            <tr>
                                <td class="alumni-name-col"><?php echo htmlspecialchars($offer['alumni_name']); ?></td>
                                <td><?php echo htmlspecialchars($offer['alumni_email']); ?></td>
                                <td><?php echo htmlspecialchars($offer['course'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($offer['subject']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($offer['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($offer['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('F d, Y', strtotime($offer['created_at'])); ?></td>
                                <td>
                                    <?php
                                        if ($offer['status'] === 'accepted' && $offer['accepted_at']) {
                                            echo date('F d, Y', strtotime($offer['accepted_at']));
                                        } elseif ($offer['status'] === 'declined' && $offer['declined_at']) {
                                            echo date('F d, Y', strtotime($offer['declined_at']));
                                        } else {
                                            echo 'N/A';
                                        }
                                    ?>
                                </td>
                                <td class="actions-cell">
                                    <?php if ($offer['status'] === 'accepted'): ?>
                                        <a class="view-btn" href="interview.php?offer_id=<?php echo (int)$offer['id']; ?>">Set Interview</a>
                                    <?php endif; ?>

                                    <?php if ($offer['status'] !== 'done'): ?>
                                        <form method="POST" style="display:inline-block; margin:0;">
                                            <input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
                                            <button type="submit" name="offer_action" value="done" class="btn-action btn-done">Done</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" style="display:inline-block; margin:0;">
                                        <input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
                                        <button type="submit" name="offer_action" value="remove" class="btn-action btn-remove" onclick="return confirm('Remove this offer?');">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function scrollToOffers() {
    const offersSection = document.getElementById('offersSection');
    if (offersSection) {
        offersSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
