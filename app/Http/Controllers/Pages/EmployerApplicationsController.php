<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\FileController;
use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerApplicationsController extends PageController
{
    public function __invoke(Request $request)
    {
        if ($request->filled('view_resume')) {
            return app(FileController::class)->resume($request);
        }

        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }
            if (! isset(\gc_context()->session['user']) || (\gc_context()->session['user']['role'] ?? '') !== 'employer') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }
            $success = '';
            $error = '';
            $employer_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['application_id'], \gc_context()->post['action'])) {
                $application_id = (int) (\gc_context()->post['application_id'] ?? 0);
                $action = trim(\gc_context()->post['action'] ?? '');
                $action_message = trim(\gc_context()->post['action_message'] ?? '');
                if ($application_id <= 0 || ! in_array($action, ['accept', 'interview', 'reject'], true)) {
                    $error = 'Invalid action.';
                } else {
                    try {
                        $checkStmt = $pdo->prepare("\r\n                SELECT \r\n                    a.id,\r\n                    a.status,\r\n                    a.job_id,\r\n                    a.alumni_id,\r\n                    u.email,\r\n                    u.fullname,\r\n                    j.title,\r\n                    j.company,\r\n                    j.employer_company\r\n                FROM applications a\r\n                INNER JOIN jobs j ON a.job_id = j.id\r\n                INNER JOIN users u ON a.alumni_id = u.id\r\n                WHERE a.id = ? AND j.posted_by = ?\r\n                LIMIT 1\r\n            ");
                        $checkStmt->execute([$application_id, $employer_id]);
                        $application = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                        if (! $application) {
                            $error = 'Application not found or you are not allowed to manage it.';
                        } else {
                            $current_status = \gc_employer_applications_normalize_status($application['status'] ?? 'pending');
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
                                $updateStmt = $pdo->prepare("\r\n                        UPDATE applications\r\n                        SET status = ?\r\n                        WHERE id = ?\r\n                    ");
                                $updateStmt->execute([$new_status, $application_id]);
                                if ($action === 'accept' || $action === 'interview') {
                                    $mailResult = \gc_employer_applications_sendApplicantEmail($application, $action, $action_message);
                                    if ($mailResult['success']) {
                                        $success = $action === 'accept' ? 'Application has been marked as hired and the congratulations email was sent.' : 'Application has been marked for interview and the message was sent.';
                                    } else {
                                        $success = $action === 'accept' ? 'Application has been marked as hired, but email could not be sent.' : 'Application has been marked for interview, but email could not be sent.';
                                    }
                                } elseif ($action === 'reject') {
                                    $success = 'Application has been rejected successfully.';
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
            $applications = [];
            try {
                $applicationFields = ['a.id AS application_id', 'a.status', 'a.resume_file', 'a.created_at', 'a.job_id', 'a.cancel_reason', 'a.cancelled_at', 'j.title AS job_title', 'j.company', 'j.start_date AS job_start_date', 'j.end_date AS job_end_date', 'u.id AS alumni_id', 'u.fullname', 'u.username', 'u.email', 'u.course', 'u.batch_year', 'u.birthdate', 'u.age', 'u.gender', 'u.civil_status', 'u.contact_number', 'u.address', 'u.indigenous_tribe', 'u.special_needs', 'u.employment_status', 'u.job_aligned', 'u.profile_picture', 'u.career_objective', 'u.skills', 'u.work_experience', 'u.trainings', 'u.is_active'];
                $applicationsSql = "\r\n        SELECT ".implode(",\n            ", $applicationFields)."\r\n        FROM applications a\r\n        INNER JOIN jobs j ON a.job_id = j.id\r\n        INNER JOIN users u ON a.alumni_id = u.id\r\n        WHERE j.posted_by = ?\r\n        ORDER BY a.id DESC\r\n    ";
                $stmt = $pdo->prepare($applicationsSql);
                $stmt->execute([$employer_id]);
                $applications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Database error: '.\gc_public_error($e);
            }
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

            return $this->pageView('pages.employer.applications', get_defined_vars());
        });
    }
}
