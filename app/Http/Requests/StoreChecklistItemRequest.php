<?php

namespace App\Http\Requests;

use App\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Topic|null $topic */
        $topic = $this->route('topic');

        return [
            'title' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('checklist_items', 'title')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()?->id)
                        ->where('topic_id', $topic?->id)
                ),
            ],
            'order_index' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
