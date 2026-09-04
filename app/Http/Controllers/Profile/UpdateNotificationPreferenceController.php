<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class UpdateNotificationPreferenceController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->user()->role === 'alumni', 403);
        $request->user()->forceFill(['receive_update_notifications' => $request->boolean('receive_update_notifications')])->save();

        return to_route('profile')->with('status', 'Notification preference updated.');
    }
}
