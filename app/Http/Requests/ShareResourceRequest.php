<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShareResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:link,email,embed',
            'permission_level' => 'required|string|in:view,download,edit',
            'expires_at' => 'nullable|date|after:now',
            'max_access_count' => 'nullable|integer|min:1',
            'emails' => 'required_if:type,email|nullable|array',
            'emails.*' => 'email',
        ];
    }
}
