<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

// ==========================
// PHPMailer
// ==========================
require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$job_id = (int)($_GET["job_id"] ?? 0);
if ($job_id <= 0) {
    die("Invalid job.");
}

// Get job info
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id=? LIMIT 1");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Job not found.");
}

$target_course = trim($job['target_course'] ?? '');

// Change this if your actual alumni job details page is different
$applyUrl = BASE_URL . "/alumni/job_details.php?id=" . $job_id;

// Public logo URL
$logoUrl = rtrim(BASE_URL, '/') . "/ccc3d.png";

// SMTP account
$smtpEmail = 'ccctestcap1@gmail.com';
$smtpPassword = 'axek bsko mass xpkk';

$msg = "";
$error = "";

// Get all alumni with the same course as the job target_course
if ($target_course !== "") {
    $recipientsStmt = $pdo->prepare("
        SELECT DISTINCT id, fullname, email, course
        FROM users
        WHERE role = 'alumni'
          AND is_active = 1
          AND email IS NOT NULL
          AND email <> ''
          AND course = ?
        ORDER BY fullname ASC
    ");
    $recipientsStmt->execute([$target_course]);
} else {
    $recipientsStmt = $pdo->prepare("
        SELECT DISTINCT id, fullname, email, course
        FROM users
        WHERE role = 'alumni'
          AND is_active = 1
          AND email IS NOT NULL
          AND email <> ''
        ORDER BY fullname ASC
    ");
    $recipientsStmt->execute();
}

$recipients = $recipientsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($subject === "" || $message === "") {
        $error = "Subject and message are required.";
    } elseif (count($recipients) === 0) {
        $error = "There are no alumni with valid email addresses matching this target course.";
    } else {
        try {
            $mail = new PHPMailer(true);

           
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpEmail;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 60;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

           
            $mail->setFrom($smtpEmail, 'Job Portal Admin');
            $mail->Sender = $smtpEmail;
            $mail->addReplyTo($smtpEmail, 'Job Portal Admin');

           
            $mail->addAddress($smtpEmail, 'Job Portal Admin');

           
            $bccCount = 0;
            foreach ($recipients as $recipient) {
                $recipientEmail = trim($recipient['email'] ?? '');
                $recipientName  = trim($recipient['fullname'] ?? 'Alumni');

                if ($recipientEmail !== '') {
                    $mail->addBCC($recipientEmail, $recipientName);
                    $bccCount++;
                }
            }

            if ($bccCount === 0) {
                throw new Exception("No valid alumni email addresses were found.");
            }

            // ==========================
            // SAFE VALUES
            // ==========================
            $safeSubject   = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
            $safeMessage   = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            $safeTitle     = htmlspecialchars($job['title'] ?? '', ENT_QUOTES, 'UTF-8');
            $safeCompany   = htmlspecialchars($job['company'] ?? '', ENT_QUOTES, 'UTF-8');
            $safeCourse    = htmlspecialchars($job['target_course'] ?: 'All Courses', ENT_QUOTES, 'UTF-8');
            $safeLocation  = htmlspecialchars($job['location'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
            $safeJobType   = htmlspecialchars($job['job_type'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
            $safeApplyUrl  = htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8');
            $safeLogoUrl   = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');

            // ==========================
            // EMAIL CONTENT
            // ==========================
            $mail->isHTML(true);
            $mail->Subject = $subject;

            $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $safeSubject . '</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; margin:0; padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:700px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);">

                    <tr>
                        <td style="background:linear-gradient(135deg, #f97316 0%, #ea580c 100%); padding:22px 32px;">
                            <div style="margin-bottom:12px;">
                                <img src="' . $safeLogoUrl . '" alt="CCC3D Logo" style="max-width:95px; height:auto; display:block;">
                            </div>
                            <div style="font-size:13px; letter-spacing:1px; text-transform:uppercase; color:#ffedd5; font-weight:700; margin-bottom:8px;">
                                Alumni Job Notification
                            </div>
                            <div style="font-size:28px; line-height:1.3; color:#ffffff; font-weight:800;">
                                ' . $safeSubject . '
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 32px 10px 32px;">
                            <div style="font-size:15px; line-height:1.8; color:#374151;">
                                Hello Alumni,<br><br>
                                We would like to inform you about a job opportunity that matches your course. Please review the message and job details below.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 32px 10px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fff7ed; border:1px solid #fdba74; border-radius:14px;">
                                <tr>
                                    <td style="padding:20px 22px;">
                                        <div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#c2410c; margin-bottom:10px;">
                                            Message
                                        </div>
                                        <div style="font-size:15px; line-height:1.8; color:#374151;">
                                            ' . $safeMessage . '
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px 10px 32px;">
                            <div style="font-size:18px; font-weight:800; color:#111827; margin-bottom:14px;">
                                Job Details
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 8px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate; border-spacing:0 10px;">
                                <tr>
                                    <td width="50%" style="padding-right:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Job Title</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeTitle . '</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%" style="padding-left:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Company</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeCompany . '</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%" style="padding-right:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Location</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeLocation . '</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%" style="padding-left:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Job Type</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeJobType . '</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 32px 8px 32px;">
                            <div style="display:inline-block; background:#fff7ed; color:#c2410c; border:1px solid #fdba74; border-radius:999px; padding:8px 14px; font-size:13px; font-weight:800;">
                                Target Course: ' . $safeCourse . '
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 8px 32px;" align="center">
                            <a href="' . $safeApplyUrl . '"
                               style="display:inline-block; background:#f97316; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:12px; font-size:15px; font-weight:700;">
                               Apply Now
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 32px 8px 32px;" align="center">
                            <div style="font-size:12px; color:#6b7280; line-height:1.7;">
                                If the button does not work, copy and paste this link into your browser:<br>
                                <span style="color:#ea580c; word-break:break-all;">' . $safeApplyUrl . '</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px 32px 32px;">
                            <div style="border-top:1px solid #e5e7eb; padding-top:18px; font-size:13px; line-height:1.7; color:#6b7280;">
                                This email was sent by <strong style="color:#374151;">Job Portal Admin</strong>.<br>
                                Please do not reply directly to this message unless instructed by the administrator.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

            $mail->AltBody =
                "Hello Alumni,\n\n" .
                $subject . "\n\n" .
                $message . "\n\n" .
                "Job Details\n" .
                "Job Title: " . ($job['title'] ?? '') . "\n" .
                "Company: " . ($job['company'] ?? '') . "\n" .
                "Location: " . ($job['location'] ?? 'Not specified') . "\n" .
                "Job Type: " . ($job['job_type'] ?? 'Not specified') . "\n" .
                "Target Course: " . ($job['target_course'] ?: 'All Courses') . "\n\n" .
                "Apply Now: " . $applyUrl . "\n\n" .
                "This email was sent by Job Portal Admin.";

            $mail->send();

            $msg = "Notification email sent successfully to {$bccCount} alumni.";
            $_POST = [];
        } catch (Exception $e) {
            $rawError = $mail->ErrorInfo ?? $e->getMessage();
            $error = "Email sending failed. " . $rawError;
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
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 6px 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        margin-bottom: 18px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
        word-break: break-word;
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
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .form-textarea-custom {
        min-height: 180px;
        resize: vertical;
    }

    .btn-orange {
        background: #f97316;
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 11px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
        cursor: pointer;
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
        padding: 11px 16px;
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

    .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .badge-course {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fff7ed;
        color: #ea580c;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
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
</style>

<div class="content">
    <div class="page-header">
        <h3 class="page-title">Notify Alumni</h3>
        <div class="page-subtitle">
            Job: <?php echo htmlspecialchars($job['title']); ?> •
            Company: <?php echo htmlspecialchars($job['company']); ?>
            <div class="badge-course">
                Target Course: <?php echo htmlspecialchars($job['target_course'] ?: 'All Courses'); ?>
            </div>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert-box alert-success-custom"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-danger-custom"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card-custom">
        <div class="section-title">Send Notification</div>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input
                    type="text"
                    name="subject"
                    class="form-control-custom"
                    value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea
                    name="message"
                    class="form-textarea-custom"
                    required
                ><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn-orange">Send Email</button>
                <a class="btn-outline-custom" href="<?php echo BASE_URL; ?>/admin/jobs_list.php">Back to Job List</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>