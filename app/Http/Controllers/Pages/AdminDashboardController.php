<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class AdminDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $totalJobs = 0;
            $totalEmployers = 0;
            $employedCount = 0;
            $unemployedCount = 0;
            $alignedCount = 0;
            $notAlignedCount = 0;
            $totalAlumni = 0;
            $employmentRate = 0;
            $alignmentRate = 0;
            $metrics = Cache::remember('dashboard.admin.metrics.v1', config('performance.dashboard_cache_seconds'), function () {
                $alumni = User::query()->where('role', 'alumni')->where('is_active', true)->selectRaw("COUNT(*) total, SUM(CASE WHEN LOWER(TRIM(employment_status)) = 'employed' THEN 1 ELSE 0 END) employed, SUM(CASE WHEN LOWER(TRIM(employment_status)) = 'unemployed' THEN 1 ELSE 0 END) unemployed, SUM(CASE WHEN LOWER(TRIM(employment_status)) = 'employed' AND LOWER(TRIM(job_aligned)) = 'yes' THEN 1 ELSE 0 END) aligned, SUM(CASE WHEN LOWER(TRIM(employment_status)) = 'employed' AND LOWER(TRIM(job_aligned)) = 'no' THEN 1 ELSE 0 END) not_aligned")->first();

                return [
                    'jobs' => Job::query()->count(),
                    'employers' => User::query()->where('role', 'employer')->where('is_active', true)->count(),
                    'alumni' => (int) $alumni->total,
                    'employed' => (int) $alumni->employed,
                    'unemployed' => (int) $alumni->unemployed,
                    'aligned' => (int) $alumni->aligned,
                    'not_aligned' => (int) $alumni->not_aligned,
                ];
            });
            $totalJobs = $metrics['jobs'];
            $totalEmployers = $metrics['employers'];
            $totalAlumni = $metrics['alumni'];
            $employedCount = $metrics['employed'];
            $unemployedCount = $metrics['unemployed'];
            $alignedCount = $metrics['aligned'];
            $notAlignedCount = $metrics['not_aligned'];
            $employmentRate = $totalAlumni > 0 ? round($employedCount / $totalAlumni * 100, 1) : 0;
            $alignmentRate = $employedCount > 0 ? round($alignedCount / $employedCount * 100, 1) : 0;
            $employmentLabels = ['Employed', 'Unemployed'];
            $employmentTotals = [$employedCount, $unemployedCount];
            $alignmentLabels = ['Aligned', 'Not Aligned'];
            $alignmentTotals = [$alignedCount, $notAlignedCount];
            $adminName = request()->user()?->fullname ?? 'System Admin';
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.dashboard', get_defined_vars());
        });
    }
}
