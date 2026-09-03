<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminGraduatesStatsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $view = \gc_context()->query['view'] ?? 'batch';
            if (! in_array($view, ['batch', 'department'], true)) {
                $view = 'batch';
            }
            $batches = $pdo->query("\r\n  SELECT batch_year, COUNT(*) AS total\r\n  FROM users\r\n  WHERE role='alumni'\r\n    AND is_active=1\r\n    AND batch_year IS NOT NULL\r\n    AND batch_year <> ''\r\n  GROUP BY batch_year\r\n  ORDER BY batch_year DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            $departments = $pdo->query("\r\n  SELECT course, COUNT(*) AS total\r\n  FROM users\r\n  WHERE role='alumni'\r\n    AND is_active=1\r\n    AND course IS NOT NULL\r\n    AND course <> ''\r\n  GROUP BY course\r\n  ORDER BY total DESC, course ASC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.graduates_stats', get_defined_vars());
        });
    }
}
