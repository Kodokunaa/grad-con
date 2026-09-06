<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'alumni_officer'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['announcement', 'news', 'event'])],
            'content' => ['required', 'string', 'max:20000'],
            'post_start_date' => ['nullable', 'date'],
            'post_end_date' => ['nullable', 'date', 'after:post_start_date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:3072'],
        ];
    }
}
