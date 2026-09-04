<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['old_password' => ['required', 'string', 'current_password'], 'new_password' => ['required', 'string', Password::min(8), 'same:confirm_password'], 'confirm_password' => ['required', 'string']];
    }
}
