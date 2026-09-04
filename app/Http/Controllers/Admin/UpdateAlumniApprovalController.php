<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAlumniApprovalRequest;
use App\Mail\AlumniAccountApprovedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class UpdateAlumniApprovalController extends Controller
{
    public function __invoke(UpdateAlumniApprovalRequest $request)
    {
        $alumni = User::query()->where('role', 'alumni')->findOrFail($request->integer('user_id'));
        $request->user()->can('update', $alumni) || abort(403);
        $approved = $request->string('action')->toString() === 'approve';
        $alumni->forceFill(['is_active' => $approved, 'status' => $approved ? 'approved' : 'rejected'])->save();
        if ($approved && filled($alumni->email)) {
            Mail::to($alumni)->queue(new AlumniAccountApprovedMail($alumni));
        }

        return to_route('admin.pending_alumni')->with('status', $approved ? 'Alumni account approved and notification queued.' : 'Alumni account rejected.');
    }
}
