<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'alumni';
    }

    public function rules(): array
    {
        return ['company_name' => ['required', 'string', 'max:255'], 'job_title' => ['required', 'string', 'max:255'], 'employment_type' => ['nullable', 'string', 'max:100'], 'location' => ['nullable', 'string', 'max:255'], 'start_date' => ['required', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'job_description' => ['nullable', 'string', 'max:10000']];
    }
}
