<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminTrainingsListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            // FETCH
            $trainings = $pdo->query("\r\n    SELECT t.*, u.fullname\r\n    FROM trainings t\r\n    LEFT JOIN users u ON u.id = t.posted_by\r\n    ORDER BY t.id DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.trainings_list', get_defined_vars());
        });
    }
}
