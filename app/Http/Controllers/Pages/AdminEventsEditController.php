<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PrivateUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
                            PrivateUploads::delete('events', $newImageName);
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
                            $newFile = 'event_'.$id.'_'.Str::uuid().'.'.$ext;
                            if (! PrivateUploads::store(request()->file('image'), 'events', $newFile)) {
                                $error = 'Image upload failed.';
                            } else {
                                PrivateUploads::delete('events', $newImageName);
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
