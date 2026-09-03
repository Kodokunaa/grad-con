<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminEventsCreateController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $msg = '';
            $error = '';
            try {
                if (! \gc_admin_events_create_column_exists($pdo, 'events', 'post_start_date')) {
                    \gc_context()->schemaChange($pdo, 'ALTER TABLE events ADD COLUMN post_start_date DATETIME NULL AFTER image');
                }
                if (! \gc_admin_events_create_column_exists($pdo, 'events', 'post_end_date')) {
                    \gc_context()->schemaChange($pdo, 'ALTER TABLE events ADD COLUMN post_end_date DATETIME NULL AFTER post_start_date');
                }
            } catch (\Throwable $ex) {
                if ($ex instanceof PageResponse) {
                    throw $ex;
                }
                $error = 'Database setup error: '.\gc_public_error($ex);
            }
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $content = trim(\gc_context()->post['content'] ?? '');
                $post_start_date = trim(\gc_context()->post['post_start_date'] ?? '');
                $post_end_date = trim(\gc_context()->post['post_end_date'] ?? '');
                $image_name = null;
                $startDateForDb = $post_start_date !== '' ? date('Y-m-d H:i:s', strtotime($post_start_date)) : null;
                $endDateForDb = $post_end_date !== '' ? date('Y-m-d H:i:s', strtotime($post_end_date)) : null;
                if ($title === '' || $content === '') {
                    $error = 'Title and content are required.';
                } elseif ($post_start_date !== '' && strtotime($post_start_date) === false) {
                    $error = 'Invalid post start date.';
                } elseif ($post_end_date !== '' && strtotime($post_end_date) === false) {
                    $error = 'Invalid post end date.';
                } elseif ($startDateForDb && $endDateForDb && strtotime($endDateForDb) <= strtotime($startDateForDb)) {
                    $error = 'Post end date must be later than post start date.';
                } else {
                    if (! empty(\gc_files()['image']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
                        } elseif ((\gc_files()['image']['size'] ?? 0) > 3 * 1024 * 1024) {
                            $error = 'Image too large. Max 3MB.';
                        } else {
                            $upload_dir = \storage_path('app/private/files/admin').'/../uploads/events/';
                            if (! is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $image_name = 'event_'.time().'_'.rand(1000, 9999).'.'.$ext;
                            $target = $upload_dir.$image_name;
                            if (! \gc_move_upload(\gc_files()['image']['tmp_name'], $target)) {
                                $error = 'Image upload failed.';
                            }
                        }
                    }
                    if ($error === '') {
                        try {
                            $stmt = $pdo->prepare('INSERT INTO events(title, content, image, post_start_date, post_end_date, posted_by) VALUES(?, ?, ?, ?, ?, ?)');
                            $stmt->execute([$title, $content, $image_name, $startDateForDb, $endDateForDb, \gc_context()->session['user']['id']]);
                            $msg = 'Event posted successfully!';
                            \gc_context()->post = [];
                        } catch (\Throwable $ex) {
                            if ($ex instanceof PageResponse) {
                                throw $ex;
                            }
                            $error = 'Post error: '.\gc_public_error($ex);
                        }
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.events_create', get_defined_vars());
        });
    }
}
