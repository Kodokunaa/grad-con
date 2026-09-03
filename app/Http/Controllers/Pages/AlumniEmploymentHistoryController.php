<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AlumniEmploymentHistoryController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role();
            $id = (int) (\gc_context()->session['user']['id'] ?? 0);
            $role = \gc_context()->session['user']['role'] ?? '';
            if ($role !== 'alumni') {
                \gc_finish('Access denied.');
            }
            // Load user
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $user) {
                \gc_finish('User not found.');
            }
            $alumniCourse = \gc_alumni_employment_history_get_alumni_course($user);
            $msg = '';
            $error = '';
            // ========================
            // ADD EMPLOYMENT HISTORY
            // ========================
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['add_employment'])) {
                $company_name = trim(\gc_context()->post['company_name'] ?? '');
                $job_title = trim(\gc_context()->post['job_title'] ?? '');
                $employment_type = trim(\gc_context()->post['employment_type'] ?? '');
                $location = trim(\gc_context()->post['location'] ?? '');
                $start_date = trim(\gc_context()->post['start_date'] ?? '');
                $end_date = trim(\gc_context()->post['end_date'] ?? '');
                $job_description = trim(\gc_context()->post['job_description'] ?? '');
                if ($company_name === '' || $job_title === '' || $start_date === '') {
                    $error = 'Company name, job title, and start date are required.';
                } elseif (strtotime($start_date) === false) {
                    $error = 'Invalid start date.';
                } elseif ($end_date !== '' && strtotime($end_date) === false) {
                    $error = 'Invalid end date.';
                } elseif ($end_date !== '' && strtotime($end_date) < strtotime($start_date)) {
                    $error = 'End date cannot be earlier than start date.';
                } else {
                    try {
                        DB::beginTransaction();
                        /*
                            BEST FLOW:
                            - If the alumni leaves End Date blank, the new job is treated as the current/present job.
                            - Before saving the new current job, close any old current job by setting its end_date
                              to one day before the new job's start date.
                            - This keeps the old job as past employment history and prevents multiple "Present" jobs.
                        */
                        if ($end_date === '') {
                            $previousEndDate = date('Y-m-d', strtotime($start_date.' -1 day'));
                            $closeOldPresentJobs = $pdo->prepare("\r\n                    UPDATE employment_history\r\n                    SET end_date = ?\r\n                    WHERE user_id = ? AND end_date IS NULL\r\n                ");
                            $closeOldPresentJobs->execute([$previousEndDate, $id]);
                        }
                        $ins = $pdo->prepare("\r\n                INSERT INTO employment_history\r\n                (user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description)\r\n                VALUES (?, ?, ?, ?, ?, ?, ?, ?)\r\n            ");
                        $ins->execute([$id, $company_name, $job_title, $employment_type !== '' ? $employment_type : null, $location !== '' ? $location : null, $start_date, $end_date !== '' ? $end_date : null, $job_description !== '' ? $job_description : null]);
                        \gc_alumni_employment_history_refresh_employment_status($pdo, $id);
                        \gc_alumni_employment_history_add_log($pdo, $id, 'EMPLOYMENT_HISTORY_ADDED', 'Employment history added');
                        DB::commit();
                        $msg = $end_date === '' ? 'New current job added successfully. Previous present job was moved to past employment history.' : 'Past employment history added successfully.';
                        \gc_context()->post = [];
                        $stmt->execute([$id]);
                        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        if (DB::transactionLevel() > 0) {
                            DB::rollBack();
                        }
                        $error = 'Unable to save employment history. Please run the SQL first.';
                    }
                }
            }
            // ========================
            // DELETE EMPLOYMENT HISTORY
            // ========================
            if (isset(\gc_context()->query['delete'])) {
                $delete_id = (int) (\gc_context()->query['delete'] ?? 0);
                if ($delete_id > 0) {
                    try {
                        $del = $pdo->prepare('DELETE FROM employment_history WHERE id=? AND user_id=?');
                        $del->execute([$delete_id, $id]);
                        \gc_alumni_employment_history_refresh_employment_status($pdo, $id);
                        \gc_alumni_employment_history_add_log($pdo, $id, 'EMPLOYMENT_HISTORY_DELETED', 'Employment history deleted');
                        \gc_header('Location: employment_history.php?deleted=1');
                        \gc_finish();
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Unable to delete employment history.';
                    }
                }
            }
            if (isset(\gc_context()->query['deleted'])) {
                $msg = 'Employment history deleted successfully!';
            }
            // ========================
            // LOAD EMPLOYMENT HISTORY
            // ========================
            $employment_list = [];
            try {
                $employmentStmt = $pdo->prepare("\r\n        SELECT id, company_name, job_title, employment_type, location, start_date, end_date, job_description, created_at\r\n        FROM employment_history\r\n        WHERE user_id=?\r\n        ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC\r\n    ");
                $employmentStmt->execute([$id]);
                $employment_list = $employmentStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $employment_list = [];
                $error = 'Employment history table not found. Please run the SQL first.';
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.employment_history', get_defined_vars());
        });
    }
}
