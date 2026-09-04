<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminGraduatesStatsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $view = $request->query('view', 'batch');
            if (! in_array($view, ['batch', 'department'], true)) {
                $view = 'batch';
            }
            $base = DB::table('users')->where('role', 'alumni')->where('is_active', true);
            $batches = (clone $base)->whereNotNull('batch_year')->where('batch_year', '<>', '')
                ->select('batch_year', DB::raw('COUNT(*) as total'))->groupBy('batch_year')
                ->orderByDesc('batch_year')->get()->map(fn ($row) => (array) $row)->all();
            $departments = (clone $base)->whereNotNull('course')->where('course', '<>', '')
                ->select('course', DB::raw('COUNT(*) as total'))->groupBy('course')
                ->orderByDesc('total')->orderBy('course')->get()->map(fn ($row) => (array) $row)->all();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.graduates_stats', get_defined_vars());
        });
    }
}
