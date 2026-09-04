<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Http\Requests\CreateAlumniOfficerRequest;
use App\Models\User;

final class AdminCreateAlumniOfficerController extends PageController
{
    public function __invoke(CreateAlumniOfficerRequest $request)
    {
        return $this->renderPage(function () use ($request) {
            $success = '';
            $error = $request->session()->get('errors')?->first() ?? '';
            if ($request->isMethod('POST')) {
                $data = $request->validated();
                User::forceCreate(['fullname' => $data['fullname'], 'username' => $data['username'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'alumni_officer', 'is_active' => $request->boolean('is_active'), 'status' => 'approved']);
                $success = 'Alumni Officer account created successfully.';
            }
            return $this->pageView('pages.admin.create_alumni_officer', get_defined_vars());
        });
    }
}
