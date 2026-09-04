<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

final class AdminJobsNotifyController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $model = Job::findOrFail($request->integer('job_id'));
            $job = $model->getAttributes();
            $target_course = trim((string) $model->target_course);
            $recipients = User::query()->where('role', 'alumni')->where('is_active', 1)
                ->where(fn ($query) => $query->where('receive_update_notifications', 1)->orWhereNull('receive_update_notifications'))
                ->whereNotNull('email')->where('email', '<>', '')
                ->when($target_course !== '', fn ($query) => $query->where('course', $target_course))
                ->orderBy('fullname')->get()->toArray();
            $msg = session('status', '');
            $error = '';
            echo view('partials.header', get_defined_vars());
            echo view('partials.admin_sidebar', get_defined_vars());

            return $this->pageView('pages.admin.jobs_notify', get_defined_vars());
        });
    }
}
