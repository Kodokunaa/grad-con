<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminGraduatesReportController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $report_type = \gc_context()->query['report_type'] ?? 'batch';
            if (! in_array($report_type, ['batch', 'department'], true)) {
                $report_type = 'batch';
            }
            $totalGraduatesStmt = $pdo->query("\r\n    SELECT COUNT(*)\r\n    FROM users\r\n    WHERE role = 'alumni' AND is_active = 1\r\n");
            $totalGraduates = (int) $totalGraduatesStmt->fetchColumn();
            $batchReport = $pdo->query("\r\n    SELECT batch_year AS label, COUNT(*) AS total\r\n    FROM users\r\n    WHERE role = 'alumni'\r\n      AND is_active = 1\r\n      AND batch_year IS NOT NULL\r\n      AND batch_year <> ''\r\n    GROUP BY batch_year\r\n    ORDER BY batch_year DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            $departmentReport = $pdo->query("\r\n    SELECT course AS label, COUNT(*) AS total\r\n    FROM users\r\n    WHERE role = 'alumni'\r\n      AND is_active = 1\r\n      AND course IS NOT NULL\r\n      AND course <> ''\r\n    GROUP BY course\r\n    ORDER BY total DESC, course ASC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            $reportData = $report_type === 'batch' ? $batchReport : $departmentReport;
            $reportTitle = $report_type === 'batch' ? 'Graduate Statistics Report per Batch' : 'Graduate Statistics Report per Department';
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.graduates_report', get_defined_vars());
        });
    }
}
