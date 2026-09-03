<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminGraduatesReportController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            \gc_require_role('admin');
            $report_type = $request->query('report_type', 'batch');
            if (! in_array($report_type, ['batch', 'department'], true)) {
                $report_type = 'batch';
            }
            $base = DB::table('users')->where('role', 'alumni')->where('is_active', true);
            $totalGraduates = (clone $base)->count();
            $batchReport = (clone $base)->whereNotNull('batch_year')->where('batch_year', '<>', '')
                ->select('batch_year as label', DB::raw('COUNT(*) as total'))->groupBy('batch_year')
                ->orderByDesc('batch_year')->get()->map(fn ($row) => (array) $row)->all();
            $departmentReport = (clone $base)->whereNotNull('course')->where('course', '<>', '')
                ->select('course as label', DB::raw('COUNT(*) as total'))->groupBy('course')
                ->orderByDesc('total')->orderBy('course')->get()->map(fn ($row) => (array) $row)->all();
            $reportData = $report_type === 'batch' ? $batchReport : $departmentReport;
            $reportTitle = $report_type === 'batch' ? 'Graduate Statistics Report per Batch' : 'Graduate Statistics Report per Department';
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.graduates_report', get_defined_vars());
        });
    }
}
