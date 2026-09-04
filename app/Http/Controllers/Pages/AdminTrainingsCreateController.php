<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Mail\PageMailer;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminTrainingsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            // PHPMailer

            $msg = '';
            $error = '';
            $allowed_courses = ['BSIS', 'BSTM', 'BSHM', 'BSED Math', 'BSED Science', 'BSNED', 'BPA', 'Open for All'];
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $content = trim(\gc_context()->post['content'] ?? '');
                $training_date = trim(\gc_context()->post['training_date'] ?? '');
                $location = trim(\gc_context()->post['location'] ?? '');
                $target_course = trim(\gc_context()->post['target_course'] ?? '');
                $image_name = null;
                if ($title === '' || $content === '' || $training_date === '' || $target_course === '') {
                    $error = 'Title, description, training date, and target course are required.';
                } elseif (! in_array($target_course, $allowed_courses, true)) {
                    $error = 'Invalid target course selected.';
                } else {
                    if (! empty(\gc_files()['image']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
                        } else {
                            $upload_dir = \storage_path('app/private/files/uploads/trainings/');
                            if (! is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $image_name = 'training_'.time().'_'.rand(1000, 9999).'.'.$ext;
                            $target = $upload_dir.$image_name;
                            if (! \gc_move_upload(\gc_files()['image']['tmp_name'], $target)) {
                                $error = 'Image upload failed.';
                            }
                        }
                    }
                    if ($error === '') {
                        $stmt = $pdo->prepare("\r\n                INSERT INTO trainings(title, content, training_date, location, target_course, image, posted_by)\r\n                VALUES(?,?,?,?,?,?,?)\r\n            ");
                        $stmt->execute([$title, $content, $training_date, $location, $target_course, $image_name, \gc_context()->session['user']['id']]);
                        // ==========================
                        // SEND EMAIL NOTIFICATION
                        // ==========================
                        try {
                            if ($target_course === 'Open for All') {
                                $notifyStmt = $pdo->prepare("\r\n                        SELECT fullname, email, course\r\n                        FROM users\r\n                        WHERE role = 'alumni'\r\n                          AND is_active = 1\r\n                          AND employment_status = 'Unemployed'\r\n                          AND email IS NOT NULL\r\n                          AND email <> ''\r\n                        ORDER BY fullname ASC\r\n                    ");
                                $notifyStmt->execute();
                            } else {
                                $notifyStmt = $pdo->prepare("\r\n                        SELECT fullname, email, course\r\n                        FROM users\r\n                        WHERE role = 'alumni'\r\n                          AND is_active = 1\r\n                          AND employment_status = 'Unemployed'\r\n                          AND course = ?\r\n                          AND email IS NOT NULL\r\n                          AND email <> ''\r\n                        ORDER BY fullname ASC\r\n                    ");
                                $notifyStmt->execute([$target_course]);
                            }
                            $recipients = $notifyStmt->fetchAll(\PDO::FETCH_ASSOC);
                            foreach ($recipients as $r) {
                                $mail = new PageMailer(true);
                                // SMTP CONFIG
                                $mail->isSMTP();

                                $mail->SMTPAuth = true;

                                $mail->SMTPSecure = PageMailer::ENCRYPTION_STARTTLS;
                                $mail->Port = 587;
                                $mail->setFrom('cccgradconn@gmail.com', 'Training Notification');
                                $mail->addAddress($r['email'], $r['fullname']);
                                $mail->isHTML(true);
                                $mail->Subject = 'New Training Opportunity Available';
                                $safeName = htmlspecialchars($r['fullname']);
                                $safeTitle = htmlspecialchars($title);
                                $safeDate = htmlspecialchars($training_date);
                                $safeLocation = htmlspecialchars($location !== '' ? $location : 'To be announced');
                                $safeCourse = htmlspecialchars($target_course);
                                $safeContent = nl2br(htmlspecialchars($content));
                                $mail->Body = "\r\n                        <div style='font-family: Arial, sans-serif; font-size: 14px; color: #111827; line-height: 1.6;'>\r\n                            <h2 style='color:#f97316; margin-bottom:10px;'>New Training Opportunity</h2>\r\n\r\n                            <p>Hello <strong>{$safeName}</strong>,</p>\r\n\r\n                            <p>A new training has been posted for <strong>{$safeCourse}</strong>.</p>\r\n\r\n                            <table style='border-collapse:collapse; width:100%; margin-top:10px; margin-bottom:16px;'>\r\n                                <tr>\r\n                                    <td style='padding:8px; border:1px solid #e5e7eb; width:160px;'><strong>Title</strong></td>\r\n                                    <td style='padding:8px; border:1px solid #e5e7eb;'>{$safeTitle}</td>\r\n                                </tr>\r\n                                <tr>\r\n                                    <td style='padding:8px; border:1px solid #e5e7eb;'><strong>Date</strong></td>\r\n                                    <td style='padding:8px; border:1px solid #e5e7eb;'>{$safeDate}</td>\r\n                                </tr>\r\n                                <tr>\r\n                                    <td style='padding:8px; border:1px solid #e5e7eb;'><strong>Location</strong></td>\r\n                                    <td style='padding:8px; border:1px solid #e5e7eb;'>{$safeLocation}</td>\r\n                                </tr>\r\n                            </table>\r\n\r\n                            <p><strong>Description:</strong></p>\r\n                            <div style='padding:12px; border:1px solid #e5e7eb; background:#f9fafb; border-radius:8px;'>\r\n                                {$safeContent}\r\n                            </div>\r\n\r\n                            <p style='margin-top:16px;'>Please log in to your alumni account for more details.</p>\r\n\r\n                            <p>Thank you.</p>\r\n                        </div>\r\n                    ";
                                $mail->AltBody = "Hello {$r['fullname']},\n\n"."A new training has been posted.\n\n"."Title: {$title}\n"."Date: {$training_date}\n".'Location: '.($location !== '' ? $location : 'To be announced')."\n"."Target Course: {$target_course}\n\n"."Description:\n{$content}\n\n".'Please log in to your alumni account for more details.';
                                $mail->send();
                            }
                            $msg = 'Training posted successfully and notifications sent!';
                        } catch (\Exception $e) {
                            if ($e instanceof PageResponse) {
                                throw $e;
                            }
                            $msg = 'Training posted successfully, but email notification failed: '.\gc_public_error($e);
                        }
                        \gc_context()->post = [];
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.trainings_create', get_defined_vars());
        });
    }
}
