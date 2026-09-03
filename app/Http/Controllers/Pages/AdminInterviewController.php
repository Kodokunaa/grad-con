<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminInterviewController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }
            if (! isset(\gc_context()->session['user']) || (\gc_context()->session['user']['role'] ?? '') !== 'admin') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }
            $admin_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            $application_id = (int) (\gc_context()->query['application_id'] ?? \gc_context()->post['application_id'] ?? 0);
            $success = '';
            $error = '';
            try {
                \gc_context()->schemaChange($pdo, "\r\n        CREATE TABLE IF NOT EXISTS interviews (\r\n            id INT AUTO_INCREMENT PRIMARY KEY,\r\n            application_id INT NOT NULL,\r\n            employer_id INT NOT NULL DEFAULT 0,\r\n            admin_id INT NULL,\r\n            alumni_id INT NOT NULL,\r\n            job_id INT NOT NULL,\r\n            interview_date DATE NOT NULL,\r\n            interview_time TIME NOT NULL,\r\n            location VARCHAR(255) NOT NULL,\r\n            message TEXT NULL,\r\n            status VARCHAR(50) DEFAULT 'scheduled',\r\n            email_sent TINYINT(1) DEFAULT 0,\r\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\r\n        )\r\n    ");
            } catch (\PDOException $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Database error creating interviews table: '.\gc_public_error($e);
            }
            if ($application_id <= 0) {
                \gc_finish('Invalid application.');
            }
            $stmt = $pdo->prepare("\r\n    SELECT \r\n        a.id AS application_id,\r\n        a.status,\r\n        a.alumni_id,\r\n        a.job_id,\r\n        u.fullname,\r\n        u.email,\r\n        j.title AS job_title,\r\n        j.company,\r\n        j.employer_company,\r\n        j.posted_by\r\n    FROM applications a\r\n    INNER JOIN users u ON a.alumni_id = u.id\r\n    INNER JOIN jobs j ON a.job_id = j.id\r\n    WHERE a.id = ?\r\n    LIMIT 1\r\n");
            $stmt->execute([$application_id]);
            $application = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $application) {
                \gc_finish('Application not found.');
            }
            $backUrl = \url('').'/admin/applications.php?job_id='.(int) $application['job_id'];
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $interview_date = trim(\gc_context()->post['interview_date'] ?? '');
                $interview_time = trim(\gc_context()->post['interview_time'] ?? '');
                $location = trim(\gc_context()->post['location'] ?? '');
                $message = trim(\gc_context()->post['message'] ?? '');
                if ($interview_date === '' || $interview_time === '' || $location === '') {
                    $error = 'Please complete interview date, time, and location.';
                } else {
                    try {
                        $check = $pdo->prepare('SELECT id FROM interviews WHERE application_id = ? LIMIT 1');
                        $check->execute([$application_id]);
                        $existing = $check->fetch(\PDO::FETCH_ASSOC);
                        if ($existing) {
                            $update = $pdo->prepare("\r\n                    UPDATE interviews\r\n                    SET \r\n                        admin_id = ?,\r\n                        interview_date = ?, \r\n                        interview_time = ?, \r\n                        location = ?, \r\n                        message = ?, \r\n                        status = 'scheduled',\r\n                        email_sent = 0\r\n                    WHERE application_id = ?\r\n                ");
                            $update->execute([$admin_id, $interview_date, $interview_time, $location, $message, $application_id]);
                        } else {
                            $insert = $pdo->prepare("\r\n                    INSERT INTO interviews \r\n                    (application_id, employer_id, admin_id, alumni_id, job_id, interview_date, interview_time, location, message, status)\r\n                    VALUES (?, 0, ?, ?, ?, ?, ?, ?, ?, 'scheduled')\r\n                ");
                            $insert->execute([$application_id, $admin_id, $application['alumni_id'], $application['job_id'], $interview_date, $interview_time, $location, $message]);
                        }
                        $updateStatus = $pdo->prepare("UPDATE applications SET status = 'interview' WHERE id = ?");
                        $updateStatus->execute([$application_id]);
                        $mailResult = \gc_admin_interview_sendInterviewEmail($application, $interview_date, $interview_time, $location, $message);
                        if ($mailResult['success']) {
                            $pdo->prepare('UPDATE interviews SET email_sent = 1 WHERE application_id = ?')->execute([$application_id]);
                            $success = 'Interview schedule saved and email sent successfully.';
                        } else {
                            $success = 'Interview schedule saved, but email was not sent.';
                            $error = 'Mailer error: '.$mailResult['message'];
                        }
                    } catch (\PDOException $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Database error: '.\gc_public_error($e);
                    }
                }
            }
            $stmt = $pdo->prepare('SELECT * FROM interviews WHERE application_id = ? LIMIT 1');
            $stmt->execute([$application_id]);
            $interview = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $this->pageView('pages.admin.interview', get_defined_vars());
        });
    }
}
