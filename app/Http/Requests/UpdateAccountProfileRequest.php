<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['fullname' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)], 'birthdate' => ['nullable', 'date', 'before_or_equal:today'], 'gender' => ['nullable', Rule::in(['Male', 'Female'])], 'civil_status' => ['nullable', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])], 'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'], 'address' => [Rule::requiredIf($this->user()->role === 'employer'), 'nullable', 'string', 'max:1000'], 'has_multiple_branches' => ['nullable', 'boolean'], 'branch_location' => [Rule::requiredIf($this->user()->role === 'employer' && $this->boolean('has_multiple_branches')), 'nullable', 'string', 'max:1000'], 'indigenous_tribe' => ['nullable', 'string', 'max:150'], 'special_needs' => ['nullable', 'string', 'max:150'], 'employment_status' => ['nullable', Rule::in(['Employed', 'Unemployed'])], 'career_objective' => ['nullable', 'string', 'max:5000'], 'skills' => ['nullable', 'string', 'max:5000'], 'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']];
    }
}
