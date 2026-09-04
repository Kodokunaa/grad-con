<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendApplicantResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return ['company_email' => ['required', 'email:rfc', 'max:254']];
    }
}
