<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Services\UpdatePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class AlumniChangePasswordController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () use ($request) {
            $msg = '';
            $error = '';

            if ($request->isMethod('POST')) {
                $input = $request->all();
                $input['new_password_confirmation'] = $request->input('confirm_password');
                $validator = Validator::make($input, [
                    'old_password' => ['required', 'current_password'],
                    'new_password' => ['required', 'string', 'max:1024', Password::defaults(), 'confirmed'],
                ], [
                    'old_password.current_password' => 'Old password is incorrect.',
                    'new_password.confirmed' => 'New password and confirm password do not match.',
                ]);

                if ($validator->fails()) {
                    $error = $validator->errors()->first();
                } else {
                    $changed = app(UpdatePassword::class)->handle(
                        $request->user(),
                        $request->string('old_password')->toString(),
                        $request->string('new_password')->toString(),
                        $request,
                    );
                    $error = $changed ? '' : 'Old password is incorrect.';
                    $msg = $changed ? 'Password changed successfully!' : '';
                }
            }

            echo \gc_partial('header', get_defined_vars());
            echo \gc_partial('alumni_sidebar', get_defined_vars());

            return $this->pageView('pages.alumni.change_password', get_defined_vars());
        });
    }
}
