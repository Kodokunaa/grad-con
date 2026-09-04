<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Event;
use Illuminate\Http\Request;

final class AdminAdminArchiveController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            \gc_require_role('admin');
            $message = (string) session('status', '');
            $error = '';
            $archivedEvents = Event::query()->where('is_archived', true)->with(['author', 'comments.author'])->orderByDesc('archived_at')->latest('id')->get()->map(function ($event) {
                $row = $event->toArray();
                $row['poster'] = $event->author?->fullname;
                $row['comments'] = $event->comments->map(function ($comment) {
                    $row = $comment->toArray();
                    $row['fullname'] = $comment->author?->fullname;
                    return $row;
                })->all();
                return $row;
            })->all();
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.admin_archive', get_defined_vars());
        });
    }
}
