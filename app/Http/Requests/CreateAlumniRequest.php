<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class CreateAlumniRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    protected function prepareForValidation(): void { $this->merge(['fullname' => trim((string) $this->fullname), 'student_id' => trim((string) $this->student_id), 'email' => trim((string) $this->email), 'course' => trim((string) $this->course)]); }
    public function rules(): array
    {
        if (! $this->isMethod('POST')) return [];
        return ['fullname' => 'required|string|max:150', 'student_id' => 'required|string|max:100|unique:users,username', 'email' => 'nullable|email|max:150|unique:users,email', 'course' => ['nullable', Rule::in(config('gradconn.courses'))], 'batch_year' => 'nullable|integer|min:2000|max:'.date('Y'), 'password' => ['required', 'string', 'max:1024', Password::defaults()]];
    }
}
