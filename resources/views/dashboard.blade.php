@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Study Dashboard</h1>
        <p class="text-body-secondary mb-0">Track momentum, pending revisions, and recent progress.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('subjects.create') }}" class="btn btn-primary">New Subject</a>
        <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">Open Subjects</a>
    </div>
</div>

@if ($activeSession)
    <div class="alert alert-warning border mb-4" role="alert">
        <strong>Active session running.</strong>
        Started at {{ optional($activeSession->started_at)->format('h:i A') ?? 'N/A' }}.
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary small text-uppercase mb-2">Subjects</p>
                <div class="d-flex align-items-end gap-2">
                    <h2 class="h3 mb-0">{{ $subjectsCount }}</h2>
                    <span class="text-success small">{{ $completedSubjectsCount }} completed</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary small text-uppercase mb-2">Average Progress</p>
                <h2 class="h3 mb-2">{{ number_format($averageProgress, 2) }}%</h2>
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($averageProgress) }}">
                    <div class="progress-bar" style="width: {{ max(0, min(100, $averageProgress)) }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary small text-uppercase mb-2">Study Minutes Today</p>
                <h2 class="h3 mb-0">{{ $todayStudyMinutes }}</h2>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary small text-uppercase mb-2">Due Revisions</p>
                <h2 class="h3 mb-1">{{ $dueRevisionsCount }}</h2>
                <p class="text-body-secondary small mb-0">This week: {{ $weeklyStudyMinutes }} min studied</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0">Recent Subjects</h3>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('subjects.index') }}">View all</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($recentSubjects as $subject)
                    <a href="{{ route('subjects.show', $subject->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $subject->name }}</div>
                            <small class="text-body-secondary">Updated {{ $subject->updated_at->diffForHumans() }}</small>
                        </div>
                        <span class="badge text-bg-light">{{ number_format((float) $subject->completion_percentage, 2) }}%</span>
                    </a>
                @empty
                    <div class="list-group-item text-body-secondary">No subjects yet. Create your first subject to start tracking.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0">Revision Inbox</h3>
                <span class="badge text-bg-warning">{{ $dueRevisionsCount }} due</span>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($dueRevisions as $revision)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $revision->topic?->name ?? 'Topic unavailable' }}</div>
                                <small class="text-body-secondary">
                                    {{ $revision->topic?->lesson?->name ?? 'Lesson unavailable' }}
                                    @if ($revision->topic?->subject?->name)
                                        | {{ $revision->topic->subject->name }}
                                    @endif
                                </small>
                            </div>
                            <span class="badge text-bg-light">{{ $revision->due_date?->format('M d') }}</span>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-body-secondary">No due revisions right now.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
