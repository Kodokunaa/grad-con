<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'alumni';
    }

    public function rules(): array
    {
        return ['school_name' => ['required', 'string', 'max:255'], 'degree' => ['required', Rule::in(['Primary', 'Secondary', 'Tertiary', 'Masteral', 'Doctorate'])], 'start_year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.now()->addYear()->year], 'end_year' => ['nullable', 'integer', 'digits:4', 'gte:start_year', 'max:'.now()->addYears(10)->year]];
    }
}
