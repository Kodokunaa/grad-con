<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PrivateUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class AlumniOfficerEventsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni_officer');
            $msg = '';
            $error = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && $error === '') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $content = trim(\gc_context()->post['content'] ?? '');
                $post_start_date = \gc_alumni_officer_events_create_to_mysql_datetime(\gc_context()->post['post_start_date'] ?? '');
                $post_end_date = \gc_alumni_officer_events_create_to_mysql_datetime(\gc_context()->post['post_end_date'] ?? '');
                $image_name = null;
                if ($title === '' || $content === '') {
                    $error = 'Title and content are required.';
                } elseif (! empty(\gc_context()->post['post_start_date']) && $post_start_date === null) {
                    $error = 'Invalid start date.';
                } elseif (! empty(\gc_context()->post['post_end_date']) && $post_end_date === null) {
                    $error = 'Invalid end date.';
                } elseif ($post_start_date !== null && $post_end_date !== null && strtotime($post_end_date) < strtotime($post_start_date)) {
                    $error = 'End date must be after the start date.';
                } else {
                    if (! empty(\gc_files()['image']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
                        } elseif ((\gc_files()['image']['size'] ?? 0) > 3 * 1024 * 1024) {
                            $error = 'Image too large. Max 3MB.';
                        } else {
                            $image_name = 'event_'.Str::uuid().'.'.$ext;
                            if (! PrivateUploads::store(request()->file('image'), 'events', $image_name)) {
                                $error = 'Image upload failed.';
                            }
                        }
                    }
                    if ($error === '') {
                        $stmt = $pdo->prepare('INSERT INTO events(title, content, image, post_start_date, post_end_date, posted_by) VALUES(?,?,?,?,?,?)');
                        $stmt->execute([$title, $content, $image_name, $post_start_date, $post_end_date, \gc_context()->session['user']['id']]);
                        $msg = 'Event posted successfully!';
                        \gc_context()->post = [];
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.events_create', get_defined_vars());
        });
    }
}
