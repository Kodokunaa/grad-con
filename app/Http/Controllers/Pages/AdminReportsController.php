<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;

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
            $report['vacancies'] = Job::query()->count();
            $report['employer_jobs'] = Job::query()->whereHas('poster', fn ($query) => $query->where('role', 'employer'))->count();
            $report['admin_jobs'] = Job::query()->whereHas('poster', fn ($query) => $query->where('role', 'admin'))->count();
            $report['enrolled_alumni'] = User::query()->where('role', 'alumni')->where('is_active', true)->count();
            $report['applicants'] = JobApplication::query()->count();
            $report['using_alumni'] = JobApplication::query()->distinct('alumni_id')->count('alumni_id');
            $report['hired_alumni'] = JobApplication::query()->whereRaw('LOWER(TRIM(status)) = ?', ['hired'])->distinct('alumni_id')->count('alumni_id');
            $report['monthly_active_users'] = User::query()->whereBetween('created_at', [$monthStart, $monthEnd])->where('created_at', '<', $monthEnd)->count();
            $report['monthly_employers'] = Job::query()->whereHas('poster', fn ($query) => $query->where('role', 'employer'))->where('created_at', '>=', $monthStart)->where('created_at', '<', $monthEnd)->distinct('posted_by')->count('posted_by');
            $monthLabel = date('F Y', strtotime($selectedMonth.'-01'));
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.reports', get_defined_vars());
        });
    }
}
