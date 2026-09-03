<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('employer');
            $eid = (int) (\gc_context()->session['user']['id'] ?? 0);
            $fullname = \gc_context()->session['user']['fullname'] ?? 'Employer';
            $jobsCount = 0;
            $openJobsCount = 0;
            $closedJobsCount = 0;
            $appsCount = 0;
            $pendingCount = 0;
            $interviewCount = 0;
            $acceptedCount = 0;
            $hiredCount = 0;
            $rejectedCount = 0;
            $offersCount = 0;
            $offersAcceptedCount = 0;
            $offersDeclinedCount = 0;
            $offersPendingCount = 0;
            $latest = [];
            $latestOffers = [];
            try {
                $statsStmt = $pdo->prepare("\r\n        SELECT\r\n            (SELECT COUNT(*) FROM jobs WHERE employer_id = ?) AS jobs_count,\r\n\r\n            (SELECT COUNT(*) FROM jobs \r\n             WHERE employer_id = ? \r\n             AND is_open = 1\r\n             AND (start_date IS NULL OR start_date <= CURDATE())\r\n             AND (end_date IS NULL OR end_date >= CURDATE())) AS open_jobs_count,\r\n\r\n            (SELECT COUNT(*) FROM jobs \r\n             WHERE employer_id = ? \r\n             AND (\r\n                is_open = 0\r\n                OR (end_date IS NOT NULL AND end_date < CURDATE())\r\n             )) AS closed_jobs_count,\r\n\r\n            (SELECT COUNT(*)\r\n             FROM applications a\r\n             JOIN jobs j ON j.id = a.job_id\r\n             WHERE j.employer_id = ?) AS apps_count,\r\n\r\n            (SELECT COUNT(*)\r\n             FROM applications a\r\n             JOIN jobs j ON j.id = a.job_id\r\n             WHERE j.employer_id = ? AND a.status = 'pending') AS pending_count,\r\n\r\n            (SELECT COUNT(*)\r\n             FROM applications a\r\n             JOIN jobs j ON j.id = a.job_id\r\n             WHERE j.employer_id = ? AND a.status IN ('interview','for interview')) AS interview_count,\r\n\r\n            (SELECT COUNT(*)\r\n             FROM applications a\r\n             JOIN jobs j ON j.id = a.job_id\r\n             WHERE j.employer_id = ? AND a.status = 'accepted') AS accepted_count,\r\n\r\n            (SELECT COUNT(*)\r\n             FROM applications a\r\n             JOIN jobs j ON j.id = a.job_id\r\n             WHERE j.employer_id = ? AND a.status = 'hired') AS hired_count,\r\n\r\n            (SELECT COUNT(*)\r\n             FROM applications a\r\n             JOIN jobs j ON j.id = a.job_id\r\n             WHERE j.employer_id = ? AND a.status = 'rejected') AS rejected_count,\r\n\r\n            (SELECT COUNT(*) FROM job_offers WHERE employer_id = ?) AS offers_count,\r\n\r\n            (SELECT COUNT(*) FROM job_offers WHERE employer_id = ? AND status = 'accepted') AS offers_accepted_count,\r\n\r\n            (SELECT COUNT(*) FROM job_offers WHERE employer_id = ? AND status = 'declined') AS offers_declined_count,\r\n\r\n            (SELECT COUNT(*) FROM job_offers WHERE employer_id = ? AND status = 'sent') AS offers_pending_count\r\n    ");
                $statsStmt->execute([$eid, $eid, $eid, $eid, $eid, $eid, $eid, $eid, $eid, $eid, $eid, $eid, $eid]);
                $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
                $jobsCount = (int) ($stats['jobs_count'] ?? 0);
                $openJobsCount = (int) ($stats['open_jobs_count'] ?? 0);
                $closedJobsCount = (int) ($stats['closed_jobs_count'] ?? 0);
                $appsCount = (int) ($stats['apps_count'] ?? 0);
                $pendingCount = (int) ($stats['pending_count'] ?? 0);
                $interviewCount = (int) ($stats['interview_count'] ?? 0);
                $acceptedCount = (int) ($stats['accepted_count'] ?? 0);
                $hiredCount = (int) ($stats['hired_count'] ?? 0);
                $rejectedCount = (int) ($stats['rejected_count'] ?? 0);
                $offersCount = (int) ($stats['offers_count'] ?? 0);
                $offersAcceptedCount = (int) ($stats['offers_accepted_count'] ?? 0);
                $offersDeclinedCount = (int) ($stats['offers_declined_count'] ?? 0);
                $offersPendingCount = (int) ($stats['offers_pending_count'] ?? 0);
                $latestStmt = $pdo->prepare("\r\n        SELECT \r\n            a.id,\r\n            a.status,\r\n            a.created_at,\r\n            u.fullname,\r\n            u.email,\r\n            j.title,\r\n            j.id AS job_id\r\n        FROM applications a\r\n        JOIN users u ON u.id = a.alumni_id\r\n        JOIN jobs j ON j.id = a.job_id\r\n        WHERE j.employer_id = ?\r\n        ORDER BY a.id DESC\r\n        LIMIT 8\r\n    ");
                $latestStmt->execute([$eid]);
                $latest = $latestStmt->fetchAll(\PDO::FETCH_ASSOC);
                $latestOffersStmt = $pdo->prepare("\r\n        SELECT \r\n            jo.id,\r\n            jo.status,\r\n            jo.created_at,\r\n            u.fullname,\r\n            u.email\r\n        FROM job_offers jo\r\n        JOIN users u ON u.id = jo.alumni_id\r\n        WHERE jo.employer_id = ?\r\n        ORDER BY jo.id DESC\r\n        LIMIT 5\r\n    ");
                $latestOffersStmt->execute([$eid]);
                $latestOffers = $latestOffersStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $jobsCount = 0;
                $openJobsCount = 0;
                $closedJobsCount = 0;
                $appsCount = 0;
                $pendingCount = 0;
                $interviewCount = 0;
                $acceptedCount = 0;
                $hiredCount = 0;
                $rejectedCount = 0;
                $offersCount = 0;
                $offersAcceptedCount = 0;
                $offersDeclinedCount = 0;
                $offersPendingCount = 0;
                $latest = [];
                $latestOffers = [];
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('employer_sidebar', \get_defined_vars());

            return $this->pageView('pages.employer.dashboard', get_defined_vars());
        });
    }
}
