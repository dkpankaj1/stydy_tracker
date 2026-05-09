<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChecklistItemRequest;
use App\Http\Requests\UpdateChecklistItemRequest;
use App\Models\ChecklistItem;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\ChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChecklistItemController extends Controller
{
    public function __construct(private readonly ChecklistService $checklistService)
    {
    }

    public function create(Subject $subject, Lesson $lesson, Topic $topic): View
    {
        $this->authorize('update', $topic);

        return view('checklist-items.create', compact('subject', 'lesson', 'topic'));
    }

    public function store(StoreChecklistItemRequest $request, Subject $subject, Lesson $lesson, Topic $topic): RedirectResponse
    {
        $this->authorize('update', $topic);

        ChecklistItem::query()->create([
            'user_id' => $request->user()->id,
            'subject_id' => $subject->id,
            'lesson_id' => $lesson->id,
            'topic_id' => $topic->id,
            'title' => $request->string('title')->toString(),
            'order_index' => (int) $request->input('order_index', 0),
            'is_completed' => false,
        ]);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Checklist item added.');
    }

    public function edit(Subject $subject, Lesson $lesson, Topic $topic, ChecklistItem $checklistItem): View
    {
        $this->authorize('update', $checklistItem);

        return view('checklist-items.edit', compact('subject', 'lesson', 'topic', 'checklistItem'));
    }

    public function update(UpdateChecklistItemRequest $request, Subject $subject, Lesson $lesson, Topic $topic, ChecklistItem $checklistItem): RedirectResponse
    {
        $this->authorize('update', $checklistItem);

        $checklistItem->update([
            'title' => $request->string('title')->toString(),
            'order_index' => (int) $request->input('order_index', 0),
        ]);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Checklist item updated.');
    }

    public function destroy(Subject $subject, Lesson $lesson, Topic $topic, ChecklistItem $checklistItem): RedirectResponse
    {
        $this->authorize('delete', $checklistItem);

        $checklistItem->delete();

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Checklist item deleted.');
    }

    public function toggle(
        Subject $subject,
        Lesson $lesson,
        Topic $topic,
        ChecklistItem $checklistItem,
    ): RedirectResponse {
        $this->authorize('update', $checklistItem);

        $this->checklistService->toggle((int) Auth::id(), $checklistItem);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Checklist item status updated.');
    }
}
