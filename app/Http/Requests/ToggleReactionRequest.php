<?php

namespace App\Http\Requests;

use App\Services\SocialFeedService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ToggleReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'alumni', 'alumni_officer'], true);
    }

    public function rules(): array
    {
        return ['reaction_type' => ['required', Rule::in(array_keys(SocialFeedService::REACTIONS))]];
    }
}
