<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ScheduleInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'employer'], true);
    }

    public function rules(): array
    {
        return ['application_id' => ['nullable', 'integer', 'exists:applications,id', 'required_without:offer_id'],
            'offer_id' => ['nullable', 'integer', 'exists:job_offers,id', 'required_without:application_id'],
            'interview_date' => ['required', 'date', 'after_or_equal:today'], 'interview_time' => ['required', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'], 'message' => ['nullable', 'string', 'max:5000']];
    }
}
