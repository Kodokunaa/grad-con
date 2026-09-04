<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'alumni'; }
    public function rules(): array
    {
        return ['message' => ['nullable', 'string', 'max:5000'], 'agree_terms' => ['accepted'], 'resume' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120']];
    }
}
