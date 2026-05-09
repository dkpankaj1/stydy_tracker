<?php

use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectTimePlanController;
use App\Http\Controllers\TopicController;
use App\Models\Revision;
use App\Models\StudySession;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $userId = (int) Auth::id();

    $subjectsCount = Subject::query()->where('user_id', $userId)->count();
    $completedSubjectsCount = Subject::query()
        ->where('user_id', $userId)
        ->whereNotNull('completed_at')
        ->count();

    $averageProgress = (float) Subject::query()
        ->where('user_id', $userId)
        ->avg('progress_score');

    $todayStudyMinutes = (int) StudySession::query()
        ->where('user_id', $userId)
        ->whereDate('session_date', now()->toDateString())
        ->sum('actual_minutes');

    $weeklyStudyMinutes = (int) StudySession::query()
        ->where('user_id', $userId)
        ->whereBetween('session_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
        ->sum('actual_minutes');

    $dueRevisionsCount = Revision::query()
        ->where('user_id', $userId)
        ->whereDate('due_date', '<=', now()->toDateString())
        ->where('status', 'pending')
        ->count();

    $activeSession = StudySession::query()
        ->where('user_id', $userId)
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    $recentSubjects = Subject::query()
        ->where('user_id', $userId)
        ->orderByDesc('updated_at')
        ->limit(5)
        ->get(['id', 'name', 'progress_score', 'updated_at']);

    $dueRevisions = Revision::query()
        ->with('topic.lesson.subject')
        ->where('user_id', $userId)
        ->whereDate('due_date', '<=', now()->toDateString())
        ->where('status', 'pending')
        ->orderBy('due_date')
        ->limit(5)
        ->get();

    return view('dashboard', [
        'subjectsCount' => $subjectsCount,
        'completedSubjectsCount' => $completedSubjectsCount,
        'averageProgress' => $averageProgress,
        'todayStudyMinutes' => $todayStudyMinutes,
        'weeklyStudyMinutes' => $weeklyStudyMinutes,
        'dueRevisionsCount' => $dueRevisionsCount,
        'activeSession' => $activeSession,
        'recentSubjects' => $recentSubjects,
        'dueRevisions' => $dueRevisions,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])
    ->scopeBindings()
    ->group(function (): void {
        Route::resource('subjects', SubjectController::class);

        Route::get('subjects/{subject}/time-plan', [SubjectTimePlanController::class, 'edit'])
            ->name('subjects.time-plan.edit');
        Route::patch('subjects/{subject}/time-plan', [SubjectTimePlanController::class, 'update'])
            ->name('subjects.time-plan.update');
        Route::get('subjects/{subject}/export-report', [SubjectController::class, 'exportReport'])
            ->name('subjects.export-report');
        Route::post('subjects/{subject}/import-structure', [SubjectController::class, 'importStructure'])
            ->name('subjects.import-structure');

        Route::resource('subjects.lessons', LessonController::class)
            ->except(['index', 'show']);

        Route::resource('subjects.lessons.topics', TopicController::class)
            ->except(['index', 'show']);

        Route::resource('subjects.lessons.topics.checklist-items', ChecklistItemController::class)
            ->except(['index', 'show']);

        Route::post(
            'subjects/{subject}/lessons/{lesson}/topics/{topic}/checklist-items/{checklistItem}/toggle',
            [ChecklistItemController::class, 'toggle']
        )->name('checklist-items.toggle');
    });

require __DIR__.'/auth.php';
