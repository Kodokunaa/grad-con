<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminJobsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $id = (int) (\gc_context()->query['id'] ?? 0);
            // Handle delete request
            if (isset(\gc_context()->query['delete']) && \gc_context()->query['delete'] === '1') {
                try {
                    $deleteStmt = $pdo->prepare('DELETE FROM jobs WHERE id=?');
                    $deleteStmt->execute([$id]);
                    \gc_header('Location: '.\url('').'/admin/jobs_list.php?deleted=1');
                    \gc_finish();
                } catch (\PDOException $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $error = 'Failed to delete job: '.\gc_public_error($e);
                }
            }
            $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $job) {
                \gc_finish('Job not found.');
            }
            $msg = '';
            $error = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $company = trim(\gc_context()->post['company'] ?? '');
                $location = trim(\gc_context()->post['location'] ?? '');
                $job_type = trim(\gc_context()->post['job_type'] ?? 'Full-time');
                $description = trim(\gc_context()->post['description'] ?? '');
                $requirements = trim(\gc_context()->post['requirements'] ?? '');
                $is_open = (int) (\gc_context()->post['is_open'] ?? 1);
                if ($title === '' || $company === '' || $description === '') {
                    $error = 'Title, company, and description are required.';
                } else {
                    $up = $pdo->prepare("\r\n            UPDATE jobs\r\n            SET title=?, company=?, location=?, job_type=?, description=?, requirements=?, is_open=?\r\n            WHERE id=?\r\n        ");
                    $up->execute([$title, $company, $location, $job_type, $description, $requirements, $is_open, $id]);
                    $msg = 'Job updated successfully.';
                    $stmt->execute([$id]);
                    $job = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.jobs_edit', get_defined_vars());
        });
    }
}
