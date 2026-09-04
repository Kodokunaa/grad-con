<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'employer'], true);
    }

    public function rules(): array
    {
        return ['application_id' => ['required', 'integer', 'exists:applications,id'],
            'action' => ['required', Rule::in(['accept', 'interview', 'reject'])],
            'action_message' => [Rule::requiredIf(fn () => in_array($this->input('action'), ['accept', 'interview'], true)), 'nullable', 'string', 'max:5000']];
    }
}
