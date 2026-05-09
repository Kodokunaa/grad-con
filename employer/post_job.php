<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'employer') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";
$error = "";
$mail_notice = "";

$posted_by = (int)($_SESSION["user"]["id"] ?? 0);
$employer_fullname = trim($_SESSION["user"]["fullname"] ?? "");
$employer_email    = trim($_SESSION["user"]["email"] ?? "");

if ($posted_by > 0) {
    try {
        $empStmt = $pdo->prepare("
            SELECT fullname, email
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $empStmt->execute([$posted_by]);
        $empRow = $empStmt->fetch(PDO::FETCH_ASSOC);

        if ($empRow) {
            $employer_fullname = trim($empRow["fullname"] ?? $employer_fullname);
            $employer_email    = trim($empRow["email"] ?? $employer_email);
        }
    } catch (Throwable $e) {
        // keep session values if query fails
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title            = trim($_POST["title"] ?? "");
    $company          = $employer_fullname;
    $employer_company = $employer_fullname;
    $email_address    = $employer_email;
    $location         = trim($_POST["location"] ?? "");
    $job_type         = trim($_POST["job_type"] ?? "");
    $start_date       = trim($_POST["start_date"] ?? "");
    $end_date         = trim($_POST["end_date"] ?? "");
    $description      = trim($_POST["description"] ?? "");
    $is_open          = isset($_POST["is_open"]) ? 1 : 0;

    if (
        $title === "" ||
        $employer_company === "" ||
        $email_address === "" ||
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
            $hasEmailColumn = false;
            $hasStartDateColumn = false;
            $hasEndDateColumn = false;

            try {
                $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'email_address'");
                $hasEmailColumn = $checkCol && $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;

                $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'start_date'");
                $hasStartDateColumn = $checkCol && $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;

                $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'end_date'");
                $hasEndDateColumn = $checkCol && $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;
            } catch (Throwable $e) {
                $hasEmailColumn = false;
                $hasStartDateColumn = false;
                $hasEndDateColumn = false;
            }

            if ($hasEmailColumn && $hasStartDateColumn && $hasEndDateColumn) {
                $stmt = $pdo->prepare("
                    INSERT INTO jobs(title, company, employer_company, email_address, location, job_type, start_date, end_date, description, is_open, posted_by)
                    VALUES(?,?,?,?,?,?,?,?,?,?,?)
                ");

                $stmt->execute([
                    $title,
                    $company,
                    $employer_company,
                    $email_address,
                    $location,
                    $job_type,
                    $start_date,
                    $end_date,
                    $description,
                    $is_open,
                    $posted_by
                ]);
            } elseif ($hasStartDateColumn && $hasEndDateColumn) {
                $stmt = $pdo->prepare("
                    INSERT INTO jobs(title, company, employer_company, location, job_type, start_date, end_date, description, is_open, posted_by)
                    VALUES(?,?,?,?,?,?,?,?,?,?)
                ");

                $stmt->execute([
                    $title,
                    $company,
                    $employer_company,
                    $location,
                    $job_type,
                    $start_date,
                    $end_date,
                    $description,
                    $is_open,
                    $posted_by
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO jobs(title, company, employer_company, location, job_type, description, is_open, posted_by)
                    VALUES(?,?,?,?,?,?,?,?)
                ");

                $stmt->execute([
                    $title,
                    $company,
                    $employer_company,
                    $location,
                    $job_type,
                    $description,
                    $is_open,
                    $posted_by
                ]);
            }

            $job_id = (int)$pdo->lastInsertId();

            $applyUrl = BASE_URL . "/index.php?force_login=1";
            $logoUrl  = rtrim(BASE_URL, '/') . "/ccc3d.png";

            $alumniStmt = $pdo->prepare("
                SELECT fullname, email
                FROM users
                WHERE role = 'alumni'
                  AND is_active = 1
                  AND email IS NOT NULL
                  AND email <> ''
            ");
            $alumniStmt->execute();
            $recipients = $alumniStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($recipients)) {
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'ccctestcap1@gmail.com';
                    $mail->Password   = 'axek bsko mass xpkk';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('ccctestcap1@gmail.com', 'Job Portal Admin');
                    $mail->addReplyTo('ccctestcap1@gmail.com', 'Job Portal Admin');
                    $mail->addAddress('ccctestcap1@gmail.com', 'Job Portal Admin');

                    $bccCount = 0;
                    foreach ($recipients as $recipient) {
                        $recipientEmail = trim($recipient['email'] ?? '');
                        $recipientName  = trim($recipient['fullname'] ?? 'Alumni');

                        if ($recipientEmail !== '') {
                            $mail->addBCC($recipientEmail, $recipientName);
                            $bccCount++;
                        }
                    }

                    if ($bccCount > 0) {
                        $mail->isHTML(true);
                        $mail->Subject = "New Job Opportunity: " . $title;

                        $safeTitle           = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                        $safeEmployerCompany = htmlspecialchars($employer_company, ENT_QUOTES, 'UTF-8');
                        $safeEmployerEmail   = htmlspecialchars($email_address, ENT_QUOTES, 'UTF-8');
                        $safeLocation        = htmlspecialchars($location ?: 'Not specified', ENT_QUOTES, 'UTF-8');
                        $safeJobType         = htmlspecialchars($job_type ?: 'Not specified', ENT_QUOTES, 'UTF-8');
                        $safeStartDate       = htmlspecialchars(date("F j, Y", strtotime($start_date)), ENT_QUOTES, 'UTF-8');
                        $safeEndDate         = htmlspecialchars(date("F j, Y", strtotime($end_date)), ENT_QUOTES, 'UTF-8');
                        $safeApplyUrl        = htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8');
                        $safeLogoUrl         = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
                        $safeDesc            = nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));

                        $customMessage = "A new job opportunity has been posted. Please click Apply Now to go directly to the login page of the system.";
                        $safeMessage = nl2br(htmlspecialchars($customMessage, ENT_QUOTES, 'UTF-8'));

                        $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Employer Name</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeEmployerCompany . '</div>
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
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Email Address</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeEmployerEmail . '</div>
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

                                <tr>
                                    <td colspan="2">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Location</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">' . $safeLocation . '</div>
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
                        <td style="padding:28px 32px 32px 32px;">
                            <div style="border-top:1px solid #e5e7eb; padding-top:18px; font-size:13px; line-height:1.7; color:#6b7280;">
                                This email was sent by <strong style="color:#374151;">Job Portal Admin</strong>.
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
                            "Employer Name: {$employer_company}\n" .
                            "Email Address: {$email_address}\n" .
                            "Job Type: {$job_type}\n" .
                            "Start Date: " . date("F j, Y", strtotime($start_date)) . "\n" .
                            "End Date: " . date("F j, Y", strtotime($end_date)) . "\n" .
                            "Location: " . ($location ?: 'Not specified') . "\n\n" .
                            "Description:\n{$description}\n\n" .
                            "Log in here to apply: {$applyUrl}";

                        $mail->send();
                        $mail_notice = " Email notification sent to {$bccCount} alumni successfully.";
                    } else {
                        $mail_notice = " Job posted, but no alumni email recipients were found.";
                    }
                } catch (Exception $e) {
                    $mail_notice = " Job posted, but email sending failed: " . $mail->ErrorInfo;
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employer Post Job</title>

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
        overflow-x:hidden;
    }

    .content{
        margin-left:290px;
        width:calc(100% - 290px);
        max-width:100%;
        padding:30px 24px;
    }

    .page-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:22px;
    }

    .page-title{
        font-size:30px;
        font-weight:700;
        color:#111827;
    }

    .page-subtitle{
        font-size:14px;
        color:#6b7280;
        margin-top:6px;
    }

    .header-actions{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .back-btn,
    .posted-btn{
        display:inline-flex;
        align-items:center;
        gap:8px;
        text-decoration:none;
        border:1px solid #d1d5db;
        padding:11px 16px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        transition:0.3s ease;
    }

    .back-btn{
        background:#ffffff;
        color:#374151;
    }

    .back-btn:hover{
        background:#f3f4f6;
        color:#111827;
    }

    .posted-btn{
        background:#f97316;
        color:#ffffff;
        border-color:#f97316;
    }

    .posted-btn:hover{
        background:#ea580c;
        border-color:#ea580c;
        color:#ffffff;
    }

    .form-card{
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:20px;
        padding:30px;
        box-shadow:0 10px 30px rgba(0,0,0,0.05);
        max-width:1000px;
    }

    .alert-box{
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

    .form-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:18px;
    }

    .form-group{
        display:flex;
        flex-direction:column;
    }

    .full-width{
        grid-column:1 / -1;
    }

    .form-label{
        font-size:14px;
        font-weight:600;
        color:#374151;
        margin-bottom:8px;
    }

    .form-control,
    .form-select,
    .form-textarea{
        width:100%;
        padding:13px 14px;
        border:1px solid #d1d5db;
        border-radius:12px;
        font-size:14px;
        background:#f9fafb;
        outline:none;
        transition:0.25s ease;
        color:#111827;
    }

    .form-control:focus,
    .form-select:focus,
    .form-textarea:focus{
        border-color:#f97316;
        background:#ffffff;
        box-shadow:0 0 0 3px rgba(249,115,22,0.15);
    }

    .form-textarea{
        resize:vertical;
        min-height:140px;
    }

    .helper-text{
        font-size:12px;
        color:#6b7280;
        margin-top:6px;
    }

    .checkbox-wrap{
        display:flex;
        align-items:center;
        gap:10px;
        margin-top:8px;
    }

    .checkbox-wrap input[type="checkbox"]{
        width:17px;
        height:17px;
        accent-color:#f97316;
        cursor:pointer;
    }

    .checkbox-wrap label{
        font-size:14px;
        color:#374151;
        cursor:pointer;
    }

    .actions{
        margin-top:24px;
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }

    .btn-primary{
        background:#f97316;
        color:#ffffff;
        border:none;
        padding:12px 20px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        cursor:pointer;
        transition:0.3s ease;
    }

    .btn-primary:hover{
        background:#16a34a;
    }

    .btn-secondary{
        background:#ffffff;
        color:#374151;
        border:1px solid #d1d5db;
        padding:12px 20px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        cursor:pointer;
        text-decoration:none;
        transition:0.3s ease;
        display:inline-flex;
        align-items:center;
        gap:8px;
    }

    .btn-secondary:hover{
        background:#f3f4f6;
        color:#111827;
    }

    @media (max-width: 991.98px){
        .content{
            margin-left:0;
            width:100%;
            padding:20px 15px;
        }

        .page-title{
            font-size:24px;
        }

        .form-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .form-card{
            padding:20px;
        }
    }
</style>
</head>
<body>

<?php
if (file_exists(__DIR__ . "/../includes/employer_sidebar.php")) {
    include __DIR__ . "/../includes/employer_sidebar.php";
} else {
    echo "<div style='padding:15px; background:#fee2e2; color:#991b1b; margin:20px; border-radius:10px;'>
            ERROR: employer_sidebar.php not found. Check your includes folder.
          </div>";
}
?>

<div class="content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Post Job</h2>
            <p class="page-subtitle">Create a new job opportunity for all alumni applicants.</p>
        </div>

    </div>

    <?php if ($msg): ?>
        <div class="alert-box alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Employer Company Name</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?php echo htmlspecialchars($employer_fullname); ?>"
                        readonly
                    >
                    <div class="helper-text">This is auto generated from the logged-in employer account.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input
                        type="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($employer_email); ?>"
                        readonly
                    >
                    <div class="helper-text">This is auto generated from the logged-in employer account.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Job Type</label>
                    <input
                        type="text"
                        class="form-control"
                        name="job_type"
                        placeholder="Full-time / Part-time / Internship"
                        value="<?php echo htmlspecialchars($_POST['job_type'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Job Title</label>
                    <input
                        type="text"
                        class="form-control"
                        name="title"
                        placeholder="Enter job title"
                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Start Post Date</label>
                    <input
                        type="date"
                        class="form-control"
                        name="start_date"
                        value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">End Post Date</label>
                    <input
                        type="date"
                        class="form-control"
                        name="end_date"
                        value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Location</label>
                    <input
                        type="text"
                        class="form-control"
                        name="location"
                        placeholder="Calapan City / Remote / Hybrid"
                        value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Description</label>
                    <textarea
                        class="form-textarea"
                        name="description"
                        placeholder="Enter full job description, responsibilities, and qualifications"
                        required
                    ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group full-width">
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

            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Post Job
                </button>

                <a href="<?php echo BASE_URL; ?>/employer/posted_job.php" class="btn-secondary">
                    <i class="fas fa-briefcase"></i> View Posted Jobs
                </a>
            </div>
        </form>
    </div>

</div>

</body>
</html>