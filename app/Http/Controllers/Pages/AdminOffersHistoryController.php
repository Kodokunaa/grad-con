<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminOffersHistoryController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $error = '';
            $logs = [];
            $employerCount = 0;
            $alumniCount = 0;
            try {
                $stmt = $pdo->prepare("SELECT l.*, emp.fullname AS employer_name, al.fullname AS alumni_name, al.email AS alumni_email,\r\n                 jo.status AS offer_status, jo.accepted_at, jo.declined_at\r\n         FROM employer_activity_logs l\r\n         LEFT JOIN users emp ON emp.id = l.employer_id\r\n         LEFT JOIN users al ON al.id = l.alumni_id\r\n            LEFT JOIN job_offers jo ON jo.id = l.offer_id\r\n         ORDER BY l.created_at DESC\r\n            LIMIT 500");
                $stmt->execute();
                $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $searchCount = 0;
                $offerCount = 0;
                foreach ($logs as $log) {
                    if (($log['action'] ?? '') === 'SEARCH_ALUMNI') {
                        $searchCount++;
                    } elseif (($log['action'] ?? '') === 'JOB_OFFER_SENT') {
                        $offerCount++;
                    }
                }
                $employerCount = count(array_unique(array_filter(array_map(static fn ($log) => (string) ($log['employer_id'] ?? ''), $logs))));
                $alumniCount = count(array_unique(array_filter(array_map(static fn ($log) => (string) ($log['alumni_id'] ?? ''), $logs))));
            } catch (\Throwable $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Unable to load employer activity history: '.\gc_public_error($e);
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.offers_history', get_defined_vars());
        });
    }
}
