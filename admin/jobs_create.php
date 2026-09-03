<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$msg = "";
$error = "";
$mail_notice = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title            = trim($_POST["title"] ?? "");
    $employer_company = "City College of Calapan";
    $location         = trim($_POST["location"] ?? "");
    $job_type         = trim($_POST["job_type"] ?? "");
    $start_date       = trim($_POST["start_date"] ?? "");
    $end_date         = trim($_POST["end_date"] ?? "");
    $description      = trim($_POST["description"] ?? "");
    $is_open          = isset($_POST["is_open"]) ? 1 : 0;

    if (
        $title === "" ||
        $job_type === "" ||
        $start_date === "" ||
        $end_date === "" ||
        $description === ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        $error = "Please enter valid start and end dates.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = "End date cannot be earlier than start date.";
    } else {
        try {
            $posted_by = (int)($_SESSION["user"]["id"] ?? 0);

            $ins = $pdo->prepare("
                INSERT INTO jobs(title, employer_company, location, job_type, start_date, end_date, description, is_open, posted_by)
                VALUES(?,?,?,?,?,?,?,?,?)
            ");
            $ins->execute([
                $title,
                $employer_company,
                $location,
                $job_type,
                $start_date,
                $end_date,
                $description,
                $is_open,
                $posted_by
            ]);

            $job_id = (int)$pdo->lastInsertId();

            $applyUrl = BASE_URL . "/index.php?force_login=1";
            $logoUrl  = rtrim(BASE_URL, '/') . "/ccc3d.png";

            $alumniStmt = $pdo->prepare("
                SELECT fullname, email
                FROM users
                WHERE role = 'alumni'
                  AND COALESCE(is_active, 0) = 1
                                    AND COALESCE(receive_update_notifications, 1) = 1
                  AND email IS NOT NULL
                  AND TRIM(email) <> ''
                ORDER BY fullname ASC
            ");
            $alumniStmt->execute();
            $recipients = $alumniStmt->fetchAll(PDO::FETCH_ASSOC);

            $validRecipients = [];
            foreach ($recipients as $recipient) {
                $recipientEmail = trim((string)($recipient['email'] ?? ''));
                if ($recipientEmail !== '' && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    $validRecipients[$recipientEmail] = trim((string)($recipient['fullname'] ?? 'Alumni'));
                }
            }

            if (!empty($validRecipients)) {
                $mail = new PHPMailer(true);

                try {
                    // ==========================
                    // GMAIL SMTP CONFIGURATION
                    // ==========================
                    $smtpEmail = 'cccgradconn@gmail.com';

                    // Paste your CURRENT Google App Password below.
                    $smtpPassword = 'anhf wyyh oqan nyll';

                    // Remove spaces Google may display between password groups.
                    $smtpPassword = preg_replace('/\s+/', '', trim($smtpPassword));

                    if (
                        $smtpPassword === '' ||
                        $smtpPassword === 'PASTE_NEW_APP_PASSWORD_HERE'
                    ) {
                        throw new Exception(
                            'SMTP App Password is not configured in this PHP file.'
                        );
                    }

                    if (strlen($smtpPassword) !== 16) {
                        throw new Exception(
                            'Invalid Google App Password length. After removing spaces, ' .
                            'the password has ' . strlen($smtpPassword) .
                            ' characters; expected 16.'
                        );
                    }

                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->AuthType   = 'LOGIN';
                    $mail->Username   = $smtpEmail;
                    $mail->Password   = $smtpPassword;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    $mail->Timeout    = 60;
                    $mail->SMTPKeepAlive = false;
                    $mail->SMTPDebug  = SMTP::DEBUG_OFF;

                    $mail->setFrom($smtpEmail, 'GradConn');
                    $mail->Sender = $smtpEmail;
                    $mail->addReplyTo($smtpEmail, 'GradConn');

                    // One visible recipient; alumni remain hidden in BCC.
                    $mail->addAddress($smtpEmail, 'GradConn');

                    $bccCount = 0;
                    foreach ($validRecipients as $recipientEmail => $recipientName) {
                        $mail->addBCC($recipientEmail, $recipientName);
                        $bccCount++;
                    }

                    if ($bccCount > 0) {
                        $mail->isHTML(true);
                        $mail->Subject = "New Job Opportunity: " . $title;

                        $safeTitle      = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                        $safeEmployer   = htmlspecialchars($employer_company, ENT_QUOTES, 'UTF-8');
                        $safeLocation   = htmlspecialchars($location ?: 'Not specified', ENT_QUOTES, 'UTF-8');
                        $safeJobType    = htmlspecialchars($job_type ?: 'Not specified', ENT_QUOTES, 'UTF-8');
                        $safeApplyUrl   = htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8');
                        $safeLogoUrl    = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
                        $safeDesc       = nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));
                        $safeStartDate  = htmlspecialchars(date("F j, Y", strtotime($start_date)), ENT_QUOTES, 'UTF-8');
                        $safeEndDate    = htmlspecialchars(date("F j, Y", strtotime($end_date)), ENT_QUOTES, 'UTF-8');

                        $customMessage = "A new job opportunity has been posted. Please click Apply Now to go directly to the login page of the system.";
                        $safeMessage = nl2br(htmlspecialchars($customMessage, ENT_QUOTES, 'UTF-8'));

                        $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Opportunity</title>
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
                                New Job Opportunity
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 32px 10px 32px;">
                            <div style="font-size:15px; line-height:1.8; color:#374151;">
                                Hello Alumni,<br><br>
                                We would like to inform you about a new job opportunity. Please review the details below and click the Apply Now button to go directly to the login page of the system.
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
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Employer</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeEmployer . '</div>
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

                                <tr>
                                    <td width="50%" style="padding-right:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Start Date</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeStartDate . '</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%" style="padding-left:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">End Date</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeEndDate . '</div>
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
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:8px;">Description</div>
                                        <div style="font-size:14px; line-height:1.8; color:#374151;">' . $safeDesc . '</div>
                                    </td>
                                </tr>
                            </table>
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
                                Clicking Apply Now will open the login page of the system.<br>
                                If the button does not work, copy and paste this link into your browser:<br>
                                <span style="color:#ea580c; word-break:break-all;">' . $safeApplyUrl . '</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px 32px 32px;">
                            <div style="border-top:1px solid #e5e7eb; padding-top:18px; font-size:13px; line-height:1.7; color:#6b7280;">
                                This email was sent by <strong style="color:#374151;">GradConn</strong>.<br>
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
                            "A new job opportunity has been posted.\n\n" .
                            "Job Title: {$title}\n" .
                            "Employer: {$employer_company}\n" .
                            "Location: " . ($location ?: 'Not specified') . "\n" .
                            "Job Type: {$job_type}\n" .
                            "Start Date: " . date("F j, Y", strtotime($start_date)) . "\n" .
                            "End Date: " . date("F j, Y", strtotime($end_date)) . "\n\n" .
                            "Description:\n{$description}\n\n" .
                            "Log in here to apply: {$applyUrl}";

                        $mail->send();
                        $mail_notice = " Email notification sent successfully.";
                    } else {
                        $mail_notice = " Job posted, but no alumni email recipients were found.";
                    }
                } catch (Exception $e) {
                    $smtpError = trim((string)$mail->ErrorInfo);
                    $exceptionMessage = trim((string)$e->getMessage());
                    $detail = $smtpError !== '' ? $smtpError : $exceptionMessage;

                    error_log('GradConn PHPMailer error: ' . $detail);

                    if (
                        stripos($detail, 'Daily user sending limit exceeded') !== false ||
                        stripos($detail, '5.4.5') !== false
                    ) {
                        $mail_notice =
                            " Job posted, but Gmail's daily sending limit has been reached. " .
                            "Please try again after the Gmail quota resets.";
                    } elseif (
                        stripos($detail, 'authenticate') !== false ||
                        stripos($detail, '535') !== false ||
                        stripos($detail, 'username and password') !== false
                    ) {
                        $mail_notice =
                            " Job posted, but Gmail rejected the SMTP login. " .
                            "Check that the App Password belongs to " . $smtpEmail .
                            " and that it is still active.";
                    } elseif (
                        stripos($detail, 'connect') !== false ||
                        stripos($detail, 'timed out') !== false ||
                        stripos($detail, 'connection') !== false
                    ) {
                        $mail_notice =
                            " Job posted, but the system could not connect to Gmail SMTP. " .
                            "Check your internet connection and whether port 587 is available.";
                    } else {
                        $mail_notice =
                            " Job posted, but email sending failed: " .
                            ($detail !== '' ? $detail : 'Unknown PHPMailer error.');
                    }
                }
            } else {
                $mail_notice = " Job posted, but no alumni were found.";
            }

            $msg = "Job posted successfully!" . $mail_notice;
            $_POST = [];
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
?>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
        min-height: 100vh;
        overflow-x: hidden;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .back-btn {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .back-btn:hover {
        background: #f3f4f6;
        color: #111827;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e0e7ff;
        border-left: 4px solid #f97316;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        max-width: 980px;
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .alert-box {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
        border-left: 4px solid;
        animation: slideDown 0.3s ease;
    }

    .alert-success-custom {
        background: #dcfce7;
        color: #166534;
        border-left-color: #22c55e;
    }

    .alert-danger-custom {
        background: #fee2e2;
        color: #b91c1c;
        border-left-color: #ef4444;
    }

    .form-label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
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
        transition: all 0.25s ease;
        color: #1f2937;
        font-family: inherit;
    }

    .form-control-custom::placeholder,
    .form-textarea-custom::placeholder {
        color: #9ca3af;
    }

    .form-control-custom:focus,
    .form-select-custom:focus,
    .form-textarea-custom:focus {
        border-color: #f97316;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 140px;
        font-family: inherit;
    }

    .helper-text {
        color: #64748b;
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.4;
    }

    .checkbox-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        padding: 12px 14px;
        background: #f9fafb;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.25s ease;
    }

    .checkbox-wrap:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .checkbox-wrap input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #f97316;
        cursor: pointer;
    }

    .checkbox-wrap label {
        margin: 0;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
        font-weight: 500;
    }

    .actions {
        margin-top: 28px;
    }

    .btn-orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        display: inline-block;
    }

    .btn-orange:hover {
        background: linear-gradient(135deg, #ea580c 0%, #d94706 100%);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
        transform: translateY(-2px);
    }

    .btn-orange:active {
        transform: translateY(0);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

        .form-card {
            padding: 20px;
        }
    }

    @media (max-width: 767.98px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .back-btn {
            width: 100%;
            text-align: center;
        }

        .btn-orange {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="content">

    <div class="page-header">
        <h3 class="page-title">Post Job</h3>
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

                <div class="col-md-6">
                    <label class="form-label">Employer Company Name</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        value="City College of Calapan"
                        readonly
                    >
                    <div class="helper-text">This value is fixed and will be saved automatically.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Job Type</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="job_type"
                        placeholder="Full-time / Part-time / Internship"
                        value="<?php echo htmlspecialchars($_POST['job_type'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="title"
                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Post Date</label>
                    <input
                        type="date"
                        class="form-control-custom"
                        name="start_date"
                        value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Post Date</label>
                    <input
                        type="date"
                        class="form-control-custom"
                        name="end_date"
                        value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        name="location"
                        placeholder="Calapan City / Remote / etc."
                        value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea
                        class="form-textarea-custom"
                        name="description"
                        rows="5"
                        required
                    ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <div class="checkbox-wrap">
                        <input
                            type="checkbox"
                            name="is_open"
                            id="is_open"
                            <?php echo isset($_POST['is_open']) || $_SERVER["REQUEST_METHOD"] !== "POST" ? 'checked' : ''; ?>
                        >
                        <label for="is_open">Open for applications</label>
                    </div>
                </div>

                <div class="col-12 actions">
                    <button type="submit" class="btn-orange">Post Job</button>
                </div>

            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>