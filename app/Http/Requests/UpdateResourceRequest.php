<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_id' => 'nullable|uuid|exists:folders,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|string|in:SCORM,H5P,PDF,Video,Image,Document,Link,Other',
            'file' => 'nullable|file|max:512000',
            'thumbnail_url' => 'nullable|url',
            'language' => 'nullable|string|max:10',
            'level' => 'nullable|string|max:50',
            'area' => 'nullable|string|max:100',
            'competencies' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'estimated_time_minutes' => 'nullable|integer|min:0',
            'author_name' => 'nullable|string|max:255',
            'status' => 'sometimes|string|in:Published,Draft,Archived',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'uuid|exists:tags,id',
        ];
    }
}
