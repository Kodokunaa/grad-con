<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class AdminReportsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $selectedMonth = trim((string) (request()->query('month') ?? date('Y-m')));
            if (! preg_match('/^\d{4}-\d{2}$/', $selectedMonth) || ! strtotime($selectedMonth.'-01')) {
                $selectedMonth = date('Y-m');
            }
            $report = ['vacancies' => 0, 'employer_jobs' => 0, 'admin_jobs' => 0, 'enrolled_alumni' => 0, 'applicants' => 0, 'using_alumni' => 0, 'monthly_active_users' => 0, 'monthly_employers' => 0, 'hired_alumni' => 0];
            $error = '';
            $monthStart = $selectedMonth.'-01';
            $monthEnd = date('Y-m-d', strtotime('+1 month', strtotime($monthStart)));
            $report = Cache::remember('reports.admin.'.$selectedMonth.'.v1', config('performance.report_cache_seconds'), function () use ($monthStart, $monthEnd) {
                return [
                    'vacancies' => Job::query()->count(),
                    'employer_jobs' => Job::query()->whereHas('poster', fn ($query) => $query->where('role', 'employer'))->count(),
                    'admin_jobs' => Job::query()->whereHas('poster', fn ($query) => $query->where('role', 'admin'))->count(),
                    'enrolled_alumni' => User::query()->where('role', 'alumni')->where('is_active', true)->count(),
                    'applicants' => JobApplication::query()->count(),
                    'using_alumni' => JobApplication::query()->distinct('alumni_id')->count('alumni_id'),
                    'hired_alumni' => JobApplication::query()->whereRaw('LOWER(TRIM(status)) = ?', ['hired'])->distinct('alumni_id')->count('alumni_id'),
                    'monthly_active_users' => User::query()->whereBetween('created_at', [$monthStart, $monthEnd])->where('created_at', '<', $monthEnd)->count(),
                    'monthly_employers' => Job::query()->whereHas('poster', fn ($query) => $query->where('role', 'employer'))->where('created_at', '>=', $monthStart)->where('created_at', '<', $monthEnd)->distinct('posted_by')->count('posted_by'),
                ];
            });
            $monthLabel = date('F Y', strtotime($selectedMonth.'-01'));
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.reports', get_defined_vars());
        });
    }
}
