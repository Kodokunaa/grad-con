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

        if ($request->boolean('change_password_page')) {
            return to_route('alumni.change_password')->with('status', 'Password changed successfully.');
        }

        return to_route('profile', ['tab' => 'security'])->with('status', 'Password changed successfully.');
    }
}
