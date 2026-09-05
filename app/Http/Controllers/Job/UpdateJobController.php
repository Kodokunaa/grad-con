<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Support\Facades\Cache;

final class UpdateJobController extends Controller
{
    public function __invoke(UpdateJobRequest $request, Job $job)
    {
        $job->update($request->validated());
        Cache::forget('feed.sidebar-jobs.v1');

        return to_route('admin.jobs_edit', ['id' => $job->id])->with('status', 'Job updated successfully.');
    }
}
