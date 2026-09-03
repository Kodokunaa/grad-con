<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniOfficerAlumniListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            if (true) {
                \gc_require_role('alumni_officer');
            } else {
                \gc_require_role('admin');
            }
            $alumni = $pdo->query("\r\n    SELECT * FROM users\r\n    WHERE role='alumni'\r\n    ORDER BY id DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            $alumniIds = array_map(static fn ($row) => (int) $row['id'], $alumni);
            $educationByUser = [];
            $certificatesByUser = [];
            $employmentByUser = [];
            $degreesByUser = [];
            if (! empty($alumniIds)) {
                $placeholders = implode(',', array_fill(0, count($alumniIds), '?'));
                try {
                    $stmt = $pdo->prepare("SELECT user_id, school_name, degree, start_year, end_year FROM alumni_education WHERE user_id IN ({$placeholders}) ORDER BY COALESCE(end_year, 9999) DESC, COALESCE(start_year, 9999) DESC, id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $educationByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                }
                try {
                    $stmt = $pdo->prepare("SELECT user_id, certificate_name, issue_date, certificate_image FROM alumni_certificates WHERE user_id IN ({$placeholders}) ORDER BY COALESCE(issue_date, '0000-00-00') DESC, id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $certificatesByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                }
                try {
                    $stmt = $pdo->prepare("SELECT user_id, company_name, job_title, employment_type, location, start_date, end_date, job_description FROM employment_history WHERE user_id IN ({$placeholders}) ORDER BY COALESCE(end_date, '9999-12-31') DESC, start_date DESC, id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $employmentByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                }
                try {
                    $stmt = $pdo->prepare("SELECT user_id, degree_name, school_name, year_graduated, diploma_file FROM alumni_degrees WHERE user_id IN ({$placeholders}) ORDER BY id DESC");
                    $stmt->execute($alumniIds);
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $degreesByUser[(int) $row['user_id']][] = $row;
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                }
            }
            $courseOptions = [];
            $batchOptions = [];
            foreach ($alumni as $a) {
                if (! empty($a['course'])) {
                    $courseOptions[] = trim($a['course']);
                }
                if (! empty($a['batch_year'])) {
                    $batchOptions[] = trim($a['batch_year']);
                }
            }
            $courseOptions = array_values(array_unique($courseOptions));
            $batchOptions = array_values(array_unique($batchOptions));
            sort($courseOptions, SORT_NATURAL | SORT_FLAG_CASE);
            sort($batchOptions, SORT_NATURAL | SORT_FLAG_CASE);
            echo \gc_partial('header', \get_defined_vars());
            if (file_exists(\storage_path('app/private/files/alumni_officer').'/../includes/alumni_officer_sidebar.php')) {
                echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());
            } else {
                echo \gc_partial('admin_sidebar', \get_defined_vars());
            }

            return $this->pageView('pages.alumni_officer.alumni_list', get_defined_vars());
        });
    }
}
