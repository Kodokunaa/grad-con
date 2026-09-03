<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminGraduatesListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $batch_year = trim(\gc_context()->query['batch_year'] ?? '');
            $course = trim(\gc_context()->query['course'] ?? '');
            $where = "WHERE role='alumni' AND is_active=1";
            $params = [];
            $title = 'Graduates List';
            if ($batch_year !== '') {
                $where .= ' AND batch_year = ?';
                $params[] = $batch_year;
                $title = 'Graduates - Batch '.$batch_year;
            }
            if ($course !== '') {
                $where .= ' AND course = ?';
                $params[] = $course;
                $title = 'Graduates - '.$course;
            }
            $stmt = $pdo->prepare("\r\n  SELECT id, fullname, username, email, course, batch_year, created_at\r\n  FROM users\r\n  {$where}\r\n  ORDER BY fullname ASC\r\n");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.graduates_list', get_defined_vars());
        });
    }
}
