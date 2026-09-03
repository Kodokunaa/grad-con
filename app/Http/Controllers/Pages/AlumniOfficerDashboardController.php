<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniOfficerDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni_officer');
            $officer_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            $fullname = \gc_context()->session['user']['fullname'] ?? \gc_context()->session['user']['username'] ?? 'Alumni Officer';
            $totalEvents = 0;
            $activeEvents = 0;
            $scheduledEvents = 0;
            $recentEvents = [];
            $error = '';
            try {
                $totalEvents = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
                $activeEvents = (int) $pdo->query('SELECT COUNT(*) FROM events WHERE (post_start_date IS NULL OR post_start_date <= NOW()) AND (post_end_date IS NULL OR post_end_date >= NOW())')->fetchColumn();
                $scheduledEvents = (int) $pdo->query('SELECT COUNT(*) FROM events WHERE post_start_date IS NOT NULL AND post_start_date > NOW()')->fetchColumn();
                $recentStmt = $pdo->prepare("SELECT e.*, u.fullname AS poster_name\r\n        FROM events e\r\n        LEFT JOIN users u ON u.id = e.posted_by\r\n        ORDER BY e.created_at DESC, e.id DESC\r\n        LIMIT 6");
                $recentStmt->execute();
                $recentEvents = $recentStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $ex) {
                if ($ex instanceof PageResponse) {
                    throw $ex;
                }
                $error = 'Unable to load dashboard data: '.\gc_public_error($ex);
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.dashboard', get_defined_vars());
        });
    }
}
