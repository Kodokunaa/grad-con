<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminReportsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $selectedMonth = trim((string) (\gc_context()->query['month'] ?? date('Y-m')));
            if (! preg_match('/^\d{4}-\d{2}$/', $selectedMonth) || ! strtotime($selectedMonth.'-01')) {
                $selectedMonth = date('Y-m');
            }
            $report = ['vacancies' => 0, 'employer_jobs' => 0, 'admin_jobs' => 0, 'enrolled_alumni' => 0, 'applicants' => 0, 'using_alumni' => 0, 'monthly_active_users' => 0, 'monthly_employers' => 0, 'hired_alumni' => 0];
            $error = '';
            try {
                $report['vacancies'] = (int) $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn();
                $posterBreakdown = $pdo->query('SELECT u.role AS poster_role, COUNT(*) AS total FROM jobs j LEFT JOIN users u ON u.id = j.posted_by GROUP BY u.role')->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($posterBreakdown as $row) {
                    $role = strtolower(trim((string) ($row['poster_role'] ?? '')));
                    if ($role === 'employer') {
                        $report['employer_jobs'] = (int) $row['total'];
                    } elseif ($role === 'admin') {
                        $report['admin_jobs'] = (int) $row['total'];
                    }
                }
                $report['enrolled_alumni'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND COALESCE(is_active, 0) = 1")->fetchColumn();
                $report['applicants'] = (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn();
                $report['using_alumni'] = (int) $pdo->query('SELECT COUNT(DISTINCT alumni_id) FROM applications')->fetchColumn();
                $report['hired_alumni'] = (int) $pdo->query("SELECT COUNT(DISTINCT alumni_id) FROM applications WHERE LOWER(TRIM(status)) = 'hired'")->fetchColumn();
                $monthStart = $selectedMonth.'-01';
                $monthEnd = date('Y-m-d', strtotime('+1 month', strtotime($monthStart)));
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at < ?');
                $stmt->execute([$monthStart, $monthEnd]);
                $report['monthly_active_users'] = (int) $stmt->fetchColumn();
                $monthlyEmployers = $pdo->prepare("\r\n\t\tSELECT COUNT(DISTINCT j.posted_by)\r\n\t\tFROM jobs j\r\n\t\tINNER JOIN users u ON u.id = j.posted_by AND u.role = 'employer'\r\n\t\tWHERE j.created_at >= ? AND j.created_at < DATE_ADD(?, INTERVAL 1 MONTH)\r\n\t");
                $monthlyEmployers->execute([$monthStart, $monthStart]);
                $report['monthly_employers'] = (int) $monthlyEmployers->fetchColumn();
            } catch (\Throwable $ex) {
                if ($ex instanceof PageResponse) {
                    throw $ex;
                }
                $error = 'Some report figures could not be loaded. Please check the database connection and table structure.';
            }
            $monthLabel = date('F Y', strtotime($selectedMonth.'-01'));
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.reports', get_defined_vars());
        });
    }
}
