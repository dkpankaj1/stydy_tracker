<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')->where(fn ($query) => $query->where('user_id', $this->user()?->id)),
            ],
            'description' => ['bail', 'nullable', 'string', 'max:5000'],
            'estimated_minutes' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
