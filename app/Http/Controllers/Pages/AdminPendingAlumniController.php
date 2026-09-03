<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminPendingAlumniController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();
            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }

            if (! isset(\gc_context()->session['user']) || \gc_context()->session['user']['role'] !== 'admin') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }
            $success = '';
            $error = '';
            // Approve or reject
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $user_id = (int) (\gc_context()->post['user_id'] ?? 0);
                $action = trim(\gc_context()->post['action'] ?? '');
                if ($user_id > 0) {
                    if ($action === 'approve') {
                        // Get user details before updating
                        $userStmt = $pdo->prepare("SELECT fullname, email, username FROM users WHERE id = ? AND role = 'alumni' LIMIT 1");
                        $userStmt->execute([$user_id]);
                        $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
                        if ($user && ! empty($user['email'])) {
                            $stmt = $pdo->prepare("\r\n                    UPDATE users\r\n                    SET is_active = 1, status = 'approved'\r\n                    WHERE id = ? AND role = 'alumni'\r\n                ");
                            if ($stmt->execute([$user_id])) {
                                // Send approval email
                                try {
                                    $mail = \gc_make_mailer();
                                    $mail->addAddress($user['email'], $user['fullname']);
                                    $mail->Subject = 'Account Approval - GradConn';
                                    $mail->Body = "\r\n                            <html>\r\n                            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>\r\n                                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>\r\n                                    <h2 style='color: #f97316;'>Account Approved! ✓</h2>\r\n                                    <p>Dear <strong>".htmlspecialchars($user['fullname'])."</strong>,</p>\r\n                                    \r\n                                    <p>Great news! Your alumni account has been successfully approved by the administrator.</p>\r\n                                    \r\n                                    <p>You can now log in to your account using your credentials:</p>\r\n                                    <ul style='background-color: #f9fafb; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #f97316;'>\r\n                                        <li><strong>Username:</strong> ".htmlspecialchars($user['username'])."</li>\r\n                                        <li><strong>Login URL:</strong> <a href='".\url('')."/auth/alumni_login.php' style='color: #f97316; text-decoration: none;'>".\url('')."/auth/alumni_login.php</a></li>\r\n                                    </ul>\r\n                                    \r\n                                    <p>Once logged in, you'll have access to:</p>\r\n                                    <ul>\r\n                                        <li>View available job opportunities</li>\r\n                                        <li>Apply for jobs</li>\r\n                                        <li>Manage your profile and employment history</li>\r\n                                        <li>Connect with employers</li>\r\n                                    </ul>\r\n                                    \r\n                                    <p>If you have any questions or need assistance, please don't hesitate to contact us.</p>\r\n                                    \r\n                                    <p style='margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 15px;'>\r\n                                        <small style='color: #6b7280;'>\r\n                                            This is an automated message. Please do not reply to this email.\r\n                                        </small>\r\n                                    </p>\r\n                                </div>\r\n                            </body>\r\n                            </html>\r\n                        ";
                                    $mail->send();
                                    $success = 'Alumni account approved successfully. Approval email has been sent.';
                                } catch (\Exception $e) {
                                    if ($e instanceof PageResponse) {
                                        throw $e;
                                    }
                                    $success = 'Alumni account approved successfully. (Email notification failed: '.\gc_public_error($e).')';
                                }
                            } else {
                                $error = 'Failed to approve account.';
                            }
                        } else {
                            $error = 'Could not find user or user email address.';
                        }
                    } elseif ($action === 'reject') {
                        $stmt = $pdo->prepare("\r\n                UPDATE users\r\n                SET is_active = 0, status = 'rejected'\r\n                WHERE id = ? AND role = 'alumni'\r\n            ");
                        if ($stmt->execute([$user_id])) {
                            $success = 'Alumni account rejected successfully.';
                        } else {
                            $error = 'Failed to reject account.';
                        }
                    }
                }
            }
            // Get pending alumni
            $stmt = $pdo->query("\r\n    SELECT id, fullname, username, email, course, batch_year, status\r\n    FROM users\r\n    WHERE role = 'alumni' AND status = 'pending'\r\n    ORDER BY id ASC\r\n");
            $pendingUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.pending_alumni', get_defined_vars());
        });
    }
}
