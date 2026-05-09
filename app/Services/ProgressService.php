<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;

class ProgressService
{
    private function decimal(float|int $value): float
    {
        return round((float) $value, 2);
    }

    public function recalculateForTopic(Topic $topic): void
    {
        $totalItems = $topic->checklistItems->count();
        $completedItems = $topic->checklistItems->where('is_completed', true)->count();

        $completionPercentage = $totalItems > 0
            ? round(($completedItems / $totalItems) * 100, 2)
            : 0;

        $timePercentage = $topic->estimated_minutes > 0
            ? min(round(($topic->actual_minutes / $topic->estimated_minutes) * 100, 2), 100)
            : 0;

        $topic->setAttribute('completion_percentage', $this->decimal($completionPercentage));
        $topic->setAttribute('time_percentage', $this->decimal($timePercentage));
        $topic->setAttribute('progress_score', $this->decimal(($completionPercentage + $timePercentage) / 2));
        $topic->completed_at = $completionPercentage >= 100 ? now() : null;
        $topic->save();

        $topic->lesson?->load('topics');
        if ($topic->lesson !== null) {
            $this->recalculateForLesson($topic->lesson);
        }
    }

    public function recalculateForLesson(Lesson $lesson): void
    {
        $totals = $lesson->topics->reduce(function (array $carry, Topic $topic): array {
            $weight = max($topic->estimated_minutes, 1);
            $carry['weight'] += $weight;
            $carry['completion'] += $topic->completion_percentage * $weight;
            $carry['time'] += $topic->time_percentage * $weight;
            $carry['actual_minutes'] += $topic->actual_minutes;
            $carry['estimated_minutes'] += $topic->estimated_minutes;

            return $carry;
        }, [
            'weight' => 0,
            'completion' => 0,
            'time' => 0,
            'actual_minutes' => 0,
            'estimated_minutes' => 0,
        ]);

        $weight = max($totals['weight'], 1);
        $lessonCompletion = round($totals['completion'] / $weight, 2);
        $lessonTime = round($totals['time'] / $weight, 2);

        $lesson->setAttribute('completion_percentage', $this->decimal($lessonCompletion));
        $lesson->setAttribute('time_percentage', $this->decimal($lessonTime));
        $lesson->setAttribute('progress_score', $this->decimal(($lessonCompletion + $lessonTime) / 2));
        $lesson->actual_minutes = $totals['actual_minutes'];
        $lesson->estimated_minutes = $totals['estimated_minutes'];
        $lesson->completed_at = $lesson->completion_percentage >= 100 ? now() : null;
        $lesson->save();

        $lesson->subject?->load('lessons.topics');
        if ($lesson->subject !== null) {
            $this->recalculateForSubject($lesson->subject);
        }
    }

    public function recalculateForSubject(Subject $subject): void
    {
        $topics = $subject->lessons->flatMap->topics;

        $totals = $topics->reduce(function (array $carry, Topic $topic): array {
            $weight = max($topic->estimated_minutes, 1);
            $carry['weight'] += $weight;
            $carry['completion'] += $topic->completion_percentage * $weight;
            $carry['time'] += $topic->time_percentage * $weight;
            $carry['actual_minutes'] += $topic->actual_minutes;
            $carry['estimated_minutes'] += $topic->estimated_minutes;

            return $carry;
        }, [
            'weight' => 0,
            'completion' => 0,
            'time' => 0,
            'actual_minutes' => 0,
            'estimated_minutes' => 0,
        ]);

        $weight = max($totals['weight'], 1);
        $subjectCompletion = round($totals['completion'] / $weight, 2);
        $subjectTime = round($totals['time'] / $weight, 2);

        $subject->setAttribute('completion_percentage', $this->decimal($subjectCompletion));
        $subject->setAttribute('time_percentage', $this->decimal($subjectTime));
        $subject->setAttribute('progress_score', $this->decimal(($subjectCompletion + $subjectTime) / 2));
        $subject->actual_minutes = $totals['actual_minutes'];
        $subject->estimated_minutes = $totals['estimated_minutes'];
        $subject->completed_at = $subject->completion_percentage >= 100 ? now() : null;
        $subject->save();
    }
}
