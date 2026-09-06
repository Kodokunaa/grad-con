<?php

namespace App\Http\Requests;

use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('job');

        return $job instanceof Job && $this->user()?->can('update', $job);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:20000'],
            'requirements' => ['required', 'string', 'max:20000'],
            'target_course' => ['required', Rule::in(config('gradconn.courses'))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_open' => ['required', Rule::in(['0', '1', 0, 1])],
        ];
    }
}
