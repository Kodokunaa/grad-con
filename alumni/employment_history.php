<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

require_login();

$id   = (int)($_SESSION['user']['id'] ?? 0);
$role = $_SESSION['user']['role'] ?? '';

if ($role !== 'alumni') {
    die("Access denied.");
}

// Load user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Helper: Add security log
function add_log(PDO $pdo, int $user_id, string $action, string $details = null): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $ins = $pdo->prepare("
        INSERT INTO security_logs(user_id, action, details, ip_address, user_agent)
        VALUES(?,?,?,?,?)
    ");
    $ins->execute([$user_id, $action, $details, $ip, $ua]);
}

$msg = "";
$error = "";

// ========================
// ADD EMPLOYMENT HISTORY
// ========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_employment"])) {
    $company_name    = trim($_POST["company_name"] ?? "");
    $job_title       = trim($_POST["job_title"] ?? "");
    $employment_type = trim($_POST["employment_type"] ?? "");
    $location        = trim($_POST["location"] ?? "");
    $start_date      = trim($_POST["start_date"] ?? "");
    $end_date        = trim($_POST["end_date"] ?? "");
    $job_description = trim($_POST["job_description"] ?? "");

    if ($company_name === "" || $job_title === "" || $start_date === "") {
        $error = "Company name, job title, and start date are required.";
    } elseif (strtotime($start_date) === false) {
        $error = "Invalid start date.";
    } elseif ($end_date !== "" && strtotime($end_date) === false) {
        $error = "Invalid end date.";
    } elseif ($end_date !== "" && strtotime($end_date) < strtotime($start_date)) {
        $error = "End date cannot be earlier than start date.";
    } else {
        try {
            $ins = $pdo->prepare("
                INSERT INTO employment_history
                (user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $id,
                $company_name,
                $job_title,
                ($employment_type !== "" ? $employment_type : null),
                ($location !== "" ? $location : null),
                $start_date,
                ($end_date !== "" ? $end_date : null),
                ($job_description !== "" ? $job_description : null)
            ]);

            // Reflect current employment status
            $checkEmployment = $pdo->prepare("
                SELECT COUNT(*) 
                FROM employment_history
                WHERE user_id = ? AND end_date IS NULL
            ");
            $checkEmployment->execute([$id]);
            $isEmployed = ((int)$checkEmployment->fetchColumn() > 0) ? "Employed" : "Unemployed";

            $updEmployment = $pdo->prepare("UPDATE users SET employment_status=? WHERE id=?");
            $updEmployment->execute([$isEmployed, $id]);

            add_log($pdo, $id, "EMPLOYMENT_HISTORY_ADDED", "Employment history added");
            $msg = "Employment history added successfully!";

            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $error = "Unable to save employment history. Please run the SQL first.";
        }
    }
}

