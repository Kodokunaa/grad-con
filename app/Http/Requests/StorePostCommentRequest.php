<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePostCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'alumni', 'alumni_officer'], true);
    }

    public function rules(): array
    {
        return ['comment' => ['required', 'string', 'max:3000'], 'parent_comment_id' => ['nullable', 'integer', 'exists:post_comments,id']];
    }
}
