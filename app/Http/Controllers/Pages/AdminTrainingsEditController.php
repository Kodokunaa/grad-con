<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminTrainingsEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $id = (int) (\gc_context()->query['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT * FROM trainings WHERE id=? LIMIT 1');
            $stmt->execute([$id]);
            $training = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $training) {
                \gc_finish('Training not found.');
            }
            $msg = '';
            $error = '';
            $allowed_courses = ['BSIS', 'BSTM', 'BSHM', 'BSED Math', 'BSED English', 'BSED Science', 'BSNED', 'Open for All'];
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $title = trim(\gc_context()->post['title'] ?? '');
                $content = trim(\gc_context()->post['content'] ?? '');
                $training_date = trim(\gc_context()->post['training_date'] ?? '');
                $location = trim(\gc_context()->post['location'] ?? '');
                $target_course = trim(\gc_context()->post['target_course'] ?? '');
                $current_image = $training['image'] ?? null;
                $image_name = $current_image;
                if ($title === '' || $content === '' || $training_date === '' || $target_course === '') {
                    $error = 'Title, description, training date, and target course are required.';
                } elseif (! in_array($target_course, $allowed_courses, true)) {
                    $error = 'Invalid target course selected.';
                } else {
                    if (! empty(\gc_files()['image']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $ext = strtolower(pathinfo(\gc_files()['image']['name'], PATHINFO_EXTENSION));
                        if (! in_array($ext, $allowed, true)) {
                            $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
                        } else {
                            $upload_dir = \storage_path('app/private/files/uploads/trainings/');
                            if (! is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $image_name = 'training_'.time().'_'.rand(1000, 9999).'.'.$ext;
                            $target = $upload_dir.$image_name;
                            if (! \gc_move_upload(\gc_files()['image']['tmp_name'], $target)) {
                                $error = 'Image upload failed.';
                            } elseif (! empty($current_image) && file_exists($upload_dir.$current_image)) {
                                @unlink($upload_dir.$current_image);
                            }
                        }
                    }
                    if ($error === '') {
                        $upd = $pdo->prepare("\r\n                UPDATE trainings\r\n                SET title=?, content=?, training_date=?, location=?, target_course=?, image=?\r\n                WHERE id=?\r\n            ");
                        $upd->execute([$title, $content, $training_date, $location, $target_course, $image_name, $id]);
                        $msg = 'Training updated successfully!';
                        $stmt->execute([$id]);
                        $training = $stmt->fetch(\PDO::FETCH_ASSOC);
                    }
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.trainings_edit', get_defined_vars());
        });
    }
}
