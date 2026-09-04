<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class CreateAlumniOfficerRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    protected function prepareForValidation(): void { $this->merge(['fullname' => trim((string) $this->fullname), 'email' => trim((string) $this->email), 'username' => trim((string) $this->username), 'password_confirmation' => $this->input('confirm_password')]); }
    public function rules(): array
    {
        if (! $this->isMethod('POST')) return [];
        return ['fullname' => 'required|string|max:150', 'email' => 'required|email|max:150|unique:users,email', 'username' => 'required|string|min:3|max:100|unique:users,username', 'password' => ['required', 'string', 'max:1024', Password::defaults(), 'confirmed'], 'is_active' => 'nullable|boolean'];
    }
}
