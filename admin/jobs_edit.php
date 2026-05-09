<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);

// Handle delete request
if (isset($_GET['delete']) && $_GET['delete'] === '1') {
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM jobs WHERE id=?");
        $deleteStmt->execute([$id]);
        header("Location: " . BASE_URL . "/admin/jobs_list.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        $error = "Failed to delete job: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Job not found.");
}

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title        = trim($_POST["title"] ?? "");
    $company      = trim($_POST["company"] ?? "");
    $location     = trim($_POST["location"] ?? "");
    $job_type     = trim($_POST["job_type"] ?? "Full-time");
    $description  = trim($_POST["description"] ?? "");
    $requirements = trim($_POST["requirements"] ?? "");
    $is_open      = (int)($_POST["is_open"] ?? 1);

    if ($title === "" || $company === "" || $description === "") {
        $error = "Title, company, and description are required.";
    } else {
        $up = $pdo->prepare("
            UPDATE jobs
            SET title=?, company=?, location=?, job_type=?, description=?, requirements=?, is_open=?
            WHERE id=?
        ");
        $up->execute([
            $title,
            $company,
            $location,
            $job_type,
            $description,
            $requirements,
            $is_open,
            $id
        ]);

        $msg = "Job updated successfully.";

        $stmt->execute([$id]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
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
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .back-btn {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
    }

    .back-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        max-width: 980px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 10px;
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
    }

    .form-control-custom,
    .form-select-custom,
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
    .form-select-custom:focus,
    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 130px;
    }

    .helper-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .status-preview {
        display: inline-block;
        margin-top: 10px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-open {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .status-closed {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .actions {
        margin-top: 24px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-orange {
        background: #f97316;
        color: #ffffff;
        border: none;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #ea580c;
        color: #ffffff;
    }

    .btn-delete {
        background: #ffffff;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-delete:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-outline-custom:hover {
        background: #f3f4f6;
        color: #111827;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
        }

        .page-title {
            font-size: 24px;
        }
    }

    @media (max-width: 767.98px) {
        .form-card {
            padding: 20px;
        }
    }
</style>

<div class="content">

    <div class="page-header">
        <h3 class="page-title">Edit Job</h3>
        <a class="back-btn" href="<?php echo BASE_URL; ?>/admin/jobs_list.php">Back to Job List</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        name="title"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($job['title']); ?>"
                        required
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Company</label>
                    <input
                        type="text"
                        name="company"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($job['company']); ?>"
                        required
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                        name="description"
                        class="form-textarea-custom"
                        rows="5"
                        required
                    ><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>"
                        placeholder="Calapan City / Remote / etc."
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Job Type</label>
                    <select name="job_type" class="form-select-custom">
                        <?php
                        $types = ["Full-time", "Part-time", "Internship", "Contract", "Remote"];
                        foreach ($types as $t) {
                            $selected = (($job['job_type'] ?? '') === $t) ? "selected" : "";
                            echo '<option value="' . htmlspecialchars($t) . '" ' . $selected . '>' . htmlspecialchars($t) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Requirements</label>
                    <textarea
                        name="requirements"
                        class="form-textarea-custom"
                        rows="4"
                    ><?php echo htmlspecialchars($job['requirements'] ?? ''); ?></textarea>
                    <div class="helper-text">Add the qualifications, skills, or experience needed for this job.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="is_open" class="form-select-custom">
                        <option value="1" <?php echo ((int)$job['is_open'] === 1 ? 'selected' : ''); ?>>Open</option>
                        <option value="0" <?php echo ((int)$job['is_open'] === 0 ? 'selected' : ''); ?>>Closed</option>
                    </select>

                    <div class="mt-2">
                        <span class="status-preview <?php echo ((int)$job['is_open'] === 1) ? 'status-open' : 'status-closed'; ?>">
                            Current: <?php echo ((int)$job['is_open'] === 1) ? 'Open' : 'Closed'; ?>
                        </span>
                    </div>
                </div>

                <div class="col-12 actions">
                    <button type="submit" class="btn-orange">Save Changes</button>
                    <a class="btn-outline-custom" href="<?php echo BASE_URL; ?>/admin/jobs_list.php">Cancel</a>
                    <a class="btn-delete" onclick="return confirm('Delete this job? This cannot be undone.');" href="<?php echo BASE_URL; ?>/admin/jobs_edit.php?id=<?php echo (int)$id; ?>&delete=1">Delete Job</a>
                </div>

            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>