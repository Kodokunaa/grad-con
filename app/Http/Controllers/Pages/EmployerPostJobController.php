<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Mail\PageMailer;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerPostJobController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }
            if (! isset(\gc_context()->session['user']) || (\gc_context()->session['user']['role'] ?? '') !== 'employer') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }

            $msg = '';
            $error = '';
            $mail_notice = '';
            $posted_by = (int) (\gc_context()->session['user']['id'] ?? 0);
            $employer_fullname = trim(\gc_context()->session['user']['fullname'] ?? '');
            $employer_email = trim(\gc_context()->session['user']['email'] ?? '');
            $employer_profile_address = '';
            $employer_branches = [];
            if ($posted_by > 0) {
                try {
                    $userCols = \gc_employer_post_job_get_table_columns($pdo, 'users');
                    $selectCols = ['fullname', 'email'];
                    // Profile address field. This uses users.address if available.
                    if (in_array('address', $userCols, true)) {
                        $selectCols[] = 'address';
                    }
                    // Optional profile branch fields. Any of these may exist depending on your database.
                    $possibleBranchCols = ['branch_location', 'branch_locations', 'company_branch', 'company_branches', 'branches', 'branch_address', 'branch_addresses'];
                    foreach ($possibleBranchCols as $col) {
                        if (in_array($col, $userCols, true)) {
                            $selectCols[] = $col;
                        }
                    }
                    $selectSql = implode(', ', array_map(fn ($col) => "`{$col}`", array_unique($selectCols)));
                    $empStmt = $pdo->prepare("\r\n            SELECT {$selectSql}\r\n            FROM users\r\n            WHERE id = ?\r\n            LIMIT 1\r\n        ");
                    $empStmt->execute([$posted_by]);
                    $empRow = $empStmt->fetch(\PDO::FETCH_ASSOC);
                    if ($empRow) {
                        $employer_fullname = trim($empRow['fullname'] ?? $employer_fullname);
                        $employer_email = trim($empRow['email'] ?? $employer_email);
                        $employer_profile_address = trim($empRow['address'] ?? '');
                        foreach ($possibleBranchCols as $col) {
                            if (! empty($empRow[$col])) {
                                $employer_branches = array_merge($employer_branches, \gc_employer_post_job_parse_branch_locations($empRow[$col]));
                            }
                        }
                    }
                    // Optional separate table support:
                    // This works if you have a table named employer_branches with employer_id or user_id,
                    // and a branch location/address column.
                    if (\gc_employer_post_job_table_exists($pdo, 'employer_branches')) {
                        $branchCols = \gc_employer_post_job_get_table_columns($pdo, 'employer_branches');
                        $ownerCol = '';
                        foreach (['employer_id', 'user_id', 'created_by'] as $candidate) {
                            if (in_array($candidate, $branchCols, true)) {
                                $ownerCol = $candidate;
                                break;
                            }
                        }
                        $locationCol = '';
                        foreach (['branch_location', 'location', 'address', 'branch_address'] as $candidate) {
                            if (in_array($candidate, $branchCols, true)) {
                                $locationCol = $candidate;
                                break;
                            }
                        }
                        $nameCol = '';
                        foreach (['branch_name', 'name', 'title'] as $candidate) {
                            if (in_array($candidate, $branchCols, true)) {
                                $nameCol = $candidate;
                                break;
                            }
                        }
                        if ($ownerCol !== '' && $locationCol !== '') {
                            $branchSql = "SELECT `{$locationCol}` AS branch_location";
                            if ($nameCol !== '') {
                                $branchSql .= ", `{$nameCol}` AS branch_name";
                            }
                            $branchSql .= " FROM employer_branches WHERE `{$ownerCol}` = ?";
                            if (in_array('is_active', $branchCols, true)) {
                                $branchSql .= ' AND is_active = 1';
                            }
                            $branchSql .= ' ORDER BY id DESC';
                            $branchStmt = $pdo->prepare($branchSql);
                            $branchStmt->execute([$posted_by]);
                            $branchRows = $branchStmt->fetchAll(\PDO::FETCH_ASSOC);
                            foreach ($branchRows as $branchRow) {
                                $branchName = trim($branchRow['branch_name'] ?? '');
                                $branchLocation = trim($branchRow['branch_location'] ?? '');
                                if ($branchLocation !== '') {
                                    $displayBranch = $branchName !== '' ? $branchName.' - '.$branchLocation : $branchLocation;
                                    if (! in_array($displayBranch, $employer_branches, true)) {
                                        $employer_branches[] = $displayBranch;
                                    }
                                }
                            }
                        }
                    }
                    $employer_branches = array_values(array_unique(array_filter($employer_branches)));
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    // keep session values if query fails
                }
            }
            $default_location = $employer_profile_address;
            $selected_branch_location = trim(\gc_context()->post['branch_location'] ?? '');
            $display_location = $selected_branch_location !== '' ? $selected_branch_location : (trim(\gc_context()->post['location'] ?? '') !== '' ? trim(\gc_context()->post['location'] ?? '') : $default_location);
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $company = $employer_fullname;
                $employer_company = $employer_fullname;
                $email_address = $employer_email;
                $posted_location = trim(\gc_context()->post['location'] ?? '');
                $branch_location = trim(\gc_context()->post['branch_location'] ?? '');
                $profile_location = trim(\gc_context()->post['profile_location'] ?? $employer_profile_address);
                // Built-in location rule:
                // 1. If employer chooses a branch, use the selected branch location.
                // 2. If no branch is selected, use the address saved in the employer profile.
                // 3. The location text field is only a final display value.
                $location = $branch_location !== '' ? $branch_location : $profile_location;
                if ($location === '' && $posted_location !== '') {
                    $location = $posted_location;
                }
                $job_type = trim(\gc_context()->post['job_type'] ?? '');
                $start_date = trim(\gc_context()->post['start_date'] ?? '');
                $end_date = trim(\gc_context()->post['end_date'] ?? '');
                $description = trim(\gc_context()->post['description'] ?? '');
                $is_open = isset(\gc_context()->post['is_open']) ? 1 : 0;
                if ($title === '' || $employer_company === '' || $email_address === '' || $job_type === '' || $start_date === '' || $end_date === '' || $description === '') {
                    $error = 'Please fill in all required fields.';
                } elseif ($location === '') {
                    $error = 'Please complete the employer profile address first, or select an available branch location.';
                } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
                    $error = 'Please enter valid start and end dates.';
                } elseif (strtotime($end_date) < strtotime($start_date)) {
                    $error = 'End date cannot be earlier than start date.';
                } else {
                    try {
                        $hasEmailColumn = false;
                        $hasStartDateColumn = false;
                        $hasEndDateColumn = false;
                        try {
                            $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'email_address'");
                            $hasEmailColumn = $checkCol && $checkCol->fetch(\PDO::FETCH_ASSOC) ? true : false;
                            $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'start_date'");
                            $hasStartDateColumn = $checkCol && $checkCol->fetch(\PDO::FETCH_ASSOC) ? true : false;
                            $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'end_date'");
                            $hasEndDateColumn = $checkCol && $checkCol->fetch(\PDO::FETCH_ASSOC) ? true : false;
                        } catch (\Throwable $e) {
                            if ($e instanceof PageResponse) {
                                throw $e;
                            }
                            $hasEmailColumn = false;
                            $hasStartDateColumn = false;
                            $hasEndDateColumn = false;
                        }
                        // Also check for employer_id and created_at columns so inserted jobs match other parts of the app
                        $hasEmployerIdColumn = false;
                        $hasCreatedAtColumn = false;
                        try {
                            $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'employer_id'");
                            $hasEmployerIdColumn = $checkCol && $checkCol->fetch(\PDO::FETCH_ASSOC) ? true : false;
                            $checkCol = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'created_at'");
                            $hasCreatedAtColumn = $checkCol && $checkCol->fetch(\PDO::FETCH_ASSOC) ? true : false;
                        } catch (\Throwable $e) {
                            if ($e instanceof PageResponse) {
                                throw $e;
                            }
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
                        $sql = 'INSERT INTO jobs('.implode(', ', $columns).") VALUES({$placeholders})";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $job_id = (int) $pdo->lastInsertId();
                        $applyUrl = \url('').'/index.php?force_login=1';
                        $logoUrl = rtrim(\url(''), '/').'/ccc3d.png';
                        $alumniStmt = $pdo->prepare("\r\n                SELECT fullname, email\r\n                FROM users\r\n                WHERE role = 'alumni'\r\n                  AND COALESCE(is_active, 0) = 1\r\n                                    AND COALESCE(receive_update_notifications, 1) = 1\r\n                  AND email IS NOT NULL\r\n                  AND TRIM(email) <> ''\r\n                ORDER BY fullname ASC\r\n            ");
                        $alumniStmt->execute();
                        $recipients = $alumniStmt->fetchAll(\PDO::FETCH_ASSOC);
                        $validRecipients = [];
                        foreach ($recipients as $recipient) {
                            $recipientEmail = trim((string) ($recipient['email'] ?? ''));
                            if ($recipientEmail !== '' && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                                $validRecipients[$recipientEmail] = trim((string) ($recipient['fullname'] ?? 'Alumni'));
                            }
                        }
                        if (! empty($validRecipients)) {
                            $mail = new PageMailer(true);
                            try {
                                // ==========================
                                // GMAIL SMTP CONFIGURATION
                                // ==========================
                                $smtpEmail = 'cccgradconn@gmail.com';
                                // Paste your CURRENT Google App Password below.
                                // Do not use your normal Gmail password.
                                $smtpPassword = \config('mail.mailers.smtp.password', '');
                                // Google may display App Passwords with spaces.
                                // Remove whitespace only.
                                $smtpPassword = \config('mail.mailers.smtp.password', '');
                                $mail->isSMTP();

                                $mail->SMTPAuth = true;
                                $mail->AuthType = 'LOGIN';

                                $mail->SMTPSecure = PageMailer::ENCRYPTION_STARTTLS;
                                $mail->Port = 587;
                                $mail->CharSet = 'UTF-8';
                                $mail->Timeout = 60;
                                $mail->SMTPKeepAlive = false;

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
                                    $mail->Subject = 'New Job Opportunity: '.$title;
                                    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                                    $safeEmployerCompany = htmlspecialchars($employer_company, ENT_QUOTES, 'UTF-8');
                                    $safeEmployerEmail = htmlspecialchars($email_address, ENT_QUOTES, 'UTF-8');
                                    $safeLocation = htmlspecialchars($location ?: 'Not specified', ENT_QUOTES, 'UTF-8');
                                    $safeJobType = htmlspecialchars($job_type ?: 'Not specified', ENT_QUOTES, 'UTF-8');
                                    $safeStartDate = htmlspecialchars(date('F j, Y', strtotime($start_date)), ENT_QUOTES, 'UTF-8');
                                    $safeEndDate = htmlspecialchars(date('F j, Y', strtotime($end_date)), ENT_QUOTES, 'UTF-8');
                                    $safeApplyUrl = htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8');
                                    $safeLogoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
                                    $safeDesc = nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));
                                    $customMessage = 'A new job opportunity has been posted. Please click Apply Now to go directly to the login page of the system.';
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
                                <img src="'.$safeLogoUrl.'" alt="CCC3D Logo" style="max-width:95px; height:auto; display:block;">
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
                                            '.$safeMessage.'
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
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeTitle.'</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%" style="padding-left:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Employer Name</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeEmployerCompany.'</div>
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
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeEmployerEmail.'</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%" style="padding-left:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Job Type</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeJobType.'</div>
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
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeStartDate.'</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%" style="padding-left:8px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">End Date</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeEndDate.'</div>
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
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeLocation.'</div>
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
                                        <div style="font-size:14px; line-height:1.8; color:#374151;">'.$safeDesc.'</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 8px 32px;" align="center">
                            <a href="'.$safeApplyUrl.'"
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
                                    $mail->AltBody = "Hello Alumni,\n\n"."A new job opportunity has been posted.\n\n"."Job Title: {$title}\n"."Employer Name: {$employer_company}\n"."Email Address: {$email_address}\n"."Job Type: {$job_type}\n".'Start Date: '.date('F j, Y', strtotime($start_date))."\n".'End Date: '.date('F j, Y', strtotime($end_date))."\n".'Location: '.($location ?: 'Not specified')."\n\n"."Description:\n{$description}\n\n"."Log in here to apply: {$applyUrl}";
                                    $mail->send();
                                    $mail_notice = " Email notification sent to {$bccCount} alumni successfully.";
                                } else {
                                    $mail_notice = ' Job posted, but no alumni email recipients were found.';
                                }
                            } catch (\Exception $e) {
                                if ($e instanceof PageResponse) {
                                    throw $e;
                                }
                                $smtpError = trim((string) $mail->ErrorInfo);
                                $exceptionMessage = trim((string) \gc_public_error($e));
                                $detail = $smtpError !== '' ? $smtpError : $exceptionMessage;
                                error_log('Employer Post Job PHPMailer error: '.$detail);
                                if (stripos($detail, 'Daily user sending limit exceeded') !== false || stripos($detail, '5.4.5') !== false) {
                                    $mail_notice = " Job posted, but Gmail's daily sending limit has been reached. ".'Please try again after the Gmail quota resets.';
                                } elseif (stripos($detail, 'authenticate') !== false || stripos($detail, '535') !== false || stripos($detail, 'username and password') !== false) {
                                    $mail_notice = ' Job posted, but Gmail rejected the SMTP login. '.'Check that the App Password belongs to '.$smtpEmail.' and that it is still active.';
                                } elseif (stripos($detail, 'connect') !== false || stripos($detail, 'timed out') !== false || stripos($detail, 'connection') !== false) {
                                    $mail_notice = ' Job posted, but the system could not connect to Gmail SMTP. '.'Check your internet connection and whether port 587 is available.';
                                } else {
                                    $mail_notice = ' Job posted, but email sending failed: '.($detail !== '' ? $detail : 'Unknown PHPMailer error.');
                                }
                            }
                        } else {
                            $mail_notice = ' Job posted, but no alumni were found.';
                        }
                        $msg = 'Job posted successfully!'.$mail_notice;
                        \gc_context()->post = [];
                    } catch (\PDOException $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Database error: '.\gc_public_error($e);
                    }
                }
            }

            return $this->pageView('pages.employer.post_job', get_defined_vars());
        });
    }
}
