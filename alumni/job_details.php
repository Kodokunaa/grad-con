<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'alumni') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$job_id = (int)($_GET["id"] ?? 0);
if ($job_id <= 0) {
    die("Invalid job ID.");
}

$success = "";
$error = "";

$alumni_id = (int)($_SESSION["user"]["id"] ?? 0);

// Get job details
$stmt = $pdo->prepare("
    SELECT *
    FROM jobs
    WHERE id = ? AND is_open = 1
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date IS NULL OR end_date >= CURDATE())
    LIMIT 1
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Job not found or no longer open.");
}

// Check if already applied
$alreadyApplied = false;
try {
    $checkStmt = $pdo->prepare("
        SELECT id
        FROM applications
        WHERE job_id = ? AND alumni_id = ?
        LIMIT 1
    ");
    $checkStmt->execute([$job_id, $alumni_id]);
    $alreadyApplied = (bool)$checkStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Apply action
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$alreadyApplied) {
    try {
        $applyStmt = $pdo->prepare("
            INSERT INTO applications (job_id, alumni_id, status, created_at)
            VALUES (?, ?, 'pending', NOW())
        ");
        $applyStmt->execute([$job_id, $alumni_id]);

        $success = "Application submitted successfully.";
        $alreadyApplied = true;
    } catch (PDOException $e) {
        $error = "Failed to submit application: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Details</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
    }

    body{
        background:#f8fafc;
        color:#1f2937;
    }

    .container{
        max-width:900px;
        margin:40px auto;
        padding:0 20px;
    }

    .topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:20px;
    }

    .page-title{
        font-size:30px;
        font-weight:700;
        color:#111827;
    }

    .back-btn{
        display:inline-flex;
        align-items:center;
        gap:8px;
        background:#ffffff;
        color:#374151;
        text-decoration:none;
        border:1px solid #d1d5db;
        padding:11px 16px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        transition:0.3s ease;
    }

    .back-btn:hover{
        background:#f3f4f6;
    }

    .card{
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:20px;
        padding:30px;
        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .job-title{
        font-size:28px;
        font-weight:800;
        color:#111827;
        margin-bottom:8px;
    }

    .company{
        font-size:16px;
        color:#f97316;
        font-weight:700;
        margin-bottom:20px;
    }

    .grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:16px;
        margin-bottom:22px;
    }

    .info-box{
        background:#f9fafb;
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:16px;
    }

    .label{
        font-size:12px;
        text-transform:uppercase;
        font-weight:700;
        color:#6b7280;
        margin-bottom:6px;
    }

    .value{
        font-size:15px;
        font-weight:600;
        color:#111827;
    }

    .description-box{
        margin-top:10px;
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:18px;
    }

    .description-title{
        font-size:18px;
        font-weight:700;
        margin-bottom:10px;
        color:#111827;
    }

    .description-text{
        font-size:15px;
        line-height:1.8;
        color:#374151;
        white-space:pre-line;
    }

    .alert{
        padding:13px 15px;
        border-radius:12px;
        margin-bottom:18px;
        font-size:14px;
        font-weight:500;
    }

    .alert-success{
        background:#dcfce7;
        color:#166534;
        border:1px solid #bbf7d0;
    }

    .alert-error{
        background:#fee2e2;
        color:#b91c1c;
        border:1px solid #fecaca;
    }

    .actions{
        margin-top:24px;
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }

    .btn-apply{
        background:#f97316;
        color:#fff;
        border:none;
        padding:13px 22px;
        border-radius:12px;
        font-size:14px;
        font-weight:700;
        cursor:pointer;
        transition:0.3s ease;
    }

    .btn-apply:hover{
        background:#16a34a;
    }

    .btn-disabled{
        background:#d1d5db;
        color:#6b7280;
        border:none;
        padding:13px 22px;
        border-radius:12px;
        font-size:14px;
        font-weight:700;
        cursor:not-allowed;
    }

    @media (max-width: 768px){
        .grid{
            grid-template-columns:1fr;
        }

        .page-title{
            font-size:24px;
        }

        .card{
            padding:20px;
        }
    }
</style>
</head>
<body>

<div class="container">

    <div class="topbar">
        <h1 class="page-title">Job Details</h1>
        <a href="<?php echo BASE_URL; ?>/alumni/dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="job-title"><?php echo htmlspecialchars($job['title'] ?? ''); ?></div>
        <div class="company"><?php echo htmlspecialchars($job['company'] ?? ''); ?></div>

        <div class="grid">
            <div class="info-box">
                <div class="label">Employer Company</div>
                <div class="value"><?php echo htmlspecialchars($job['employer_company'] ?? 'N/A'); ?></div>
            </div>

            <div class="info-box">
                <div class="label">Job Type</div>
                <div class="value"><?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?></div>
            </div>

            <div class="info-box">
                <div class="label">Location</div>
                <div class="value"><?php echo htmlspecialchars($job['location'] ?? 'Not specified'); ?></div>
            </div>

            <div class="info-box">
                <div class="label">Target Course</div>
                <div class="value"><?php echo htmlspecialchars($job['target_course'] ?? 'Open for All'); ?></div>
            </div>
        </div>

        <div class="description-box">
            <div class="description-title">Description</div>
            <div class="description-text"><?php echo htmlspecialchars($job['description'] ?? ''); ?></div>
        </div>

        <div class="actions">
            <?php if ($alreadyApplied): ?>
                <button class="btn-disabled" disabled>You already applied</button>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-paper-plane"></i> Apply Now
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>