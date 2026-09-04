<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'alumni';
    }

    public function rules(): array
    {
        return ['certificate_name' => ['required', 'string', 'max:255'], 'issue_date' => ['nullable', 'date', 'before_or_equal:today'], 'certificate_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072']];
    }
}
