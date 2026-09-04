<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'employer'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'profile_location' => ['nullable', 'string', 'max:255'],
            'branch_location' => ['nullable', 'string', 'max:255'],
            'job_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['required', 'string', 'max:20000'],
            'is_open' => ['nullable', Rule::in(['0', '1', 0, 1, 'on'])],
        ];
    }
}
