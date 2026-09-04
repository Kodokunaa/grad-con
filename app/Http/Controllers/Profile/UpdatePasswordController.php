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

        $route = $request->boolean('change_password_page') ? 'alumni.change_password' : 'profile';

        return to_route($route)->with('status', 'Password changed successfully.');
    }
}
