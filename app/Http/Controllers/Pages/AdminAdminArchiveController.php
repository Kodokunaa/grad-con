<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminAdminArchiveController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $message = (string) session('status', '');
            $error = '';
            try {
                if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['delete_comment'])) {
                    $eventId = (int) (\gc_context()->post['event_id'] ?? 0);
                    $commentId = (int) (\gc_context()->post['comment_id'] ?? 0);
                    $checkArchived = $pdo->prepare('SELECT id FROM events WHERE id=? AND is_archived=1 LIMIT 1');
                    $checkArchived->execute([$eventId]);
                    if ($eventId > 0 && $commentId > 0 && $checkArchived->fetchColumn()) {
                        $deleteReplies = $pdo->prepare("DELETE FROM post_comments WHERE parent_comment_id=? AND post_type='event' AND post_id=?");
                        $deleteReplies->execute([$commentId, $eventId]);
                        $deleteComment = $pdo->prepare("DELETE FROM post_comments WHERE id=? AND post_type='event' AND post_id=?");
                        $deleteComment->execute([$commentId, $eventId]);
                        $message = 'Comment deleted successfully.';
                    }
                }
                $eventsStmt = $pdo->query('SELECT e.*, u.fullname AS poster FROM events e LEFT JOIN users u ON u.id=e.posted_by WHERE e.is_archived=1 ORDER BY e.archived_at DESC, e.id DESC');
                $archivedEvents = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC);
                $commentStmt = $pdo->prepare("SELECT pc.*, u.fullname FROM post_comments pc LEFT JOIN users u ON u.id=pc.user_id WHERE pc.post_type='event' AND pc.post_id=? ORDER BY pc.id ASC");
                foreach ($archivedEvents as &$archivedEvent) {
                    $commentStmt->execute([(int) $archivedEvent['id']]);
                    $archivedEvent['comments'] = $commentStmt->fetchAll(\PDO::FETCH_ASSOC);
                }
                unset($archivedEvent);
            } catch (\Throwable $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $archivedEvents = [];
                $error = 'Unable to load archived events: '.\gc_public_error($e);
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.admin_archive', get_defined_vars());
        });
    }
}
