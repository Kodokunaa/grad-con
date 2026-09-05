<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelApplicationRequest;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class AlumniApplicationActionController extends Controller
{
    public function cancel(CancelApplicationRequest $request, JobApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);
        abort_if(in_array(strtolower(trim((string) $application->status)), ['accepted', 'hired', 'rejected', 'cancelled'], true), 422, 'This application can no longer be cancelled.');
        $application->forceFill(['status' => 'cancelled', 'cancel_reason' => $request->string('cancel_reason')->trim()->toString(), 'cancelled_at' => now()])->save();

        return to_route('alumni.my_applications')->with('status', 'Application cancelled successfully.');
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        Gate::authorize('delete', $application);
        abort_unless(strtolower(trim((string) $application->status)) === 'cancelled', 422, 'Only cancelled applications can be removed.');
        $application->delete();

        return to_route('alumni.my_applications')->with('status', 'Cancelled application removed successfully.');
    }
}
