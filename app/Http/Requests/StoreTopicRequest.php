<?php

namespace App\Http\Requests;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Lesson|null $lesson */
        $lesson = $this->route('lesson');

        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('topics', 'name')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()?->id)
                        ->where('lesson_id', $lesson?->id)
                ),
            ],
            'order_index' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
            'estimated_minutes' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
