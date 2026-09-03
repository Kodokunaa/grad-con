<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminJobsListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            // Jobs with poster + assigned employer + poster role
            // Automatically hide expired jobs - only show jobs that haven't expired yet or have no end date
            $jobs = $pdo->query("\r\n  SELECT j.*,\r\n         u.fullname AS poster,\r\n         u.role AS poster_role,\r\n         e.fullname AS employer_name\r\n  FROM jobs j\r\n  JOIN users u ON u.id = j.posted_by\r\n  LEFT JOIN users e ON e.id = j.employer_id\r\n  WHERE j.end_date IS NULL OR j.end_date >= CURDATE()\r\n  ORDER BY j.id DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.jobs_list', get_defined_vars());
        });
    }
}
