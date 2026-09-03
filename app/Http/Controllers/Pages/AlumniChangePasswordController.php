<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AlumniChangePasswordController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $id = (int) \gc_context()->session['user']['id'];
            $msg = '';
            $error = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $old = trim(\gc_context()->post['old_password'] ?? '');
                $new = trim(\gc_context()->post['new_password'] ?? '');
                $confirm = trim(\gc_context()->post['confirm_password'] ?? '');
                if ($old === '' || $new === '' || $confirm === '') {
                    $error = 'All fields are required.';
                } elseif ($new !== $confirm) {
                    $error = 'New password and confirm password do not match.';
                } elseif (strlen($new) < 8) {
                    $error = 'New password must be at least 8 characters.';
                } else {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=? AND role='alumni' LIMIT 1");
                    $stmt->execute([$id]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if (! $row) {
                        $error = 'User not found.';
                    } elseif (! Hash::check($old, $row['password'])) {
                        $error = 'Old password is incorrect.';
                    } else {
                        $update = $pdo->prepare("UPDATE users SET password=? WHERE id=? AND role='alumni'");
                        $update->execute([$new, $id]);
                        $msg = 'Password changed successfully!';
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.change_password', get_defined_vars());
        });
    }
}
