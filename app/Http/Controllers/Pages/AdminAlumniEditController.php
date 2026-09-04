<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class AdminAlumniEditController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            \gc_require_role('admin');
            $id = (int) (\gc_context()->query['id'] ?? 0);
            $account = User::query()->whereKey($id)->where('role', 'alumni')->first();
            if (! $account) {
                \gc_finish('Alumni not found.');
            }
            Gate::authorize('update', $account);
            $user = $account->toArray();
            $msg = '';
            $error = '';
            $msg = (string) session('status', '');
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_edit', get_defined_vars());
        });
    }
}
