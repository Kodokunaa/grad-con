<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendJobOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'employer';
    }

    public function rules(): array
    {
        return ['email_alumni_id' => ['required', 'integer', 'exists:users,id'], 'email_subject' => ['nullable', 'string', 'max:255'], 'email_message' => ['required', 'string', 'max:5000']];
    }
}
