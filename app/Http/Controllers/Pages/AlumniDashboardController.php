<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniDashboardController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $fullname = \gc_context()->session['user']['fullname'] ?? 'User';
            $alumni_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());
            $totalApplications = 0;
            $pendingApplications = 0;
            $rejectedApplications = 0;
            $hiredApplications = 0;
            $upcomingInterviews = 0;
            $totalJobOffers = 0;
            $acceptedJobOffers = 0;
            $declinedJobOffers = 0;
            $pendingJobOffers = 0;
            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE alumni_id = ?');
                $stmt->execute([$alumni_id]);
                $totalApplications = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE alumni_id = ? AND status = 'pending'");
                $stmt->execute([$alumni_id]);
                $pendingApplications = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE alumni_id = ? AND status = 'rejected'");
                $stmt->execute([$alumni_id]);
                $rejectedApplications = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE alumni_id = ? AND status = 'hired'");
                $stmt->execute([$alumni_id]);
                $hiredApplications = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("\r\n        SELECT COUNT(*) \r\n        FROM applications \r\n        WHERE alumni_id = ? \r\n        AND status IN ('interview','for interview')\r\n    ");
                $stmt->execute([$alumni_id]);
                $upcomingInterviews = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM job_offers WHERE alumni_id = ?');
                $stmt->execute([$alumni_id]);
                $totalJobOffers = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_offers WHERE alumni_id = ? AND status = 'accepted'");
                $stmt->execute([$alumni_id]);
                $acceptedJobOffers = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_offers WHERE alumni_id = ? AND status = 'declined'");
                $stmt->execute([$alumni_id]);
                $declinedJobOffers = (int) $stmt->fetchColumn();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_offers WHERE alumni_id = ? AND status = 'sent'");
                $stmt->execute([$alumni_id]);
                $pendingJobOffers = (int) $stmt->fetchColumn();
            } catch (\Exception $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $totalApplications = 0;
                $pendingApplications = 0;
                $rejectedApplications = 0;
                $hiredApplications = 0;
                $upcomingInterviews = 0;
                $totalJobOffers = 0;
                $acceptedJobOffers = 0;
                $declinedJobOffers = 0;
                $pendingJobOffers = 0;
            }

            return $this->pageView('pages.alumni.dashboard', get_defined_vars());
        });
    }
}
