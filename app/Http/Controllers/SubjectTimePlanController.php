<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSubjectTimePlanRequest;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubjectTimePlanController extends Controller
{
    public function __construct(private readonly ProgressService $progressService)
    {
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        $subject->load('lessons.topics');

        return view('subjects.time-plan', compact('subject'));
    }

    public function update(UpdateSubjectTimePlanRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        $minutesMap = collect($request->validated('topic_minutes'))
            ->mapWithKeys(fn ($value, $key) => [(int) $key => (int) ($value ?? 0)]);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Topic> $topics */
        $topics = Topic::query()
            ->where('user_id', $request->user()->id)
            ->where('subject_id', $subject->id)
            ->whereIn('id', $minutesMap->keys())
            ->get();

        DB::transaction(function () use ($topics, $minutesMap): void {
            foreach ($topics as $topic) {
                $newMinutes = $minutesMap->get((int) $topic->id, 0);

                if ((int) $topic->estimated_minutes !== $newMinutes) {
                    $topic->estimated_minutes = $newMinutes;
                    $topic->save();
                }
            }
        });

        $topics->load('checklistItems', 'lesson.subject.lessons.topics');

        foreach ($topics as $topic) {
            $this->progressService->recalculateForTopic($topic);
        }

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Estimated time plan updated successfully.');
    }
}
