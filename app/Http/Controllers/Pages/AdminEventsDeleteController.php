<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminEventsDeleteController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $id = (int) (\gc_context()->query['id'] ?? 0);
            if ($id <= 0) {
                \gc_header('Location: '.\url('').'/admin/events_list.php');
                \gc_finish();
            }
            // Get event info first (so we know the image file)
            $stmt = $pdo->prepare('SELECT id, image FROM events WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $event = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $event) {
                \gc_header('Location: '.\url('').'/admin/events_list.php');
                \gc_finish();
            }
            // Delete image file if exists
            if (! empty($event['image'])) {
                $imgPath = \storage_path('app/private/files/admin').'/../uploads/events/'.$event['image'];
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }
            // Delete event record
            $del = $pdo->prepare('DELETE FROM events WHERE id=?');
            $del->execute([$id]);
            // Redirect back with success message
            \gc_header('Location: '.\url('').'/admin/events_list.php?deleted=1');
            \gc_finish();

            return $this->pageView('pages.admin.events_delete', get_defined_vars());
        });
    }
}
