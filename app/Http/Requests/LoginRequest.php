<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => $this->input('username', $this->input('student_id'))]);
    }

    public function rules(): array
    {
        return ['username' => 'required|string|max:100', 'password' => 'required|string|max:1024'];
    }
}
