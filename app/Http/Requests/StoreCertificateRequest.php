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
        return ['certificate_name' => ['required', 'string', 'max:255'], 'issue_date' => ['nullable', 'date', 'before_or_equal:today'], 'certificate_image' => ['required', 'file', 'extensions:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf,application/x-pdf', 'max:5120']];
    }
}
