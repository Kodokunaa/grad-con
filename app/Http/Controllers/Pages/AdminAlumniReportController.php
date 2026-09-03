<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminAlumniReportController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            \gc_require_role('admin');
            $courseFilter = trim((string) $request->query('course', ''));
            $batchFilter = trim((string) $request->query('batch_year', ''));
            $query = DB::table('users')->where('role', 'alumni');
            if ($courseFilter !== '') {
                $query->where('course', $courseFilter);
            }
            if ($batchFilter !== '') {
                $query->where('batch_year', $batchFilter);
            }
            $alumni = $query->select('id', 'fullname', 'username', 'email', 'course', 'batch_year', 'created_at')
                ->orderBy('fullname')->get()->map(fn ($row) => (array) $row)->all();
            $courses = DB::table('users')->where('role', 'alumni')->whereNotNull('course')->where('course', '<>', '')
                ->distinct()->orderBy('course')->pluck('course')->all();
            $batches = DB::table('users')->where('role', 'alumni')->whereNotNull('batch_year')->where('batch_year', '<>', '')
                ->distinct()->orderByDesc('batch_year')->pluck('batch_year')->all();
            $totalAlumni = count($alumni);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_report', get_defined_vars());
        });
    }
}
