<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\User;
use Illuminate\Http\Request;

final class AdminPendingAlumniController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $success = session('status', '');
            $error = '';
            $pendingUsers = User::query()->where('role', 'alumni')->where('status', 'pending')
                ->orderBy('id')->get(['id', 'fullname', 'username', 'email', 'course', 'batch_year', 'status'])->toArray();
            echo gc_partial('header', get_defined_vars());
            echo gc_partial('admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.pending_alumni', get_defined_vars());
        });
    }
}
