<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CancelApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'alumni';
    }

    public function rules(): array
    {
        return ['cancel_reason' => ['required', 'string', 'min:10', 'max:2000']];
    }
}
