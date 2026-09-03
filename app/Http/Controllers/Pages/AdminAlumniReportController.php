<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminAlumniReportController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $courseFilter = trim(\gc_context()->query['course'] ?? '');
            $batchFilter = trim(\gc_context()->query['batch_year'] ?? '');
            $where = "WHERE role='alumni'";
            $params = [];
            if ($courseFilter !== '') {
                $where .= ' AND course = ?';
                $params[] = $courseFilter;
            }
            if ($batchFilter !== '') {
                $where .= ' AND batch_year = ?';
                $params[] = $batchFilter;
            }
            $stmt = $pdo->prepare("\r\n    SELECT id, fullname, username, email, course, batch_year, created_at\r\n    FROM users\r\n    {$where}\r\n    ORDER BY fullname ASC\r\n");
            $stmt->execute($params);
            $alumni = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $courses = $pdo->query("\r\n    SELECT DISTINCT course\r\n    FROM users\r\n    WHERE role='alumni' AND course IS NOT NULL AND course <> ''\r\n    ORDER BY course ASC\r\n")->fetchAll(\PDO::FETCH_COLUMN);
            $batches = $pdo->query("\r\n    SELECT DISTINCT batch_year\r\n    FROM users\r\n    WHERE role='alumni' AND batch_year IS NOT NULL AND batch_year <> ''\r\n    ORDER BY batch_year DESC\r\n")->fetchAll(\PDO::FETCH_COLUMN);
            $totalAlumni = count($alumni);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_report', get_defined_vars());
        });
    }
}
