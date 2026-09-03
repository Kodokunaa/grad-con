<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerPostedJobController extends PageController
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
            $employer_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            $today = date('Y-m-d');
            $error = '';
            $hasEmailAddress = false;
            $hasStartDate = false;
            $hasEndDate = false;
            try {
                $colStmt = $pdo->query('SHOW COLUMNS FROM jobs');
                $jobColumns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);
                $hasEmailAddress = in_array('email_address', $jobColumns, true);
                $hasStartDate = in_array('start_date', $jobColumns, true);
                $hasEndDate = in_array('end_date', $jobColumns, true);
            } catch (\Throwable $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Unable to read jobs table structure.';
            }
            $posted_jobs = [];
            try {
                $postedJobsFields = ['j.id', 'j.title', 'j.company', 'j.employer_company', $hasEmailAddress ? 'j.email_address' : 'NULL AS email_address', $hasStartDate ? 'j.start_date' : 'NULL AS start_date', $hasEndDate ? 'j.end_date' : 'NULL AS end_date', 'j.location', 'j.job_type', 'j.description', 'j.is_open', 'j.created_at', '(SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS total_applications'];
                $jobsSql = "\r\n        SELECT ".implode(",\n            ", $postedJobsFields)."\r\n        FROM jobs j\r\n        WHERE j.posted_by = ?\r\n        ORDER BY j.id DESC\r\n    ";
                $jobsStmt = $pdo->prepare($jobsSql);
                $jobsStmt->execute([$employer_id]);
                $posted_jobs = $jobsStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Database error loading posted jobs: '.\gc_public_error($e);
            }

            return $this->pageView('pages.employer.posted_job', get_defined_vars());
        });
    }
}
