<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Support\Facades\Gate;

final class DestroyJobController extends Controller
{
    public function __invoke(Job $job)
    {
        Gate::authorize('delete', $job);
        $job->delete();

        return to_route('admin.jobs_list')->with('status', 'Job deleted.');
    }
}
