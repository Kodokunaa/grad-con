<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniAddDegreeController extends PageController
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
            $msg = '';
            $error = '';
            $degree_options = ['Primary', 'Secondary', 'Tertiary', 'Masteral', 'Doctorate'];
            // Add educational background
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['add_education'])) {
                $school_name = trim(\gc_context()->post['school_name'] ?? '');
                $degree = trim(\gc_context()->post['degree'] ?? '');
                $start_year = trim(\gc_context()->post['start_year'] ?? '');
                $end_year = trim(\gc_context()->post['end_year'] ?? '');
                if ($school_name === '') {
                    $error = 'School name is required.';
                } elseif ($degree === '') {
                    $error = 'Degree / level is required.';
                } elseif (! in_array($degree, $degree_options, true)) {
                    $error = 'Please select a valid degree / level.';
                } elseif ($start_year !== '' && ! preg_match('/^\d{4}$/', $start_year)) {
                    $error = 'Start year must be a valid 4-digit year.';
                } elseif ($end_year !== '' && ! preg_match('/^\d{4}$/', $end_year)) {
                    $error = 'End year must be a valid 4-digit year.';
                } elseif ($start_year !== '' && $end_year !== '' && (int) $end_year < (int) $start_year) {
                    $error = 'End year cannot be earlier than start year.';
                } else {
                    try {
                        $ins = $pdo->prepare("\r\n                INSERT INTO alumni_education (user_id, school_name, degree, start_year, end_year)\r\n                VALUES (?, ?, ?, ?, ?)\r\n            ");
                        $ins->execute([$id, $school_name, $degree, $start_year !== '' ? (int) $start_year : null, $end_year !== '' ? (int) $end_year : null]);
                        \gc_alumni_add_degree_add_log($pdo, $id, 'EDUCATION_ADDED', 'Added educational background: '.$school_name);
                        $msg = 'Educational background added successfully!';
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Unable to save educational background. Please make sure the alumni_education table exists.';
                    }
                }
            }
            // Load educational background
            $education_list = [];
            try {
                $eduStmt = $pdo->prepare("\r\n        SELECT id, school_name, degree, start_year, end_year, created_at\r\n        FROM alumni_education\r\n        WHERE user_id=?\r\n        ORDER BY id DESC\r\n    ");
                $eduStmt->execute([$id]);
                $education_list = $eduStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Educational background table not found. Please run the SQL first.';
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.add_degree', get_defined_vars());
        });
    }
}
