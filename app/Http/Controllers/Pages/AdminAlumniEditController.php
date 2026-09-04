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
            $id = (int) (request()->query('id') ?? 0);
            $account = User::query()->whereKey($id)->where('role', 'alumni')->first();
            if (! $account) {
                abort(404, 'Alumni not found.');
            }
            Gate::authorize('update', $account);
            $user = $account->toArray();
            $msg = '';
            $error = '';
            $msg = (string) session('status', '');
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_edit', get_defined_vars());
        });
    }
}
