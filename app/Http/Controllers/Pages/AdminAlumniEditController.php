<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminAlumniEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $id = (int) (\gc_context()->query['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='alumni' LIMIT 1");
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $user) {
                \gc_finish('Alumni not found.');
            }
            $msg = '';
            $error = '';
            // ==========================
            // Delete Account
            // ==========================
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['delete_account'])) {
                try {
                    // Delete related records first (to handle foreign key constraints)
                    $pdo->prepare('DELETE FROM security_logs WHERE user_id=?')->execute([$id]);
                    $pdo->prepare('DELETE FROM applications WHERE alumni_id=?')->execute([$id]);
                    // Then delete the user
                    $del = $pdo->prepare("DELETE FROM users WHERE id=? AND role='alumni' LIMIT 1");
                    $del->execute([$id]);
                    \gc_header('Location: '.\url('').'/admin/alumni_list.php?msg=Alumni deleted successfully');
                    \gc_finish();
                } catch (\Exception $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $error = 'Error deleting alumni: '.\gc_public_error($e);
                }
            }
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $fullname = trim(\gc_context()->post['fullname'] ?? '');
                $email = trim(\gc_context()->post['email'] ?? '');
                $course = trim(\gc_context()->post['course'] ?? '');
                $batch_year = trim(\gc_context()->post['batch_year'] ?? '');
                $is_active = (int) (\gc_context()->post['is_active'] ?? 1);
                $newpass = trim(\gc_context()->post['password'] ?? '');
                if ($fullname === '') {
                    $error = 'Fullname required.';
                } else {
                    if ($newpass !== '') {
                        $up = $pdo->prepare("\r\n                UPDATE users\r\n                SET fullname=?, email=?, course=?, batch_year=?, is_active=?, password=?\r\n                WHERE id=?\r\n            ");
                        $up->execute([$fullname, $email, $course, $batch_year, $is_active, $newpass, $id]);
                    } else {
                        $up = $pdo->prepare("\r\n                UPDATE users\r\n                SET fullname=?, email=?, course=?, batch_year=?, is_active=?\r\n                WHERE id=?\r\n            ");
                        $up->execute([$fullname, $email, $course, $batch_year, $is_active, $id]);
                    }
                    $msg = 'Updated successfully!';
                    $stmt->execute([$id]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_edit', get_defined_vars());
        });
    }
}
