<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminCreateEmployerController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();
            if (session_status() === PHP_SESSION_NONE) {
                \gc_noop();
            }

            if (! isset(\gc_context()->session['user']) || \gc_context()->session['user']['role'] !== 'admin') {
                \gc_header('Location: '.\url('').'/index.php');
                \gc_finish();
            }
            $success = '';
            $error = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $fullname = trim(\gc_context()->post['fullname'] ?? '');
                $company = trim(\gc_context()->post['company'] ?? '');
                $email = trim(\gc_context()->post['email'] ?? '');
                $username = trim(\gc_context()->post['username'] ?? '');
                $password = trim(\gc_context()->post['password'] ?? '');
                if ($fullname === '' || $company === '' || $email === '' || $username === '' || $password === '') {
                    $error = 'Please fill in all fields.';
                } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address.';
                } else {
                    $check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                    $check->execute([$username, $email]);
                    if ($check->fetch()) {
                        $error = 'Username or email already exists.';
                    } else {
                        try {
                            // Try insert with employer_company column
                            $stmt = $pdo->prepare("\r\n                    INSERT INTO users (fullname, username, email, password, role, is_active, employer_company)\r\n                    VALUES (?, ?, ?, ?, 'employer', 1, ?)\r\n                ");
                            if ($stmt->execute([$fullname, $username, $email, $password, $company])) {
                                $success = 'Employer account created successfully.';
                            } else {
                                $error = 'Failed to create employer account.';
                            }
                        } catch (\PDOException $e) {
                            if ($e instanceof PageResponse) {
                                throw $e;
                            }
                            try {
                                // Fallback if employer_company column does not exist
                                $stmt2 = $pdo->prepare("\r\n                        INSERT INTO users (fullname, username, email, password, role, is_active)\r\n                        VALUES (?, ?, ?, ?, 'employer', 1)\r\n                    ");
                                if ($stmt2->execute([$fullname, $username, $email, $password])) {
                                    $success = 'Employer account created successfully. Company name was not saved because employer_company column does not exist.';
                                } else {
                                    $error = 'Failed to create employer account.';
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

            return $this->pageView('pages.admin.create_employer', get_defined_vars());
        });
    }
}
