<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LogAlumniSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'employer';
    }

    public function rules(): array
    {
        return ['course_filter' => ['nullable', 'string', 'max:120'], 'batch_filter' => ['nullable', 'string', 'max:20'], 'skills_search' => ['nullable', 'string', 'max:255'], 'result_count' => ['required', 'integer', 'min:0']];
    }
}
