<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateOwnProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'alumni';
    }

    public function rules(): array
    {
        return ['fullname' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)], 'course' => ['nullable', 'string', 'max:120'], 'batch_year' => ['nullable', 'string', 'max:20'], 'employment_status' => ['nullable', Rule::in(['Employed', 'Unemployed'])], 'job_aligned' => [Rule::requiredIf(fn () => $this->input('employment_status') === 'Employed'), 'nullable', Rule::in(['Yes', 'No'])]];
    }
}
