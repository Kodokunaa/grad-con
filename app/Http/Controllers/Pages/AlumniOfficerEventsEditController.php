<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PrivateUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
                            $new_image_name = 'event_'.Str::uuid().'.'.$ext;
                            if (PrivateUploads::store(request()->file('image'), 'events', $new_image_name)) {
                                PrivateUploads::delete('events', $event['image'] ?? null);
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
