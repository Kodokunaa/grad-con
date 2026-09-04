<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AlumniJobsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $alumni_id = (int) $request->user()->id;
            $alumni = (array) DB::table('users')->select('fullname', 'course')->where('id', $alumni_id)->where('role', 'alumni')->first();
            $alumniCourse = trim($alumni['course'] ?? '');
            $search = trim((string) $request->query('search', ''));
            $query = DB::table('jobs')->where('is_open', true)
                ->where(fn ($query) => $query->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
                ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()));
            if ($search !== '') {
                $query->where(function ($query) use ($search) {
                    foreach (['title', 'company', 'location', 'job_type', 'description'] as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            }
            $jobs = $query->orderByDesc('id')->get()->map(fn ($row) => (array) $row)->all();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.jobs', get_defined_vars());
        });
    }
}
