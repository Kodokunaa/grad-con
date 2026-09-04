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
                    $empStmt = $pdo->prepare('SELECT fullname, email, address, branch_location FROM users WHERE id = ? LIMIT 1');
                    $empStmt->execute([$posted_by]);
                    $empRow = $empStmt->fetch(\PDO::FETCH_ASSOC);
                    if ($empRow) {
                        $employer_fullname = trim($empRow['fullname'] ?? $employer_fullname);
                        $employer_email = trim($empRow['email'] ?? $employer_email);
                        $employer_profile_address = trim($empRow['address'] ?? '');
                        if (! empty($empRow['branch_location'])) {
                            $employer_branches = \gc_employer_post_job_parse_branch_locations($empRow['branch_location']);
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
                        $columns = ['title', 'company', 'employer_company', 'email_address', 'location', 'job_type', 'start_date', 'end_date', 'description', 'is_open', 'posted_by', 'employer_id', 'created_at'];
                        $params = [$title, $company, $employer_company, $email_address, $location, $job_type, $start_date, $end_date, $description, $is_open, $posted_by, $posted_by, date('Y-m-d H:i:s')];
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
                                $smtpEmail = (string) \config('mail.from.address');
                                // Paste your CURRENT Google App Password below.
                                // Do not use your normal Gmail password.
                                // Google may display App Passwords with spaces.
                                // Remove whitespace only.

                                $mail->setFrom($smtpEmail, 'Job Portal Admin');
                                $mail->addReplyTo($smtpEmail, 'Job Portal Admin');
                                // Keep one visible recipient; alumni stay hidden in BCC.
                                $mail->addAddress($smtpEmail, 'Job Portal Admin');
                                $bccCount = 0;
                                foreach ($validRecipients as $recipientEmail => $recipientName) {
                                    $mail->addBCC($recipientEmail, $recipientName);
                                    $bccCount++;
                                }
                                if ($bccCount > 0) {
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
