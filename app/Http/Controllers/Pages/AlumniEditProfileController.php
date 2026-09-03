<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniEditProfileController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $id = (int) \gc_context()->session['user']['id'];
            $stmt = $pdo->prepare("\r\n    SELECT fullname, email, course, batch_year, employment_status, job_aligned\r\n    FROM users\r\n    WHERE id=? AND role='alumni'\r\n    LIMIT 1\r\n");
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $user) {
                \gc_finish('User not found.');
            }
            $msg = '';
            $error = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $fullname = trim(\gc_context()->post['fullname'] ?? '');
                $email = trim(\gc_context()->post['email'] ?? '');
                $course = trim(\gc_context()->post['course'] ?? '');
                $batch_year = trim(\gc_context()->post['batch_year'] ?? '');
                $employment_status = trim(\gc_context()->post['employment_status'] ?? '');
                $job_aligned = trim(\gc_context()->post['job_aligned'] ?? '');
                if ($fullname === '') {
                    $error = 'Fullname is required.';
                } elseif ($employment_status !== '' && ! in_array($employment_status, ['Employed', 'Unemployed'])) {
                    $error = 'Invalid employment status.';
                } else {
                    // If unemployed, clear job alignment
                    if ($employment_status === 'Unemployed') {
                        $job_aligned = null;
                    }
                    // If employed, job alignment must be Yes or No
                    if ($employment_status === 'Employed') {
                        if (! in_array($job_aligned, ['Yes', 'No'])) {
                            $error = 'Please select if your job is aligned to your degree.';
                        }
                    } else {
                        $job_aligned = null;
                    }
                }
                if ($error === '') {
                    $update = $pdo->prepare("\r\n            UPDATE users\r\n            SET fullname=?, email=?, course=?, batch_year=?, employment_status=?, job_aligned=?\r\n            WHERE id=? AND role='alumni'\r\n        ");
                    $update->execute([$fullname, $email, $course, $batch_year, $employment_status ?: null, $job_aligned, $id]);
                    \gc_context()->session['user']['fullname'] = $fullname;
                    $msg = 'Profile updated successfully.';
                    $stmt->execute([$id]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('navbar', \get_defined_vars());

            return $this->pageView('pages.alumni.edit_profile', get_defined_vars());
        });
    }
}
