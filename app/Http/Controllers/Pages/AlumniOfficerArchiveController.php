<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniOfficerArchiveController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni_officer');
            $message = (string) session('status', '');
            $stmt = $pdo->query('SELECT e.*, u.fullname FROM events e LEFT JOIN users u ON u.id = e.posted_by WHERE e.is_archived = 1 ORDER BY e.archived_at DESC, e.id DESC');
            $archivedEvents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $commentStmt = $pdo->prepare("SELECT pc.*, u.fullname FROM post_comments pc LEFT JOIN users u ON u.id = pc.user_id WHERE pc.post_type = 'event' AND pc.post_id = ? ORDER BY pc.id ASC");
            foreach ($archivedEvents as &$event) {
                $eventId = (int) ($event['id'] ?? 0);
                if ($eventId > 0) {
                    $commentStmt->execute([$eventId]);
                    $event['comments'] = $commentStmt->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $event['comments'] = [];
                }
            }
            unset($event);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.archive', get_defined_vars());
        });
    }
}
