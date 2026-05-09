<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSubjectStructureRequest;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\ChecklistItem;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\ProgressService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubjectController extends Controller
{
    public function __construct(private readonly ProgressService $progressService)
    {
    }

    public function index(): View
    {
        $subjects = Subject::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->paginate(12);

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        try {
            Subject::query()->create([
                'user_id' => $request->user()->id,
                'name' => $request->string('name')->toString(),
                'description' => $request->string('description')->toString() ?: null,
                'estimated_minutes' => (int) $request->input('estimated_minutes', 0),
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'You already have a subject with this name.']);
        }

        return redirect()
            ->route('subjects.index')
            ->with('status', 'Subject created successfully.');
    }

    public function show(Subject $subject): View
    {
        $this->authorize('view', $subject);

        $subject->load('lessons.topics.checklistItems');

        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        return view('subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        try {
            $subject->update([
                'name' => $request->string('name')->toString(),
                'description' => $request->string('description')->toString() ?: null,
                'estimated_minutes' => (int) $request->input('estimated_minutes', 0),
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'You already have a subject with this name.']);
        }

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('delete', $subject);
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('status', 'Subject deleted successfully.');
    }

    public function exportReport(Subject $subject): StreamedResponse
    {
        $this->authorize('view', $subject);

        $subject->load('lessons.topics.checklistItems');

        $filename = sprintf(
            'subject-report-%s-%s.csv',
            $subject->id,
            now()->format('Ymd_His')
        );

        return response()->streamDownload(function () use ($subject): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'Subject ID',
                'Subject Name',
                'Subject Progress %',
                'Lesson',
                'Lesson Progress %',
                'Topic',
                'Topic Progress %',
                'Checklist Item',
                'Checklist Status',
                'Estimated Minutes',
                'Actual Minutes',
            ]);

            foreach ($subject->lessons as $lesson) {
                if ($lesson->topics->isEmpty()) {
                    fputcsv($handle, [
                        $subject->id,
                        $subject->name,
                        number_format((float) $subject->progress_score, 2, '.', ''),
                        $lesson->name,
                        number_format((float) $lesson->progress_score, 2, '.', ''),
                        '',
                        '',
                        '',
                        '',
                        (int) $lesson->estimated_minutes,
                        (int) $lesson->actual_minutes,
                    ]);

                    continue;
                }

                foreach ($lesson->topics as $topic) {
                    if ($topic->checklistItems->isEmpty()) {
                        fputcsv($handle, [
                            $subject->id,
                            $subject->name,
                            number_format((float) $subject->progress_score, 2, '.', ''),
                            $lesson->name,
                            number_format((float) $lesson->progress_score, 2, '.', ''),
                            $topic->name,
                            number_format((float) $topic->progress_score, 2, '.', ''),
                            '',
                            '',
                            (int) $topic->estimated_minutes,
                            (int) $topic->actual_minutes,
                        ]);

                        continue;
                    }

                    foreach ($topic->checklistItems as $item) {
                        fputcsv($handle, [
                            $subject->id,
                            $subject->name,
                            number_format((float) $subject->progress_score, 2, '.', ''),
                            $lesson->name,
                            number_format((float) $lesson->progress_score, 2, '.', ''),
                            $topic->name,
                            number_format((float) $topic->progress_score, 2, '.', ''),
                            $item->title,
                            $item->is_completed ? 'Completed' : 'Pending',
                            (int) $topic->estimated_minutes,
                            (int) $topic->actual_minutes,
                        ]);
                    }
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStructure(ImportSubjectStructureRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        $file = $request->file('csv_file');
        if ($file === null) {
            return back()->withErrors(['csv_file' => 'CSV file is required.']);
        }

        $filePath = $file->getRealPath();
        if ($filePath === false || $filePath === '') {
            return back()->withErrors(['csv_file' => 'Unable to resolve uploaded CSV file path.']);
        }

        $delimiter = $this->detectCsvDelimiter($filePath);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return back()->withErrors(['csv_file' => 'Unable to read uploaded CSV file.']);
        }

        $line = 0;
        $skipped = 0;
        $createdLessons = 0;
        $createdTopics = 0;
        $createdChecklistItems = 0;
        $touchedTopicIds = collect();
        $headerIndexes = [
            'subject' => -1,
            'lesson' => -1,
            'topic' => -1,
            'checklist' => -1,
        ];
        $hasHeader = false;

        try {
            DB::transaction(function () use (
                $handle,
                $delimiter,
                &$line,
                &$skipped,
                &$createdLessons,
                &$createdTopics,
                &$createdChecklistItems,
                $subject,
                $request,
                &$headerIndexes,
                &$hasHeader,
                $touchedTopicIds
            ): void {
                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $line++;

                    if ($row === [] || $row === [null]) {
                        continue;
                    }

                    $values = array_map(fn ($value): string => $this->normalizeCsvValue($value), $row);

                    if ($line === 1) {
                        $headerValues = array_map(fn (string $value): string => $this->normalizeCsvHeader($value), $values);
                        $subjectIdx = $this->findHeaderIndex($headerValues, ['subject']);
                        $lessonIdx = $this->findHeaderIndex($headerValues, ['lesson', 'lession']);
                        $topicIdx = $this->findHeaderIndex($headerValues, ['topic']);
                        $checklistIdx = $this->findHeaderIndex($headerValues, ['checklist', 'checklistitem', 'checklistitems']);

                        if ($topicIdx >= 0 && $checklistIdx >= 0 && $lessonIdx >= 0) {
                            $hasHeader = true;
                            $headerIndexes['subject'] = $subjectIdx;
                            $headerIndexes['lesson'] = $lessonIdx;
                            $headerIndexes['topic'] = $topicIdx;
                            $headerIndexes['checklist'] = $checklistIdx;
                            continue;
                        }
                    }

                    if (! $hasHeader) {
                        if (count($values) < 4) {
                            $skipped++;
                            continue;
                        }

                        $subjectName = $values[0] ?? '';
                        $lessonName = $values[1] ?? '';
                        $topicName = $values[2] ?? '';
                        $checklistTitle = $values[3] ?? '';
                    } else {
                        $subjectName = $headerIndexes['subject'] >= 0 ? ($values[$headerIndexes['subject']] ?? '') : '';
                        $lessonName = $values[$headerIndexes['lesson']] ?? '';
                        $topicName = $values[$headerIndexes['topic']] ?? '';
                        $checklistTitle = $values[$headerIndexes['checklist']] ?? '';
                    }

                    if ($lessonName === '' || $topicName === '' || $checklistTitle === '') {
                        $skipped++;
                        continue;
                    }

                    if ($subjectName !== '' && mb_strtolower($subjectName) !== mb_strtolower($subject->name)) {
                        $skipped++;
                        continue;
                    }

                    $lesson = Lesson::query()->firstOrCreate(
                        [
                            'user_id' => (int) $request->user()->id,
                            'subject_id' => $subject->id,
                            'name' => $lessonName,
                        ],
                        [
                            'order_index' => 0,
                            'estimated_minutes' => 0,
                            'actual_minutes' => 0,
                        ]
                    );

                    if ($lesson->wasRecentlyCreated) {
                        $createdLessons++;
                    }

                    $topic = Topic::query()->firstOrCreate(
                        [
                            'user_id' => (int) $request->user()->id,
                            'subject_id' => $subject->id,
                            'lesson_id' => $lesson->id,
                            'name' => $topicName,
                        ],
                        [
                            'order_index' => 0,
                            'estimated_minutes' => 0,
                            'actual_minutes' => 0,
                        ]
                    );

                    if ($topic->wasRecentlyCreated) {
                        $createdTopics++;
                    }

                    $checklistItem = ChecklistItem::query()->firstOrCreate(
                        [
                            'user_id' => (int) $request->user()->id,
                            'subject_id' => $subject->id,
                            'lesson_id' => $lesson->id,
                            'topic_id' => $topic->id,
                            'title' => $checklistTitle,
                        ],
                        [
                            'order_index' => 0,
                            'is_completed' => false,
                        ]
                    );

                    if ($checklistItem->wasRecentlyCreated) {
                        $createdChecklistItems++;
                    }

                    $touchedTopicIds->push((int) $topic->id);
                }
            });
        } finally {
            fclose($handle);
        }

        $uniqueTopicIds = $touchedTopicIds->unique()->values();
        if ($uniqueTopicIds->isNotEmpty()) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, Topic> $topics */
            $topics = Topic::query()->whereIn('id', $uniqueTopicIds)->get();
            $topics->load('checklistItems', 'lesson.subject.lessons.topics');

            foreach ($topics as $topic) {
                $this->progressService->recalculateForTopic($topic);
            }
        }

        if ($line === 0) {
            return back()->withErrors(['csv_file' => 'The uploaded CSV is empty.']);
        }

        if ($createdLessons === 0 && $createdTopics === 0 && $createdChecklistItems === 0) {
            return back()->withErrors([
                'csv_file' => 'No rows were imported. Ensure the file uses comma, semicolon, tab, or pipe delimiter and that the subject column matches "'.$subject->name.'" (or leave subject blank).',
            ]);
        }

        $message = "Import complete. {$createdLessons} lessons created, {$createdTopics} topics created, {$createdChecklistItems} checklist items created, {$skipped} rows skipped.";

        return redirect()
            ->route('subjects.show', $subject)
            ->with('status', $message);
    }

    private function detectCsvDelimiter(string $filePath): string
    {
        $firstLine = '';
        $handle = fopen($filePath, 'r');
        if ($handle !== false) {
            $firstLine = (string) fgets($handle);
            fclose($handle);
        }

        if ($firstLine === '') {
            return ',';
        }

        $candidates = [',', ';', "\t", '|'];
        $selected = ',';
        $maxCount = -1;

        foreach ($candidates as $candidate) {
            $count = substr_count($firstLine, $candidate);
            if ($count > $maxCount) {
                $maxCount = $count;
                $selected = $candidate;
            }
        }

        return $selected;
    }

    private function normalizeCsvValue(mixed $value): string
    {
        $text = trim((string) $value);

        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            $text = substr($text, 3);
        }

        return $text;
    }

    private function normalizeCsvHeader(string $value): string
    {
        return str_replace([' ', '_', '-'], '', mb_strtolower($this->normalizeCsvValue($value)));
    }

    /**
     * @param array<int, string> $headerValues
     * @param array<int, string> $aliases
     */
    private function findHeaderIndex(array $headerValues, array $aliases): int
    {
        foreach ($aliases as $alias) {
            $index = array_search($alias, $headerValues, true);
            if ($index !== false) {
                return (int) $index;
            }
        }

        return -1;
    }
}
