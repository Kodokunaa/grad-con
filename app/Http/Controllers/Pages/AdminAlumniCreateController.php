<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminAlumniCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $msg = '';
            $error = '';
            /* COURSE OPTIONS */
            $course_options = ['BSIS', 'BSTM', 'BSHM', 'BSED Science', 'BSED Math', 'BSNED', 'BPA'];
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $fullname = trim(\gc_context()->post['fullname'] ?? '');
                $student_id = trim(\gc_context()->post['student_id'] ?? '');
                $password = trim(\gc_context()->post['password'] ?? '');
                $email = trim(\gc_context()->post['email'] ?? '');
                $course = trim(\gc_context()->post['course'] ?? '');
                $batch_year = trim(\gc_context()->post['batch_year'] ?? '');
                if ($fullname === '' || $student_id === '' || $password === '') {
                    $error = 'Fullname, Student ID, and password are required.';
                } else {
                    $check = $pdo->prepare('SELECT id FROM users WHERE username=? LIMIT 1');
                    $check->execute([$student_id]);
                    if ($check->fetch()) {
                        $error = 'Student ID already exists.';
                    } else {
                        $stmt = $pdo->prepare("\r\n                INSERT INTO users(fullname, username, password, role, email, course, batch_year)\r\n                VALUES(?,?,?,'alumni',?,?,?)\r\n            ");
                        $stmt->execute([$fullname, $student_id, $password, $email, $course, $batch_year]);
                        $msg = 'Alumni account created!';
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_create', get_defined_vars());
        });
    }
}
