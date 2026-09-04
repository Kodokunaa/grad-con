<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminApplicationsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }
            $job_id = (int) (\gc_context()->query['job_id'] ?? 0);
            if ($job_id <= 0) {
                \gc_finish('Invalid job.');
            }
            $msg = '';
            $error = '';
            // ==========================
            // Job info
            // ==========================
            $jstmt = $pdo->prepare("\r\n    SELECT j.*, u.role AS poster_role, u.fullname AS poster_name\r\n    FROM jobs j\r\n    JOIN users u ON u.id = j.posted_by\r\n    WHERE j.id=? \r\n    LIMIT 1\r\n");
            $jstmt->execute([$job_id]);
            $job = $jstmt->fetch(\PDO::FETCH_ASSOC);
            if (! $job) {
                \gc_finish('Job not found.');
            }
            $isEmployerPosted = isset($job['poster_role']) && strtolower($job['poster_role']) === 'employer';
            $isAdminPosted = isset($job['poster_role']) && strtolower($job['poster_role']) === 'admin';
            // ==========================
            // Handle actions
            // ==========================
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['application_id'], \gc_context()->post['action'])) {
                if ($isEmployerPosted) {
                    $error = 'You cannot manage applications for jobs posted by employer.';
                } else {
                    $application_id = (int) (\gc_context()->post['application_id'] ?? 0);
                    $action = trim(\gc_context()->post['action'] ?? '');
                    $action_message = trim(\gc_context()->post['action_message'] ?? '');
                    if ($application_id <= 0 || ! in_array($action, ['accept', 'interview', 'reject'], true)) {
                        $error = 'Invalid action.';
                    } else {
                        try {
                            $checkStmt = $pdo->prepare("\r\n                    SELECT \r\n                        a.id,\r\n                        a.status,\r\n                        a.job_id,\r\n                        a.alumni_id,\r\n                        u.email,\r\n                        u.fullname,\r\n                        j.title AS job_title,\r\n                        j.company\r\n                    FROM applications a\r\n                    INNER JOIN jobs j ON a.job_id = j.id\r\n                    INNER JOIN users u ON a.alumni_id = u.id\r\n                    WHERE a.id = ? AND a.job_id = ?\r\n                    LIMIT 1\r\n                ");
                            $checkStmt->execute([$application_id, $job_id]);
                            $application = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                            if (! $application) {
                                $error = 'Application not found.';
                            } else {
                                $current_status = \gc_admin_applications_normalize_status($application['status'] ?? 'pending');
                                if ($current_status === 'cancelled') {
                                    $error = 'This application was already cancelled by the alumni.';
                                } elseif (in_array($action, ['accept', 'interview'], true) && $action_message === '') {
                                    $error = 'Please enter a message before continuing.';
                                } else {
                                    if ($action === 'accept') {
                                        $new_status = 'hired';
                                    } elseif ($action === 'interview') {
                                        $new_status = 'interview';
                                    } else {
                                        $new_status = 'rejected';
                                    }
                                    $updateStmt = $pdo->prepare("\r\n                            UPDATE applications\r\n                            SET status = ?\r\n                            WHERE id = ? AND job_id = ?\r\n                        ");
                                    $updateStmt->execute([$new_status, $application_id, $job_id]);
                                    if (in_array($action, ['accept', 'interview'], true)) {
                                        $mailResult = \gc_admin_applications_sendAdminApplicantEmail($application, $action, $action_message);
                                        if ($mailResult['success']) {
                                            if ($action === 'accept') {
                                                $msg = 'Application has been marked as hired and the congratulations email was sent.';
                                            } else {
                                                $msg = 'Application has been moved to interview and the email was sent.';
                                            }
                                        } elseif ($action === 'accept') {
                                            $msg = 'Application has been marked as hired, but email could not be sent.';
                                        } else {
                                            $msg = 'Application has been moved to interview, but email could not be sent.';
                                        }
                                    } else {
                                        $msg = 'Application has been rejected successfully.';
                                    }
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
            }
            // ==========================
            // Applications list
            // ==========================
            $selectParts = ['a.id AS application_id', 'a.status', 'a.message', 'a.cancel_reason', 'a.cancelled_at', 'a.created_at', 'a.job_id', 'u.id AS alumni_id', 'u.fullname', 'u.username', 'u.email', 'u.course', 'u.batch_year', 'u.birthdate', 'u.age', 'u.gender', 'u.civil_status', 'u.contact_number', 'u.address', 'u.indigenous_tribe', 'u.special_needs', 'u.employment_status', 'u.job_aligned', 'u.profile_picture', 'u.career_objective', 'u.skills', 'u.work_experience', 'u.trainings', 'u.is_active', 'a.resume_file', 'NULL AS resume', 'NULL AS resume_path', 'NULL AS cv', 'NULL AS cv_file', 'NULL AS file', 'NULL AS attachment'];
            $appSql = "\r\n    SELECT \r\n        ".implode(",\n        ", $selectParts)."\r\n    FROM applications a\r\n    JOIN users u ON u.id = a.alumni_id\r\n    WHERE a.job_id=?\r\n    ORDER BY a.id DESC\r\n";
            $astmt = $pdo->prepare($appSql);
            $astmt->execute([$job_id]);
            $applications = $astmt->fetchAll(\PDO::FETCH_ASSOC);
            // ==========================
            // Education / Employment details
            // ==========================
            $alumniIds = [];
            foreach ($applications as $row) {
                $alumniIds[] = (int) $row['alumni_id'];
            }
            $alumniIds = array_values(array_unique(array_filter($alumniIds)));
            $educationByUser = [];
            $employmentByUser = [];
            if (! empty($alumniIds)) {
                $placeholders = implode(',', array_fill(0, count($alumniIds), '?'));
                try {
                    $stmt = $pdo->prepare("\r\n            SELECT user_id, school_name, degree, start_year, end_year\r\n            FROM alumni_education\r\n            WHERE user_id IN ({$placeholders})\r\n            ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 9999) DESC, id DESC\r\n        ");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $educationByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $educationByUser = [];
                }
                try {
                    $stmt = $pdo->prepare("\r\n            SELECT user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description\r\n            FROM employment_history\r\n            WHERE user_id IN ({$placeholders})\r\n            ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC\r\n        ");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $employmentByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $employmentByUser = [];
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.applications', get_defined_vars());
        });
    }
}
