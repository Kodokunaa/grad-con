<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Http\Requests\CreateEmployerRequest;
use App\Models\User;

final class AdminCreateEmployerController extends PageController
{
    public function __invoke(CreateEmployerRequest $request)
    {
        return $this->renderPage(function () use ($request) {
            $success = '';
            $error = $request->session()->get('errors')?->first() ?? '';
            if ($request->isMethod('POST')) {
                $data = $request->validated();
                User::forceCreate(['fullname' => $data['fullname'], 'employer_company' => $data['company'], 'username' => $data['username'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'employer', 'is_active' => true, 'status' => 'approved']);
                $success = 'Employer account created successfully.';
            }
            return $this->pageView('pages.admin.create_employer', get_defined_vars());
        });
    }
}
