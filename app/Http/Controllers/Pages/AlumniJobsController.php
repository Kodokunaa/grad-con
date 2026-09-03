<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniJobsController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $alumni_id = (int) \gc_context()->session['user']['id'];
            $userStmt = $pdo->prepare("SELECT fullname, course FROM users WHERE id = ? AND role = 'alumni' LIMIT 1");
            $userStmt->execute([$alumni_id]);
            $alumni = $userStmt->fetch(\PDO::FETCH_ASSOC);
            $alumniCourse = trim($alumni['course'] ?? '');
            $search = trim(\gc_context()->query['search'] ?? '');
            $sql = "\r\n    SELECT *\r\n    FROM jobs\r\n    WHERE is_open = 1\r\n      AND (start_date IS NULL OR start_date <= CURDATE())\r\n      AND (end_date IS NULL OR end_date >= CURDATE())\r\n";
            $params = [];
            if ($search !== '') {
                $sql .= "\r\n        AND (\r\n            title LIKE ?\r\n            OR company LIKE ?\r\n            OR location LIKE ?\r\n            OR job_type LIKE ?\r\n            OR description LIKE ?\r\n        )\r\n    ";
                $keyword = "%{$search}%";
                $params = [$keyword, $keyword, $keyword, $keyword, $keyword];
            }
            $sql .= ' ORDER BY id DESC';
            $jobsStmt = $pdo->prepare($sql);
            $jobsStmt->execute($params);
            $jobs = $jobsStmt->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.jobs', get_defined_vars());
        });
    }
}
