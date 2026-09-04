<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AlumniOfficerEventsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni_officer');
            $msg = '';
            $error = '';
            $id = (int) (\gc_context()->query['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $event = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $event) {
                \gc_finish('Event not found.');
            }
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $content = trim(\gc_context()->post['content'] ?? '');
                $image_name = $event['image'];
                if ($title === '' || $content === '') {
                    $error = 'Title and content are required.';
                } else {
                    if (! empty(\gc_files()['image']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
                        } elseif ((\gc_files()['image']['size'] ?? 0) > 3 * 1024 * 1024) {
                            $error = 'Image too large. Max 3MB.';
                        } else {
                            $upload_dir = \storage_path('app/private/files/uploads/events/');
                            if (! is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $new_image_name = 'event_'.time().'_'.rand(1000, 9999).'.'.$ext;
                            $target = $upload_dir.$new_image_name;
                            if (\gc_move_upload(\gc_files()['image']['tmp_name'], $target)) {
                                if (! empty($event['image'])) {
                                    $oldImage = \storage_path('app/private/files/uploads/events/'.$event['image']);
                                    if (is_file($oldImage)) {
                                        @unlink($oldImage);
                                    }
                                }
                                $image_name = $new_image_name;
                            } else {
                                $error = 'Image upload failed.';
                            }
                        }
                    }
                    if ($error === '') {
                        $upd = $pdo->prepare('UPDATE events SET title = ?, content = ?, image = ? WHERE id = ?');
                        $upd->execute([$title, $content, $image_name, $id]);
                        $msg = 'Event updated successfully.';
                        $stmt->execute([$id]);
                        $event = $stmt->fetch(\PDO::FETCH_ASSOC);
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.events_edit', get_defined_vars());
        });
    }
}
