<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminJobsListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            // Jobs with poster + assigned employer + poster role
            // Automatically hide expired jobs - only show jobs that haven't expired yet or have no end date
            $jobs = DB::table('jobs as j')
                ->join('users as u', 'u.id', '=', 'j.posted_by')
                ->leftJoin('users as e', 'e.id', '=', 'j.employer_id')
                ->where(fn ($query) => $query->whereNull('j.end_date')->orWhereDate('j.end_date', '>=', today()))
                ->select('j.*', 'u.fullname as poster', 'u.role as poster_role', 'e.fullname as employer_name')
                ->orderByDesc('j.id')->get()->map(fn ($row) => (array) $row)->all();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.jobs_list', get_defined_vars());
        });
    }
}
