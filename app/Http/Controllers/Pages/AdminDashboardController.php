<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

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
            $activeAlumni = User::query()->where('role', 'alumni')->where('is_active', true);
            $totalJobs = Job::query()->count();
            $totalEmployers = User::query()->where('role', 'employer')->where('is_active', true)->count();
            $totalAlumni = (clone $activeAlumni)->count();
            $employedCount = (clone $activeAlumni)->whereRaw('LOWER(TRIM(employment_status)) = ?', ['employed'])->count();
            $unemployedCount = (clone $activeAlumni)->whereRaw('LOWER(TRIM(employment_status)) = ?', ['unemployed'])->count();
            $alignedCount = (clone $activeAlumni)->whereRaw('LOWER(TRIM(employment_status)) = ?', ['employed'])->whereRaw('LOWER(TRIM(job_aligned)) = ?', ['yes'])->count();
            $notAlignedCount = (clone $activeAlumni)->whereRaw('LOWER(TRIM(employment_status)) = ?', ['employed'])->whereRaw('LOWER(TRIM(job_aligned)) = ?', ['no'])->count();
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
