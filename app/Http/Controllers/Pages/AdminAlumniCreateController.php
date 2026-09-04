<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Http\Requests\CreateAlumniRequest;
use App\Models\User;

final class AdminAlumniCreateController extends PageController
{
    public function __invoke(CreateAlumniRequest $request)
    {
        return $this->renderPage(function () use ($request) {
            $msg = '';
            $error = $request->session()->get('errors')?->first() ?? '';
            $course_options = config('gradconn.courses');
            if ($request->isMethod('POST')) {
                $data = $request->validated();
                User::forceCreate(['fullname' => $data['fullname'], 'username' => $data['student_id'], 'password' => $data['password'], 'email' => ($data['email'] ?? '') ?: null, 'course' => ($data['course'] ?? '') ?: null, 'batch_year' => ($data['batch_year'] ?? '') ?: null, 'role' => 'alumni', 'is_active' => true, 'status' => 'approved']);
                $msg = 'Alumni account created!';
            }
            echo view('partials.header', get_defined_vars());
            echo view('partials.admin_sidebar', get_defined_vars());
            return $this->pageView('pages.admin.alumni_create', get_defined_vars());
        });
    }
}
