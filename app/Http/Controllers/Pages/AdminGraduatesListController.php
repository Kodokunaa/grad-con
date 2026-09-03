<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminGraduatesListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            \gc_require_role('admin');
            $batch_year = trim((string) $request->query('batch_year', ''));
            $course = trim((string) $request->query('course', ''));
            $query = DB::table('users')->where('role', 'alumni')->where('is_active', true);
            $title = 'Graduates List';
            if ($batch_year !== '') {
                $query->where('batch_year', $batch_year);
                $title = 'Graduates - Batch '.$batch_year;
            }
            if ($course !== '') {
                $query->where('course', $course);
                $title = 'Graduates - '.$course;
            }
            $list = $query->select('id', 'fullname', 'username', 'email', 'course', 'batch_year', 'created_at')
                ->orderBy('fullname')->get()->map(fn ($row) => (array) $row)->all();
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.graduates_list', get_defined_vars());
        });
    }
}
