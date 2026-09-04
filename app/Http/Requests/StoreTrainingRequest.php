<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainingRequest extends FormRequest
{
    public const COURSES = ['BSIS', 'BSTM', 'BSHM', 'BSED Math', 'BSED English', 'BSED Science', 'BSNED', 'BPA', 'Open for All'];

    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'training_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'target_course' => ['required', Rule::in(self::COURSES)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:3072'],
        ];
    }
}
