<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class AdminTrainingsListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            // DELETE
            if (isset(\gc_context()->query['delete'])) {
                $id = (int) \gc_context()->query['delete'];
                $getImg = $pdo->prepare('SELECT image FROM trainings WHERE id=? LIMIT 1');
                $getImg->execute([$id]);
                $old = $getImg->fetch(\PDO::FETCH_ASSOC);
                if ($old && ! empty($old['image'])) {
                    $imgPath = \storage_path('app/private/files/uploads/trainings/'.$old['image']);
                    if (file_exists($imgPath)) {
                        @unlink($imgPath);
                    }
                }
                $del = $pdo->prepare('DELETE FROM trainings WHERE id=?');
                $del->execute([$id]);
                \gc_header('Location: trainings_list.php');
                \gc_finish();
            }
            // FETCH
            $trainings = $pdo->query("\r\n    SELECT t.*, u.fullname\r\n    FROM trainings t\r\n    LEFT JOIN users u ON u.id = t.posted_by\r\n    ORDER BY t.id DESC\r\n")->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.trainings_list', get_defined_vars());
        });
    }
}
