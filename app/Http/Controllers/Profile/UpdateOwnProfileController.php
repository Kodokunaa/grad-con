<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOwnProfileRequest;

final class UpdateOwnProfileController extends Controller
{
    public function __invoke(UpdateOwnProfileRequest $request)
    {
        $data = $request->validated();
        if (($data['employment_status'] ?? null) !== 'Employed') {
            $data['job_aligned'] = null;
        }
        $request->user()->forceFill($data)->save();

        return to_route('alumni.edit_profile')->with('status', 'Profile updated successfully.');
    }
}
