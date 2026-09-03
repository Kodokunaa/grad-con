<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $totalJobs = 0;
            $totalEmployers = 0;
            $employedCount = 0;
            $unemployedCount = 0;
            $alignedCount = 0;
            $notAlignedCount = 0;
            $totalAlumni = 0;
            $employmentRate = 0;
            $alignmentRate = 0;
            try {
                $totalJobs = (int) $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn();
                $totalEmployers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer' AND is_active = 1")->fetchColumn();
                $totalAlumni = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND is_active = 1")->fetchColumn();
                $employmentStats = $pdo->query("SELECT employment_status, COUNT(*) AS total FROM users WHERE role = 'alumni' AND is_active = 1 GROUP BY employment_status")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($employmentStats as $row) {
                    $status = strtolower(trim((string) ($row['employment_status'] ?? '')));
                    if ($status === 'employed') {
                        $employedCount = (int) $row['total'];
                    } elseif ($status === 'unemployed') {
                        $unemployedCount = (int) $row['total'];
                    }
                }
                $alignmentStats = $pdo->query("SELECT job_aligned, COUNT(*) AS total FROM users WHERE role = 'alumni' AND is_active = 1 AND employment_status = 'Employed' GROUP BY job_aligned")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($alignmentStats as $row) {
                    $aligned = strtolower(trim((string) ($row['job_aligned'] ?? '')));
                    if ($aligned === 'yes') {
                        $alignedCount = (int) $row['total'];
                    } elseif ($aligned === 'no') {
                        $notAlignedCount = (int) $row['total'];
                    }
                }
                $employmentRate = $totalAlumni > 0 ? round($employedCount / $totalAlumni * 100, 1) : 0;
                $alignmentRate = $employedCount > 0 ? round($alignedCount / $employedCount * 100, 1) : 0;
            } catch (\Throwable $ex) {
                if ($ex instanceof PageResponse) {
                    throw $ex;
                }
                $totalJobs = 0;
                $totalEmployers = 0;
                $employedCount = 0;
                $unemployedCount = 0;
                $alignedCount = 0;
                $notAlignedCount = 0;
                $totalAlumni = 0;
                $employmentRate = 0;
                $alignmentRate = 0;
            }
            $employmentLabels = ['Employed', 'Unemployed'];
            $employmentTotals = [$employedCount, $unemployedCount];
            $alignmentLabels = ['Aligned', 'Not Aligned'];
            $alignmentTotals = [$alignedCount, $notAlignedCount];
            $adminName = \gc_context()->session['user']['fullname'] ?? 'System Admin';
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.dashboard', get_defined_vars());
        });
    }
}
