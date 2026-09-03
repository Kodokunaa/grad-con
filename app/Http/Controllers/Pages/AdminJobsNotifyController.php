<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Mail\PageMailer;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminJobsNotifyController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            // ==========================
            // PHPMailer
            // ==========================

            $job_id = (int) (\gc_context()->query['job_id'] ?? 0);
            if ($job_id <= 0) {
                \gc_finish('Invalid job.');
            }
            // Get job info
            $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id=? LIMIT 1');
            $stmt->execute([$job_id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $job) {
                \gc_finish('Job not found.');
            }
            $target_course = trim($job['target_course'] ?? '');
            // Change this if your actual alumni job details page is different
            $applyUrl = \url('').'/alumni/job_details.php?id='.$job_id;
            // Public logo URL
            $logoUrl = rtrim(\url(''), '/').'/ccc3d.png';
            // SMTP account
            $smtpEmail = 'cccgradconn@gmail.com';
            $smtpPassword = \config('mail.mailers.smtp.password', '');
            $msg = '';
            $error = '';
            // Get all alumni with the same course as the job target_course
            if ($target_course !== '') {
                $recipientsStmt = $pdo->prepare("\r\n        SELECT DISTINCT id, fullname, email, course\r\n        FROM users\r\n        WHERE role = 'alumni'\r\n          AND is_active = 1\r\n                    AND COALESCE(receive_update_notifications, 1) = 1\r\n          AND email IS NOT NULL\r\n          AND email <> ''\r\n          AND course = ?\r\n        ORDER BY fullname ASC\r\n    ");
                $recipientsStmt->execute([$target_course]);
            } else {
                $recipientsStmt = $pdo->prepare("\r\n        SELECT DISTINCT id, fullname, email, course\r\n        FROM users\r\n        WHERE role = 'alumni'\r\n          AND is_active = 1\r\n                    AND COALESCE(receive_update_notifications, 1) = 1\r\n          AND email IS NOT NULL\r\n          AND email <> ''\r\n        ORDER BY fullname ASC\r\n    ");
                $recipientsStmt->execute();
            }
            $recipients = $recipientsStmt->fetchAll(\PDO::FETCH_ASSOC);
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $subject = trim(\gc_context()->post['subject'] ?? '');
                $message = trim(\gc_context()->post['message'] ?? '');
                if ($subject === '' || $message === '') {
                    $error = 'Subject and message are required.';
                } elseif (count($recipients) === 0) {
                    $error = 'There are no alumni with valid email addresses matching this target course.';
                } else {
                    try {
                        $mail = new PageMailer(true);
                        $mail->isSMTP();

                        $mail->SMTPAuth = true;

                        $mail->SMTPSecure = PageMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';
                        $mail->Timeout = 60;

                        $mail->setFrom($smtpEmail, 'Job Portal Admin');
                        $mail->Sender = $smtpEmail;
                        $mail->addReplyTo($smtpEmail, 'Job Portal Admin');
                        $mail->addAddress($smtpEmail, 'Job Portal Admin');
                        $bccCount = 0;
                        foreach ($recipients as $recipient) {
                            $recipientEmail = trim($recipient['email'] ?? '');
                            $recipientName = trim($recipient['fullname'] ?? 'Alumni');
                            if ($recipientEmail !== '') {
                                $mail->addBCC($recipientEmail, $recipientName);
                                $bccCount++;
                            }
                        }
                        if ($bccCount === 0) {
                            throw new \Exception('No valid alumni email addresses were found.');
                        }
                        // ==========================
                        // SAFE VALUES
                        // ==========================
                        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
                        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
                        $safeTitle = htmlspecialchars($job['title'] ?? '', ENT_QUOTES, 'UTF-8');
                        $safeCompany = htmlspecialchars($job['company'] ?? '', ENT_QUOTES, 'UTF-8');
                        $safeCourse = htmlspecialchars($job['target_course'] ?: 'All Courses', ENT_QUOTES, 'UTF-8');
                        $safeLocation = htmlspecialchars($job['location'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
                        $safeJobType = htmlspecialchars($job['job_type'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
                        $safeApplyUrl = htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8');
                        $safeLogoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
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
    <title>'.$safeSubject.'</title>
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
                                '.$safeSubject.'
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
                                                    <div style="font-size:12px; text-transform:uppercase; font-weight:700; color:#6b7280; margin-bottom:6px;">Company</div>
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeCompany.'</div>
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
                                                    <div style="font-size:15px; font-weight:700; color:#111827;">'.$safeLocation.'</div>
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
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 32px 8px 32px;">
                            <div style="display:inline-block; background:#fff7ed; color:#c2410c; border:1px solid #fdba74; border-radius:999px; padding:8px 14px; font-size:13px; font-weight:800;">
                                Target Course: '.$safeCourse.'
                            </div>
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
                        <td style="padding:10px 32px 8px 32px;" align="center">
                            <div style="font-size:12px; color:#6b7280; line-height:1.7;">
                                If the button does not work, copy and paste this link into your browser:<br>
                                <span style="color:#ea580c; word-break:break-all;">'.$safeApplyUrl.'</span>
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
                        $mail->AltBody = "Hello Alumni,\n\n".$subject."\n\n".$message."\n\n"."Job Details\n".'Job Title: '.($job['title'] ?? '')."\n".'Company: '.($job['company'] ?? '')."\n".'Location: '.($job['location'] ?? 'Not specified')."\n".'Job Type: '.($job['job_type'] ?? 'Not specified')."\n".'Target Course: '.($job['target_course'] ?: 'All Courses')."\n\n".'Apply Now: '.$applyUrl."\n\n".'This email was sent by Job Portal Admin.';
                        $mail->send();
                        $msg = "Notification email sent successfully to {$bccCount} alumni.";
                        \gc_context()->post = [];
                    } catch (\Exception $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $rawError = $mail->ErrorInfo ?? \gc_public_error($e);
                        $error = 'Email sending failed. '.$rawError;
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.jobs_notify', get_defined_vars());
        });
    }
}
