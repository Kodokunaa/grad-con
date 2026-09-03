<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniJobDetailsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }
            if (! isset(\gc_context()->session['user']) || (\gc_context()->session['user']['role'] ?? '') !== 'alumni') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }
            $job_id = (int) (\gc_context()->query['id'] ?? 0);
            if ($job_id <= 0) {
                \gc_finish('Invalid job ID.');
            }
            $success = '';
            $error = '';
            $alumni_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            // Get job details
            $stmt = $pdo->prepare("\r\n    SELECT *\r\n    FROM jobs\r\n    WHERE id = ? AND is_open = 1\r\n      AND (start_date IS NULL OR start_date <= CURDATE())\r\n      AND (end_date IS NULL OR end_date >= CURDATE())\r\n    LIMIT 1\r\n");
            $stmt->execute([$job_id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $job) {
                \gc_finish('Job not found or no longer open.');
            }
            // Check if already applied
            $alreadyApplied = false;
            try {
                $checkStmt = $pdo->prepare("\r\n        SELECT id\r\n        FROM applications\r\n        WHERE job_id = ? AND alumni_id = ?\r\n        LIMIT 1\r\n    ");
                $checkStmt->execute([$job_id, $alumni_id]);
                $alreadyApplied = (bool) $checkStmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Database error: '.\gc_public_error($e);
            }
            // Always use the complete application flow, which validates the profile,
            // terms agreement, job availability, and resume upload.
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && ! $alreadyApplied) {
                \gc_header('Location: '.\url('').'/alumni/apply.php?job_id='.$job_id);
                \gc_finish();
            }

            return $this->pageView('pages.alumni.job_details', get_defined_vars());
        });
    }
}
