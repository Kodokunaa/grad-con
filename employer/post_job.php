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
$employer_profile_address = "";
$employer_branches = [];

/** 
 * Safely check if a table exists.
 */
function table_exists(PDO $pdo, string $tableName): bool {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        return (bool)$stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Get all column names from a table.
 */
function get_table_columns(PDO $pdo, string $tableName): array {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$tableName`");
        $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return array_map(fn($row) => $row['Field'], $cols);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Split branch text saved in one profile field into dropdown choices.
 * Accepted separators: newline, comma, semicolon, or vertical bar.
 */
function parse_branch_locations(?string $branchText): array {
    $branchText = trim((string)$branchText);
    if ($branchText === '') {
        return [];
    }

    $parts = preg_split('/[\r\n,;|]+/', $branchText);
    $branches = [];

    foreach ($parts as $part) {
        $branch = trim($part);
        if ($branch !== '' && !in_array($branch, $branches, true)) {
            $branches[] = $branch;
        }
    }

    return $branches;
}

if ($posted_by > 0) {
    try {
        $userCols = get_table_columns($pdo, "users");

        $selectCols = ["fullname", "email"];

        // Profile address field. This uses users.address if available.
        if (in_array("address", $userCols, true)) {
            $selectCols[] = "address";
        }

        // Optional profile branch fields. Any of these may exist depending on your database.
        $possibleBranchCols = [
            "branch_location",
            "branch_locations",
            "company_branch",
            "company_branches",
            "branches",
            "branch_address",
            "branch_addresses"
        ];

        foreach ($possibleBranchCols as $col) {
            if (in_array($col, $userCols, true)) {
                $selectCols[] = $col;
            }
        }

        $selectSql = implode(", ", array_map(fn($col) => "`$col`", array_unique($selectCols)));

        $empStmt = $pdo->prepare("
            SELECT $selectSql
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $empStmt->execute([$posted_by]);
        $empRow = $empStmt->fetch(PDO::FETCH_ASSOC);

        if ($empRow) {
            $employer_fullname = trim($empRow["fullname"] ?? $employer_fullname);
            $employer_email    = trim($empRow["email"] ?? $employer_email);
            $employer_profile_address = trim($empRow["address"] ?? "");

            foreach ($possibleBranchCols as $col) {
                if (!empty($empRow[$col])) {
                    $employer_branches = array_merge($employer_branches, parse_branch_locations($empRow[$col]));
                }
            }
        }

        // Optional separate table support:
        // This works if you have a table named employer_branches with employer_id or user_id,
        // and a branch location/address column.
        if (table_exists($pdo, "employer_branches")) {
            $branchCols = get_table_columns($pdo, "employer_branches");

            $ownerCol = "";
            foreach (["employer_id", "user_id", "created_by"] as $candidate) {
                if (in_array($candidate, $branchCols, true)) {
                    $ownerCol = $candidate;
                    break;
                }
            }

            $locationCol = "";
            foreach (["branch_location", "location", "address", "branch_address"] as $candidate) {
                if (in_array($candidate, $branchCols, true)) {
                    $locationCol = $candidate;
                    break;
                }
            }

            $nameCol = "";
            foreach (["branch_name", "name", "title"] as $candidate) {
                if (in_array($candidate, $branchCols, true)) {
                    $nameCol = $candidate;
                    break;
                }
            }

            if ($ownerCol !== "" && $locationCol !== "") {
                $branchSql = "SELECT `$locationCol` AS branch_location";
                if ($nameCol !== "") {
                    $branchSql .= ", `$nameCol` AS branch_name";
                }
                $branchSql .= " FROM employer_branches WHERE `$ownerCol` = ?";

                if (in_array("is_active", $branchCols, true)) {
                    $branchSql .= " AND is_active = 1";
                }

                $branchSql .= " ORDER BY id DESC";

                $branchStmt = $pdo->prepare($branchSql);
                $branchStmt->execute([$posted_by]);
                $branchRows = $branchStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($branchRows as $branchRow) {
                    $branchName = trim($branchRow["branch_name"] ?? "");
                    $branchLocation = trim($branchRow["branch_location"] ?? "");

                    if ($branchLocation !== "") {
                        $displayBranch = $branchName !== ""
                            ? $branchName . " - " . $branchLocation
                            : $branchLocation;

                        if (!in_array($displayBranch, $employer_branches, true)) {
                            $employer_branches[] = $displayBranch;
                        }
                    }
                }
            }
        }

        $employer_branches = array_values(array_unique(array_filter($employer_branches)));
    } catch (Throwable $e) {
        // keep session values if query fails
    }
}

$default_location = $employer_profile_address;
$selected_branch_location = trim($_POST["branch_location"] ?? "");
$display_location = $selected_branch_location !== ""
    ? $selected_branch_location
    : (trim($_POST["location"] ?? "") !== "" ? trim($_POST["location"] ?? "") : $default_location);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title            = trim($_POST["title"] ?? "");
    $company          = $employer_fullname;
    $employer_company = $employer_fullname;
    $email_address    = $employer_email;
    $posted_location  = trim($_POST["location"] ?? "");
    $branch_location = trim($_POST["branch_location"] ?? "");
    $profile_location = trim($_POST["profile_location"] ?? $employer_profile_address);

    // Built-in location rule:
    // 1. If employer chooses a branch, use the selected branch location.
    // 2. If no branch is selected, use the address saved in the employer profile.
    // 3. The location text field is only a final display value.
    $location = $branch_location !== "" ? $branch_location : $profile_location;
    if ($location === "" && $posted_location !== "") {
        $location = $posted_location;
    }
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
    } elseif ($location === "") {
        $error = "Please complete the employer profile address first, or select an available branch location.";
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

            // Also check for employer_id and created_at columns so inserted jobs match other parts of the app
            $hasEmployerIdColumn = false;
            $hasCreatedAtColumn = false;
            try {
                $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'employer_id'");
                $hasEmployerIdColumn = $checkCol && $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;

                $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'created_at'");
                $hasCreatedAtColumn = $checkCol && $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;
            } catch (Throwable $e) {
                $hasEmployerIdColumn = false;
                $hasCreatedAtColumn = false;
            }

            // Build columns and parameters dynamically to support different schema versions
            $columns = ['title', 'company', 'employer_company'];
            $params = [$title, $company, $employer_company];

            if ($hasEmailColumn) {
                $columns[] = 'email_address';
                $params[] = $email_address;
            }

            $columns[] = 'location';
            $params[] = $location;

            $columns[] = 'job_type';
            $params[] = $job_type;

            if ($hasStartDateColumn) {
                $columns[] = 'start_date';
                $params[] = $start_date;
            }

            if ($hasEndDateColumn) {
                $columns[] = 'end_date';
                $params[] = $end_date;
            }

            $columns[] = 'description';
            $params[] = $description;

            $columns[] = 'is_open';
            $params[] = $is_open;

            // keep posted_by for backward compatibility with code expecting this column
            $columns[] = 'posted_by';
            $params[] = $posted_by;

            if ($hasEmployerIdColumn) {
                $columns[] = 'employer_id';
                $params[] = $posted_by;
            }

            if ($hasCreatedAtColumn) {
                $columns[] = 'created_at';
                $params[] = date('Y-m-d H:i:s');
            }

            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO jobs(" . implode(', ', $columns) . ") VALUES({$placeholders})";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

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
                    // Do not use your normal Gmail password.
                    $smtpPassword = 'anhf wyyh oqan nyll';

                    // Google may display App Passwords with spaces.
                    // Remove whitespace only.
                    $smtpPassword = preg_replace('/\s+/', '', trim($smtpPassword));

                    if (
                        $smtpPassword === '' ||
                        $smtpPassword === 'PASTE_NEW_APP_PASSWORD_HERE'
                    ) {
                        throw new Exception(
                            'SMTP App Password is not configured in this PHP file.'
                        );
                    }

                    // Google App Passwords are 16 characters after spaces are removed.
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
                    $mail->SMTPDebug  = 0;

                    $mail->setFrom($smtpEmail, 'Job Portal Admin');
                    $mail->Sender = $smtpEmail;
                    $mail->addReplyTo($smtpEmail, 'Job Portal Admin');

                    // Keep one visible recipient; alumni stay hidden in BCC.
                    $mail->addAddress($smtpEmail, 'Job Portal Admin');

                    $bccCount = 0;
                    foreach ($validRecipients as $recipientEmail => $recipientName) {
                        $mail->addBCC($recipientEmail, $recipientName);
                        $bccCount++;
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

                        $mail->Body = '<!DOCTYPE html>
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
                    $smtpError = trim((string)$mail->ErrorInfo);
                    $exceptionMessage = trim((string)$e->getMessage());
                    $detail = $smtpError !== '' ? $smtpError : $exceptionMessage;

                    error_log('Employer Post Job PHPMailer error: ' . $detail);

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

    .location-branch-row{
        display:grid;
        grid-template-columns:minmax(0, 1fr) 320px;
        gap:14px;
        align-items:start;
    }

    .location-main-field,
    .branch-side-field{
        min-width:0;
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

        .location-branch-row{
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
                        type="hidden"
                        name="profile_location"
                        id="profile_location"
                        value="<?php echo htmlspecialchars($employer_profile_address); ?>"
                    >

                    <div class="location-branch-row">
                        <div class="location-main-field">
                            <input
                                type="text"
                                class="form-control"
                                name="location"
                                id="location"
                                value="<?php echo htmlspecialchars($display_location); ?>"
                                readonly
                                required
                            >
                            <div class="helper-text">
                                Automatically retrieved from your employer profile address.
                                <?php if ($employer_profile_address === ""): ?>
                                    Please update your employer profile address first.
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($employer_branches)): ?>
                            <div class="branch-side-field">
                                <select class="form-select" name="branch_location" id="branch_location">
                                    <option value="">Main company address</option>
                                    <?php foreach ($employer_branches as $branch): ?>
                                        <option
                                            value="<?php echo htmlspecialchars($branch); ?>"
                                            <?php echo ($selected_branch_location === $branch) ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars($branch); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="helper-text">Choose a branch if this job is assigned to another location.</div>
                            </div>
                        <?php endif; ?>
                    </div>
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


<script>
document.addEventListener("DOMContentLoaded", function () {
    const profileLocation = document.getElementById("profile_location");
    const locationInput = document.getElementById("location");
    const branchSelect = document.getElementById("branch_location");

    function updateLocationFromProfileOrBranch() {
        if (!locationInput || !profileLocation) {
            return;
        }

        const mainAddress = profileLocation.value || "";
        const selectedBranch = branchSelect ? branchSelect.value : "";

        locationInput.value = selectedBranch !== "" ? selectedBranch : mainAddress;
    }

    if (branchSelect) {
        branchSelect.addEventListener("change", updateLocationFromProfileOrBranch);
    }

    updateLocationFromProfileOrBranch();
});
</script>

</body>
</html>