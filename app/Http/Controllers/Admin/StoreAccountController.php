<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAlumniOfficerRequest;
use App\Http\Requests\CreateAlumniRequest;
use App\Http\Requests\CreateEmployerRequest;
use App\Models\User;

final class StoreAccountController extends Controller
{
    public function alumni(CreateAlumniRequest $request)
    {
        $data = $request->validated();
        User::forceCreate(['fullname' => $data['fullname'], 'username' => $data['student_id'], 'password' => $data['password'], 'email' => ($data['email'] ?? '') ?: null, 'course' => ($data['course'] ?? '') ?: null, 'batch_year' => ($data['batch_year'] ?? '') ?: null, 'role' => 'alumni', 'is_active' => true, 'status' => 'approved']);

        return to_route('admin.alumni_create')->with('status', 'Alumni account created.');
    }

    public function officer(CreateAlumniOfficerRequest $request)
    {
        $data = $request->validated();
        User::forceCreate(['fullname' => $data['fullname'], 'username' => $data['username'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'alumni_officer', 'is_active' => $request->boolean('is_active'), 'status' => 'approved']);

        return to_route('admin.create_alumni_officer')->with('status', 'Alumni Officer account created successfully.');
    }

    public function employer(CreateEmployerRequest $request)
    {
        $data = $request->validated();
        User::forceCreate(['fullname' => $data['fullname'], 'employer_company' => $data['company'], 'username' => $data['username'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'employer', 'is_active' => true, 'status' => 'approved']);

        return to_route('admin.create_employer')->with('status', 'Employer account created successfully.');
    }
}
