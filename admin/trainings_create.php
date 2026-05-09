<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

// PHPMailer
require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";
$error = "";

$allowed_courses = [
    "BSIS",
    "BSTM",
    "BSHM",
    "BSED Math",
    "BSED Science",
    "BSNED",
    "BPA",
    "Open for All"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title         = trim($_POST["title"] ?? "");
    $content       = trim($_POST["content"] ?? "");
    $training_date = trim($_POST["training_date"] ?? "");
    $location      = trim($_POST["location"] ?? "");
    $target_course = trim($_POST["target_course"] ?? "");
    $image_name    = null;

    if ($title === "" || $content === "" || $training_date === "" || $target_course === "") {
        $error = "Title, description, training date, and target course are required.";
    } elseif (!in_array($target_course, $allowed_courses, true)) {
        $error = "Invalid target course selected.";
    } else {

        if (!empty($_FILES["image"]["name"])) {
            $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                $error = "Invalid image type. Allowed: jpg, jpeg, png, gif, webp.";
            } else {
                $upload_dir = __DIR__ . "/../uploads/trainings/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $image_name = "training_" . time() . "_" . rand(1000, 9999) . "." . $ext;
                $target = $upload_dir . $image_name;

                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
                    $error = "Image upload failed.";
                }
            }
        }

        if ($error === "") {
            $stmt = $pdo->prepare("
                INSERT INTO trainings(title, content, training_date, location, target_course, image, posted_by)
                VALUES(?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $title,
                $content,
                $training_date,
                $location,
                $target_course,
                $image_name,
                $_SESSION['user']['id']
            ]);

            // ==========================
            // SEND EMAIL NOTIFICATION
            // ==========================
            try {
                if ($target_course === "Open for All") {
                    $notifyStmt = $pdo->prepare("
                        SELECT fullname, email, course
                        FROM users
                        WHERE role = 'alumni'
                          AND is_active = 1
                          AND employment_status = 'Unemployed'
                          AND email IS NOT NULL
                          AND email <> ''
                        ORDER BY fullname ASC
                    ");
                    $notifyStmt->execute();
                } else {
                    $notifyStmt = $pdo->prepare("
                        SELECT fullname, email, course
                        FROM users
                        WHERE role = 'alumni'
                          AND is_active = 1
                          AND employment_status = 'Unemployed'
                          AND course = ?
                          AND email IS NOT NULL
                          AND email <> ''
                        ORDER BY fullname ASC
                    ");
                    $notifyStmt->execute([$target_course]);
                }

                $recipients = $notifyStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($recipients as $r) {
                    $mail = new PHPMailer(true);

                    // SMTP CONFIG
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'ccctestcap1@gmail.com';
                    $mail->Password   = 'axek bsko mass xpkk';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('ccctestcap1@gmail.com', 'Training Notification');
                    $mail->addAddress($r['email'], $r['fullname']);

                    $mail->isHTML(true);
                    $mail->Subject = 'New Training Opportunity Available';

                    $safeName = htmlspecialchars($r['fullname']);
                    $safeTitle = htmlspecialchars($title);
                    $safeDate = htmlspecialchars($training_date);
                    $safeLocation = htmlspecialchars($location !== "" ? $location : "To be announced");
                    $safeCourse = htmlspecialchars($target_course);
                    $safeContent = nl2br(htmlspecialchars($content));

                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; font-size: 14px; color: #111827; line-height: 1.6;'>
                            <h2 style='color:#f97316; margin-bottom:10px;'>New Training Opportunity</h2>

                            <p>Hello <strong>{$safeName}</strong>,</p>

                            <p>A new training has been posted for <strong>{$safeCourse}</strong>.</p>

                            <table style='border-collapse:collapse; width:100%; margin-top:10px; margin-bottom:16px;'>
                                <tr>
                                    <td style='padding:8px; border:1px solid #e5e7eb; width:160px;'><strong>Title</strong></td>
                                    <td style='padding:8px; border:1px solid #e5e7eb;'>{$safeTitle}</td>
                                </tr>
                                <tr>
                                    <td style='padding:8px; border:1px solid #e5e7eb;'><strong>Date</strong></td>
                                    <td style='padding:8px; border:1px solid #e5e7eb;'>{$safeDate}</td>
                                </tr>
                                <tr>
                                    <td style='padding:8px; border:1px solid #e5e7eb;'><strong>Location</strong></td>
                                    <td style='padding:8px; border:1px solid #e5e7eb;'>{$safeLocation}</td>
                                </tr>
                            </table>

                            <p><strong>Description:</strong></p>
                            <div style='padding:12px; border:1px solid #e5e7eb; background:#f9fafb; border-radius:8px;'>
                                {$safeContent}
                            </div>

                            <p style='margin-top:16px;'>Please log in to your alumni account for more details.</p>

                            <p>Thank you.</p>
                        </div>
                    ";

                    $mail->AltBody =
                        "Hello {$r['fullname']},\n\n" .
                        "A new training has been posted.\n\n" .
                        "Title: {$title}\n" .
                        "Date: {$training_date}\n" .
                        "Location: " . ($location !== "" ? $location : "To be announced") . "\n" .
                        "Target Course: {$target_course}\n\n" .
                        "Description:\n{$content}\n\n" .
                        "Please log in to your alumni account for more details.";

                    $mail->send();
                }

                $msg = "Training posted successfully and notifications sent!";
            } catch (Exception $e) {
                $msg = "Training posted successfully, but email notification failed: " . $e->getMessage();
            }

            $_POST = [];
        }
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

    .page-subtitle {
        color: #6b7280;
        margin-top: 4px;
        font-size: 15px;
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

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control-custom,
    .form-textarea-custom,
    .form-file-custom,
    .form-select-custom {
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
    .form-textarea-custom:focus,
    .form-file-custom:focus,
    .form-select-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 140px;
    }

    .helper-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 24px;
    }

    .btn-orange {
        background: #f97316;
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        cursor: pointer;
        display: inline-block;
    }

    .btn-orange:hover {
        background: #16a34a;
        color: #ffffff;
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
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
        <div>
            <h3 class="page-title">Post Training</h3>
            <div class="page-subtitle">Create a new training post for alumni and users.</div>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert-box alert-success-custom">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-danger-custom">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label class="form-label">Training Title</label>
                <input
                    type="text"
                    name="title"
                    class="form-control-custom"
                    value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea
                    name="content"
                    rows="5"
                    class="form-textarea-custom"
                    required
                ><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="form-label">Training Date</label>
                    <input
                        type="date"
                        name="training_date"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($_POST['training_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6 form-group">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        name="location"
                        class="form-control-custom"
                        value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                        placeholder="Enter training location"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Target Course</label>
                <select name="target_course" class="form-select-custom" required>
                    <option value="">-- Select Target Course --</option>
                    <?php foreach ($allowed_courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course); ?>"
                            <?php echo (($course === ($_POST['target_course'] ?? '')) ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($course); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="helper-text">
                    Select BSIS if only BSIS unemployed alumni should receive email notification and see this training.
                    Select Open for All if all unemployed alumni should receive the email.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Image (optional)</label>
                <input
                    type="file"
                    name="image"
                    class="form-file-custom"
                    accept="image/*"
                >
                <div class="helper-text">
                    Allowed file types: jpg, jpeg, png, gif, webp
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange">Post Training</button>
                <a class="btn-outline-custom" href="<?php echo BASE_URL; ?>/admin/trainings_list.php">View Trainings</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>