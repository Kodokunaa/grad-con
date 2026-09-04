<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminEventsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $id = (int) (\gc_context()->query['id'] ?? 0);
            if ($id <= 0) {
                \gc_finish('Invalid event.');
            }
            $msg = '';
            $error = '';
            // Fetch event
            $stmt = $pdo->prepare('SELECT * FROM events WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $event = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $event) {
                \gc_finish('Event not found.');
            }
            // Handle update
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $content = trim(\gc_context()->post['content'] ?? '');
                $remove_image = isset(\gc_context()->post['remove_image']) ? 1 : 0;
                if ($title === '' || $content === '') {
                    $error = 'Title and content are required.';
                } else {
                    $newImageName = $event['image'] ?? null;
                    // Remove current image
                    if ($remove_image === 1) {
                        if (! empty($newImageName)) {
                            $oldPath = \storage_path('app/private/files/uploads/events/'.$newImageName);
                            if (file_exists($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                        $newImageName = null;
                    }
                    // Upload new image (optional)
                    if (! empty(\gc_files()['image']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $error = 'Invalid image type. Use JPG, JPEG, PNG, or WEBP.';
                        } elseif (\gc_files()['image']['size'] > 3 * 1024 * 1024) {
                            $error = 'Image too large. Max 3MB.';
                        } else {
                            $dir = \storage_path('app/private/files/uploads/events/');
                            if (! is_dir($dir)) {
                                mkdir($dir, 0777, true);
                            }
                            $newFile = 'event_'.$id.'_'.time().'.'.$ext;
                            $target = $dir.$newFile;
                            if (! \gc_move_upload(\gc_files()['image']['tmp_name'], $target)) {
                                $error = 'Image upload failed.';
                            } else {
                                // Delete old image if exists
                                if (! empty($newImageName)) {
                                    $oldPath = $dir.$newImageName;
                                    if (file_exists($oldPath)) {
                                        @unlink($oldPath);
                                    }
                                }
                                $newImageName = $newFile;
                            }
                        }
                    }
                    // Save if no errors
                    if ($error === '') {
                        $up = $pdo->prepare('UPDATE events SET title=?, content=?, image=? WHERE id=?');
                        $up->execute([$title, $content, $newImageName, $id]);
                        $msg = 'Event updated successfully!';
                        // Refresh data
                        $stmt = $pdo->prepare('SELECT * FROM events WHERE id=? LIMIT 1');
                        $stmt->execute([$id]);
                        $event = $stmt->fetch(\PDO::FETCH_ASSOC);
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.events_edit', get_defined_vars());
        });
    }
}
