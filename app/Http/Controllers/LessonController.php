<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Subject;
use App\Services\ProgressService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(private readonly ProgressService $progressService)
    {
    }

    public function create(Subject $subject): View
    {
        $this->authorize('view', $subject);

        return view('lessons.create', compact('subject'));
    }

    public function store(StoreLessonRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('view', $subject);

        try {
            Lesson::query()->create([
                'user_id' => $request->user()->id,
                'subject_id' => $subject->id,
                'name' => $request->string('name')->toString(),
                'order_index' => (int) $request->input('order_index', 0),
                'estimated_minutes' => (int) $request->input('estimated_minutes', 0),
                'actual_minutes' => 0,
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'A lesson with this name already exists in this subject.']);
        }

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Lesson created successfully.');
    }

    public function edit(Subject $subject, Lesson $lesson): View
    {
        $this->authorize('update', $lesson);

        return view('lessons.edit', compact('subject', 'lesson'));
    }

    public function update(UpdateLessonRequest $request, Subject $subject, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        try {
            $lesson->update([
                'name' => $request->string('name')->toString(),
                'order_index' => (int) $request->input('order_index', 0),
                'estimated_minutes' => (int) $request->input('estimated_minutes', 0),
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'A lesson with this name already exists in this subject.']);
        }

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Lesson updated successfully.');
    }

    public function destroy(Subject $subject, Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        $subject->load('lessons.topics');
        $this->progressService->recalculateForSubject($subject);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Lesson deleted successfully.');
    }
}
