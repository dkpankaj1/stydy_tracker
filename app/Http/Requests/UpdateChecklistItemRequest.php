<?php

namespace App\Http\Requests;

use App\Models\ChecklistItem;
use App\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Topic|null $topic */
        $topic = $this->route('topic');
        /** @var ChecklistItem|null $checklistItem */
        $checklistItem = $this->route('checklistItem');

        return [
            'title' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('checklist_items', 'title')
                    ->where(fn ($query) => $query
                        ->where('user_id', $this->user()?->id)
                        ->where('topic_id', $topic?->id))
                    ->ignore($checklistItem?->id),
            ],
            'order_index' => ['bail', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
