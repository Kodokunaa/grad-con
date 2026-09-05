<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AlumniAccountApprovedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

final class UpdateAlumniApprovalController extends Controller
{
    public function approve(Request $request, User $alumni)
    {
        abort_unless($alumni->role === 'alumni', 404);
        $request->user()->can('update', $alumni) || abort(403);
        $alumni->forceFill(['is_active' => true, 'status' => 'approved'])->save();
        Cache::forget('sidebar.pending-alumni.v1');
        if (filled($alumni->email)) {
            Mail::to($alumni)->queue(new AlumniAccountApprovedMail($alumni));
        }

        return to_route('admin.pending_alumni')->with('status', 'Alumni account approved and notification queued.');
    }

    public function reject(Request $request, User $alumni)
    {
        abort_unless($alumni->role === 'alumni', 404);
        $request->user()->can('update', $alumni) || abort(403);
        $alumni->forceFill(['is_active' => false, 'status' => 'rejected'])->save();
        Cache::forget('sidebar.pending-alumni.v1');

        return to_route('admin.pending_alumni')->with('status', 'Alumni account rejected.');
    }
}
