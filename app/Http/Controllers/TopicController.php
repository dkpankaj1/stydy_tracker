<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\ProgressService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TopicController extends Controller
{
    public function __construct(private readonly ProgressService $progressService)
    {
    }

    public function create(Subject $subject, Lesson $lesson): View
    {
        $this->authorize('update', $lesson);

        return view('topics.create', compact('subject', 'lesson'));
    }

    public function store(StoreTopicRequest $request, Subject $subject, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        try {
            Topic::query()->create([
                'user_id' => $request->user()->id,
                'subject_id' => $subject->id,
                'lesson_id' => $lesson->id,
                'name' => $request->string('name')->toString(),
                'order_index' => (int) $request->input('order_index', 0),
                'estimated_minutes' => (int) $request->input('estimated_minutes', 0),
                'actual_minutes' => 0,
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'A topic with this name already exists in this lesson.']);
        }

        $subject->load('lessons.topics');
        $this->progressService->recalculateForSubject($subject);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Topic created successfully.');
    }

    public function edit(Subject $subject, Lesson $lesson, Topic $topic): View
    {
        $this->authorize('update', $topic);

        return view('topics.edit', compact('subject', 'lesson', 'topic'));
    }

    public function update(UpdateTopicRequest $request, Subject $subject, Lesson $lesson, Topic $topic): RedirectResponse
    {
        $this->authorize('update', $topic);

        try {
            $topic->update([
                'name' => $request->string('name')->toString(),
                'order_index' => (int) $request->input('order_index', 0),
                'estimated_minutes' => (int) $request->input('estimated_minutes', 0),
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'A topic with this name already exists in this lesson.']);
        }

        $topic->load('checklistItems', 'lesson.subject.lessons.topics');
        $this->progressService->recalculateForTopic($topic);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Topic updated successfully.');
    }

    public function destroy(Subject $subject, Lesson $lesson, Topic $topic): RedirectResponse
    {
        $this->authorize('delete', $topic);

        $topic->delete();

        $subject->load('lessons.topics');
        $this->progressService->recalculateForSubject($subject);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Topic deleted successfully.');
    }
}