// ========================
// DELETE EMPLOYMENT HISTORY
// ========================
if (isset($_GET["delete"])) {
    $delete_id = (int)($_GET["delete"] ?? 0);

    if ($delete_id > 0) {
        try {
            $del = $pdo->prepare("DELETE FROM employment_history WHERE id=? AND user_id=?");
            $del->execute([$delete_id, $id]);

            // Reflect employment status again
            $checkEmployment = $pdo->prepare("
                SELECT COUNT(*) 
                FROM employment_history
                WHERE user_id = ? AND end_date IS NULL
            ");
            $checkEmployment->execute([$id]);
            $isEmployed = ((int)$checkEmployment->fetchColumn() > 0) ? "Employed" : "Unemployed";

            $updEmployment = $pdo->prepare("UPDATE users SET employment_status=? WHERE id=?");
            $updEmployment->execute([$isEmployed, $id]);

            add_log($pdo, $id, "EMPLOYMENT_HISTORY_DELETED", "Employment history deleted");
            header("Location: employment_history.php?deleted=1");
            exit;
        } catch (Throwable $e) {
            $error = "Unable to delete employment history.";
        }
    }
}

if (isset($_GET["deleted"])) {
    $msg = "Employment history deleted successfully!";
}

// ========================
// LOAD EMPLOYMENT HISTORY
// ========================
$employment_list = [];
try {
    $employmentStmt = $pdo->prepare("
        SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description, created_at
        FROM employment_history
        WHERE user_id=?
        ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC
    ");
    $employmentStmt->execute([$id]);
    $employment_list = $employmentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $employment_list = [];
    $error = "Employment history table not found. Please run the SQL first.";
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/alumni_sidebar.php";
?>

<style>
    body {
        background: #f8fafc;
        overflow-x: hidden;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 30px 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 18px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-success-custom {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-custom {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 14px;
        background: #f9fafb;
        outline: none;
        transition: 0.25s ease;
    }

    .form-control-custom:focus,
    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        min-height: 110px;
        resize: vertical;
    }

    .btn-orange {
        background: #f97316;
        color: #fff;
        border: none;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: 0.25s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #ea580c;
        color: #fff;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table thead tr {
        background: #f8fafc;
    }

    .custom-table th,
    .custom-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
    }

    .custom-table th {
        color: #374151;
        font-weight: 700;
    }

    .custom-table td {
        color: #111827;
    }

    .muted-small {
        color: #6b7280;
        font-size: 12px;
    }

    .text-danger-link {
        color: #dc2626;
        text-decoration: none;
        font-weight: 600;
    }

    .text-danger-link:hover {
        text-decoration: underline;
    }

    .top-badge {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fdba74;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <div>
            <h3 class="page-title">Employment History</h3>
            <div class="page-subtitle">Add your past and current jobs. Leave end date blank if you are still employed.</div>
        </div>
        <div class="top-badge">Alumni Employment Manager</div>
    </div>

    <div class="card-custom">
        <div class="section-title">Add New Employment History</div>

        <?php if ($msg): ?>
            <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Company Name</label>
                    <input
                        type="text"
                        name="company_name"
                        class="form-control-custom"
                        placeholder="Enter company name"
                        value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        name="job_title"
                        class="form-control-custom"
                        placeholder="Enter job title"
                        value="<?php echo htmlspecialchars($_POST['job_title'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Employment Type</label>
                    <input
                        type="text"
                        name="employment_type"
                        class="form-control-custom"
                        placeholder="Full-time, Part-time, Contract"
                        value="<?php echo htmlspecialchars($_POST['employment_type'] ?? ''); ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control-custom"
                        placeholder="Enter work location"
                        value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input
                        type="date"
                        name="start_date"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input
                        type="date"
                        name="end_date"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Job Description</label>
                    <textarea
                        name="job_description"
                        class="form-textarea-custom"
                        placeholder="Optional job description"
                    ><?php echo htmlspecialchars($_POST['job_description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" name="add_employment" class="btn-orange">Add Employment History</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="section-title">My Employment History</div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Duration</th>
                        <th>Description</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($employment_list) === 0): ?>
                        <tr>
                            <td colspan="8" class="muted-small">No employment history added yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employment_list as $emp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emp['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($emp['job_title']); ?></td>
                                <td><?php echo htmlspecialchars($emp['employment_type'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($emp['location'] ?? ''); ?></td>
                                <td>
                                    <?php
                                        $start = $emp['start_date'] ?? '';
                                        $end = $emp['end_date'] ?? '';
                                        if ($start && $end) {
                                            echo htmlspecialchars($start . ' to ' . $end);
                                        } elseif ($start && !$end) {
                                            echo htmlspecialchars($start . ' to Present');
                                        } else {
                                            echo '<span class="muted-small">N/A</span>';
                                        }
                                    ?>
                                </td>
                                <td class="muted-small"><?php echo htmlspecialchars($emp['job_description'] ?? ''); ?></td>
                                <td class="muted-small"><?php echo htmlspecialchars($emp['created_at']); ?></td>
                                <td>
                                    <a
                                        href="employment_history.php?delete=<?php echo (int)$emp['id']; ?>"
                                        class="text-danger-link"
                                        onclick="return confirm('Delete this employment history?');"
                                    >
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>