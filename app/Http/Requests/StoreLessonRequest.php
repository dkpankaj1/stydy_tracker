<?php

namespace App\Http\Requests;

use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Subject|null $subject */
        $subject = $this->route('subject');

        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('lessons', 'name')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()?->id)
                        ->where('subject_id', $subject?->id)
                ),
            ],
            'order_index' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
            'estimated_minutes' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
