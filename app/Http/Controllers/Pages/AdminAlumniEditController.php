<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Http\Requests\UpdateAlumniRequest;
use App\Models\User;
use App\Support\PageResponse;
use Illuminate\Support\Facades\Gate;

final class AdminAlumniEditController extends PageController
{
    public function __invoke(UpdateAlumniRequest $request)
    {
        return $this->renderPage(function () use ($request) {
            $pdo = gc_context()->pdo();

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
            // ==========================
            // Delete Account
            // ==========================
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['delete_account'])) {
                try {
                    Gate::authorize('delete', $account);
                    // Delete related records first (to handle foreign key constraints)
                    $pdo->prepare('DELETE FROM security_logs WHERE user_id=?')->execute([$id]);
                    $pdo->prepare('DELETE FROM applications WHERE alumni_id=?')->execute([$id]);
                    // Then delete the user
                    $del = $pdo->prepare("DELETE FROM users WHERE id=? AND role='alumni' LIMIT 1");
                    $del->execute([$id]);
                    \gc_header('Location: '.\url('').'/admin/alumni_list.php?msg=Alumni deleted successfully');
                    \gc_finish();
                } catch (\Exception $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $error = 'Error deleting alumni: '.\gc_public_error($e);
                }
            }
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $data = $request->validated();
                $account->forceFill([
                    'fullname' => $data['fullname'],
                    'email' => ($data['email'] ?? '') ?: null,
                    'course' => ($data['course'] ?? '') ?: null,
                    'batch_year' => ($data['batch_year'] ?? '') ?: null,
                    'is_active' => $request->boolean('is_active'),
                ]);
                if (! empty($data['password'])) {
                    $account->password = $data['password'];
                }
                $account->save();
                $msg = 'Updated successfully!';
                $user = $account->fresh()->toArray();
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.alumni_edit', get_defined_vars());
        });
    }
}
