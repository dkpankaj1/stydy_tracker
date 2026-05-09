<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectTimePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'topic_minutes' => ['bail', 'required', 'array', 'min:1'],
            'topic_minutes.*' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
