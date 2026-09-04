<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\UpdatePassword;

final class UpdatePasswordController extends Controller
{
    public function __invoke(UpdatePasswordRequest $request, UpdatePassword $service)
    {
        $service->handle($request->user(), $request->input('old_password'), $request->input('new_password'), $request);

        return to_route('profile')->with('status', 'Password changed successfully.');
    }
}
