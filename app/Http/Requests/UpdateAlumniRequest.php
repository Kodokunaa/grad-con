<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UpdateAlumniRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fullname' => trim((string) $this->fullname),
            'email' => trim((string) $this->email),
            'course' => trim((string) $this->course),
            'batch_year' => trim((string) $this->batch_year),
        ]);
    }

    public function rules(): array
    {
        $userId = (int) $this->route('alumni')?->id;

        return [
            'fullname' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'course' => ['nullable', Rule::in(config('gradconn.courses'))],
            'batch_year' => ['nullable', 'integer', 'min:2000', 'max:'.date('Y')],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'max:1024', Password::defaults()],
        ];
    }
}
