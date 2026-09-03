<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniMyApplicationsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $alumni_id = (int) \gc_context()->session['user']['id'];
            $msg = '';
            $error = '';
            /*
            |--------------------------------------------------------------------------
            | CANCEL APPLICATION WITH REASON
            |--------------------------------------------------------------------------
            */
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['remove_application'])) {
                $application_id = (int) (\gc_context()->post['application_id'] ?? 0);
                if ($application_id <= 0) {
                    $error = 'Invalid application.';
                } else {
                    try {
                        $checkStmt = $pdo->prepare('SELECT id, status, alumni_id FROM applications WHERE id = ? AND alumni_id = ? LIMIT 1');
                        $checkStmt->execute([$application_id, $alumni_id]);
                        $application = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                        if (! $application) {
                            $error = 'Application not found.';
                        } elseif (\gc_alumni_my_applications_normalize_status($application['status'] ?? '') !== 'cancelled') {
                            $error = 'Only cancelled applications can be removed.';
                        } else {
                            $deleteStmt = $pdo->prepare('DELETE FROM applications WHERE id = ? AND alumni_id = ?');
                            $deleteStmt->execute([$application_id, $alumni_id]);
                            $msg = 'Cancelled application removed successfully.';
                        }
                    } catch (\PDOException $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Database error: '.\gc_public_error($e);
                    }
                }
            } elseif (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['cancel_application'])) {
                $application_id = (int) (\gc_context()->post['application_id'] ?? 0);
                $cancel_reason = trim(\gc_context()->post['cancel_reason'] ?? '');
                if ($application_id <= 0) {
                    $error = 'Invalid application.';
                } elseif ($cancel_reason === '') {
                    $error = 'Please provide a reason before cancelling your application.';
                } elseif (strlen($cancel_reason) < 10) {
                    $error = 'Please provide a clearer reason. Minimum of 10 characters is required.';
                } else {
                    try {
                        $checkStmt = $pdo->prepare("\r\n                SELECT a.id, a.status, a.alumni_id, a.job_id, j.title, j.company\r\n                FROM applications a\r\n                JOIN jobs j ON j.id = a.job_id\r\n                WHERE a.id = ? AND a.alumni_id = ?\r\n                LIMIT 1\r\n            ");
                        $checkStmt->execute([$application_id, $alumni_id]);
                        $application = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                        if (! $application) {
                            $error = 'Application not found.';
                        } else {
                            $current_status = \gc_alumni_my_applications_normalize_status($application['status'] ?? 'pending');
                            if (in_array($current_status, ['accepted', 'hired', 'rejected', 'cancelled'])) {
                                $error = 'This application can no longer be cancelled.';
                            } else {
                                $cancelStmt = $pdo->prepare("\r\n                        UPDATE applications\r\n                        SET \r\n                            status = 'cancelled',\r\n                            cancel_reason = ?,\r\n                            cancelled_at = NOW()\r\n                        WHERE id = ? AND alumni_id = ?\r\n                    ");
                                $cancelStmt->execute([$cancel_reason, $application_id, $alumni_id]);
                                $msg = 'Application cancelled successfully. Your reason has been saved and can be viewed by the employer/admin.';
                            }
                        }
                    } catch (\PDOException $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Database error: '.\gc_public_error($e);
                    }
                }
            }
            /*
            |--------------------------------------------------------------------------
            | LOAD APPLICATIONS
            |--------------------------------------------------------------------------
            */
            try {
                $stmt = $pdo->prepare("\r\n        SELECT a.*, j.title, j.company, j.location, j.job_type\r\n        FROM applications a\r\n        JOIN jobs j ON j.id = a.job_id\r\n        WHERE a.alumni_id = ?\r\n        ORDER BY a.id DESC\r\n    ");
                $stmt->execute([$alumni_id]);
                $apps = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $apps = [];
                $error = 'Database error: '.\gc_public_error($e);
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.my_applications', get_defined_vars());
        });
    }
}
