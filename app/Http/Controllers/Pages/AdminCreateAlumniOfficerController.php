<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminCreateAlumniOfficerController extends PageController
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
            $success = '';
            $error = '';
            $sidebar_file = null;
            $possible_sidebars = [\storage_path('app/private/files/admin').'/sidebar.php', \storage_path('app/private/files/admin').'/admin_sidebar.php', \storage_path('app/private/files/admin').'/includes/admin_sidebar.php', \storage_path('app/private/files/admin').'/../includes/admin_sidebar.php'];
            foreach ($possible_sidebars as $file) {
                if (file_exists($file)) {
                    $sidebar_file = $file;
                    break;
                }
            }
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $fullname = trim(\gc_context()->post['fullname'] ?? '');
                $email = trim(\gc_context()->post['email'] ?? '');
                $username = trim(\gc_context()->post['username'] ?? '');
                $password = trim(\gc_context()->post['password'] ?? '');
                $confirm_password = trim(\gc_context()->post['confirm_password'] ?? '');
                $is_active = isset(\gc_context()->post['is_active']) ? 1 : 0;
                if ($fullname === '' || $email === '' || $username === '' || $password === '' || $confirm_password === '') {
                    $error = 'Please fill in all fields.';
                } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address.';
                } elseif (strlen($username) < 3) {
                    $error = 'Username must be at least 3 characters long.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                } elseif ($password !== $confirm_password) {
                    $error = 'Password and confirm password do not match.';
                } else {
                    $check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                    $check->execute([$username, $email]);
                    if ($check->fetch()) {
                        $error = 'Username or email already exists.';
                    } else {
                        try {
                            // Primary insert with created_at
                            $stmt = $pdo->prepare("\r\n                    INSERT INTO users (fullname, username, email, password, role, is_active, created_at)\r\n                    VALUES (?, ?, ?, ?, ?, ?, NOW())\r\n                ");
                            if ($stmt->execute([$fullname, $username, $email, $password, 'alumni_officer', $is_active])) {
                                $success = 'Alumni Officer account created successfully.';
                            } else {
                                $error = 'Failed to create Alumni Officer account.';
                            }
                        } catch (\PDOException $e) {
                            if ($e instanceof PageResponse) {
                                throw $e;
                            }
                            try {
                                // Fallback if created_at column does not exist
                                $stmt2 = $pdo->prepare("\r\n                        INSERT INTO users (fullname, username, email, password, role, is_active)\r\n                        VALUES (?, ?, ?, ?, ?, ?)\r\n                    ");
                                if ($stmt2->execute([$fullname, $username, $email, $password, 'alumni_officer', $is_active])) {
                                    $success = 'Alumni Officer account created successfully.';
                                } else {
                                    $error = 'Failed to create Alumni Officer account.';
                                }
                            } catch (\PDOException $e2) {
                                if ($e2 instanceof PageResponse) {
                                    throw $e2;
                                }
                                $error = 'Database error: '.\gc_public_error($e2);
                            }
                        }
                    }
                }
            }

            return $this->pageView('pages.admin.create_alumni_officer', get_defined_vars());
        });
    }
}
