@extends('layouts.app')

@section('content')
@php
    $lessonCount = $subject->lessons->count();
    $topicCount = $subject->lessons->sum(fn ($lesson) => $lesson->topics->count());
    $checklistCount = $subject->lessons->sum(fn ($lesson) => $lesson->topics->sum(fn ($topic) => $topic->checklistItems->count()));
    $completedChecklistCount = $subject->lessons->sum(fn ($lesson) => $lesson->topics->sum(fn ($topic) => $topic->checklistItems->where('is_completed', true)->count()));
@endphp

<div class="subject-view tb-page">
    <section class="subject-hero tb-hero mb-4">
        <div class="subject-hero__head tb-hero__head">
            <div>
                <p class="subject-hero__label tb-kicker mb-1">Subject Overview</p>
                <h1 class="subject-hero__title tb-title mb-2">{{ $subject->name }}</h1>
                <p class="subject-hero__progress tb-subtitle mb-0">Progress {{ number_format((float) $subject->progress_score, 2) }}%</p>
            </div>
            <div class="subject-hero__actions">
                <a class="btn btn-outline-secondary" href="{{ route('subjects.time-plan.edit', $subject) }}">Time Planner</a>
                <a class="btn btn-outline-secondary" href="{{ route('subjects.export-report', $subject) }}">Export CSV Report</a>
                <a class="btn btn-primary" href="{{ route('subjects.lessons.create', $subject) }}">New Lesson</a>
                <a class="btn btn-outline-primary" href="{{ route('subjects.edit', $subject) }}">Edit Subject</a>
                <button
                    class="btn btn-outline-danger"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteConfirmModal"
                    data-delete-action="{{ route('subjects.destroy', $subject) }}"
                    data-delete-title="Delete Subject"
                    data-delete-message="Delete subject '{{ $subject->name }}' and all related data?"
                    data-delete-confirm="Delete Subject"
                >
                    Delete
                </button>
            </div>
        </div>

        <div class="subject-hero__stats tb-stat-grid">
            <article class="subject-stat tb-stat-card">
                <span>Lessons</span>
                <strong>{{ $lessonCount }}</strong>
            </article>
            <article class="subject-stat tb-stat-card">
                <span>Topics</span>
                <strong>{{ $topicCount }}</strong>
            </article>
            <article class="subject-stat tb-stat-card">
                <span>Checklist Items</span>
                <strong>{{ $checklistCount }}</strong>
            </article>
            <article class="subject-stat tb-stat-card">
                <span>Completed Items</span>
                <strong>{{ $completedChecklistCount }}</strong>
            </article>
        </div>

        @if ($subject->description)
            <div class="subject-hero__description tb-note">{{ $subject->description }}</div>
        @endif
    </section>

    <section class="card subject-import tb-card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-3">
                <div>
                    <h2 class="h5 mb-1 tb-card-title">Import Structure CSV</h2>
                    <p class="text-body-secondary mb-0">Columns: <strong>subject, lesson, topic, checklist</strong> (or <strong>lession</strong>).</p>
                </div>
                <span class="subject-import__sample">{{ $subject->name }}, Lesson 1, Topic A, Checklist item 1</span>
            </div>

            <form method="post" action="{{ route('subjects.import-structure', $subject) }}" enctype="multipart/form-data" class="row g-2 align-items-center">
                @csrf
                <div class="col-12 col-lg-9">
                    <input class="form-control" type="file" name="csv_file" accept=".csv,text/csv,text/plain" required>
                    @error('csv_file')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-lg-3">
                    <button class="btn btn-primary w-100" type="submit">Import Structure</button>
                </div>
            </form>
        </div>
    </section>

    @if ($subject->lessons->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
            <button id="openAllLessons" class="btn btn-sm btn-outline-secondary" type="button">Open All</button>
            <button id="collapseAllLessons" class="btn btn-sm btn-outline-secondary" type="button">Collapse All</button>
        </div>

        <div class="accordion" id="lessonsAccordion">
            @foreach ($subject->lessons as $lesson)
                <div class="accordion-item lesson-block tb-card mb-3">
                    <h2 class="accordion-header" id="lessonHeading{{ $lesson->id }}">
                        <button
                            class="accordion-button"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#lessonCollapse{{ $lesson->id }}"
                            aria-expanded="true"
                            aria-controls="lessonCollapse{{ $lesson->id }}"
                        >
                            <span class="d-flex flex-column text-start">
                                <span class="h5 mb-1 tb-card-title">{{ $lesson->name }}</span>
                                <span class="text-body-secondary">{{ number_format((float) $lesson->progress_score, 2) }}% complete</span>
                            </span>
                        </button>
                    </h2>
                    <div
                        id="lessonCollapse{{ $lesson->id }}"
                        class="accordion-collapse collapse show"
                        aria-labelledby="lessonHeading{{ $lesson->id }}"
                    >
                        <div class="accordion-body">
                            <div class="lesson-block__head mb-3">
                                <div class="progress lesson-block__progress" role="progressbar" aria-label="Lesson progress" aria-valuenow="{{ (int) $lesson->progress_score }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ min(100, max(0, (float) $lesson->progress_score)) }}%"></div>
                                </div>
                                <div class="lesson-block__actions">
                                    <a class="btn btn-sm btn-dark" href="{{ route('subjects.lessons.topics.create', [$subject, $lesson]) }}">New Topic</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('subjects.lessons.edit', [$subject, $lesson]) }}">Edit</a>
                                    <button
                                        class="btn btn-sm btn-outline-danger"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteConfirmModal"
                                        data-delete-action="{{ route('subjects.lessons.destroy', [$subject, $lesson]) }}"
                                        data-delete-title="Delete Lesson"
                                        data-delete-message="Delete lesson '{{ $lesson->name }}' and all nested topics/checklist items?"
                                        data-delete-confirm="Delete Lesson"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>

                            @if ($lesson->topics->isEmpty())
                                <div class="alert alert-info mb-0">No topics yet.</div>
                            @else
                                <div class="topic-table-wrap">
                                    <table class="table tb-nested-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 30%;">Topic</th>
                                                <th style="width: 20%;">Progress</th>
                                                <th style="width: 35%;">Checklist</th>
                                                <th style="width: 15%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lesson->topics as $topic)
                                                <tr>
                                                    <td>
                                                        <h3 class="h6 mb-1 tb-subcard-title">{{ $topic->name }}</h3>
                                                        <p class="text-body-secondary small mb-2">{{ $topic->checklistItems->count() }} items</p>
                                                        <a class="btn btn-sm btn-primary" href="{{ route('subjects.lessons.topics.checklist-items.create', [$subject, $lesson, $topic]) }}">Add Checklist Item</a>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            <span class="badge text-primary bg-primary-subtle border border-primary-subtle">{{ number_format((float) $topic->progress_score, 2) }}%</span>
                                                        </div>
                                                        <div class="progress tb-topic-progress" role="progressbar" aria-label="Topic progress" aria-valuenow="{{ (int) $topic->progress_score }}" aria-valuemin="0" aria-valuemax="100">
                                                            <div class="progress-bar" style="width: {{ min(100, max(0, (float) $topic->progress_score)) }}%"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="tb-checklist-stack">
                                                            @forelse ($topic->checklistItems as $item)
                                                                <div class="tb-checklist-line">
                                                                    <form method="post" action="{{ route('checklist-items.toggle', [$subject, $lesson, $topic, $item]) }}">
                                                                        @csrf
                                                                        <button class="btn btn-sm {{ $item->is_completed ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">
                                                                            {{ $item->is_completed ? 'Done' : 'Pending' }}
                                                                        </button>
                                                                    </form>
                                                                    <span class="checklist-item-row__title {{ $item->is_completed ? 'is-done' : '' }}">{{ $item->title }}</span>
                                                                    <a class="btn btn-sm btn-outline-secondary ms-auto" href="{{ route('subjects.lessons.topics.checklist-items.edit', [$subject, $lesson, $topic, $item]) }}">Edit</a>
                                                                    <button
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteConfirmModal"
                                                                        data-delete-action="{{ route('subjects.lessons.topics.checklist-items.destroy', [$subject, $lesson, $topic, $item]) }}"
                                                                        data-delete-title="Delete Checklist Item"
                                                                        data-delete-message="Delete checklist item '{{ $item->title }}'?"
                                                                        data-delete-confirm="Delete Item"
                                                                    >
                                                                        Delete
                                                                    </button>
                                                                </div>
                                                            @empty
                                                                <span class="text-body-secondary small">No checklist items yet.</span>
                                                            @endforelse
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('subjects.lessons.topics.edit', [$subject, $lesson, $topic]) }}">Edit Topic</a>
                                                            <button
                                                                class="btn btn-sm btn-outline-danger w-100"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmModal"
                                                                data-delete-action="{{ route('subjects.lessons.topics.destroy', [$subject, $lesson, $topic]) }}"
                                                                data-delete-title="Delete Topic"
                                                                data-delete-message="Delete topic '{{ $topic->name }}' and its checklist items?"
                                                                data-delete-confirm="Delete Topic"
                                                            >
                                                                Delete Topic
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">No lessons yet. Create your first lesson to continue.</div>
    @endif
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteConfirmModalMessage">Are you sure you want to delete this item?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" id="deleteConfirmForm" action="{{ route('subjects.destroy', $subject) }}">
                    @csrf
                    @method('delete')
                    <button class="btn btn-danger" id="deleteConfirmSubmit" type="submit">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('deleteConfirmModal');
        const modalTitle = document.getElementById('deleteConfirmModalLabel');
        const modalMessage = document.getElementById('deleteConfirmModalMessage');
        const modalForm = document.getElementById('deleteConfirmForm');
        const modalSubmit = document.getElementById('deleteConfirmSubmit');

        if (modal && modalTitle && modalMessage && modalForm && modalSubmit) {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                if (!button) {
                    return;
                }

                const action = button.getAttribute('data-delete-action') || modalForm.getAttribute('action') || '#';
                const title = button.getAttribute('data-delete-title') || 'Confirm Delete';
                const message = button.getAttribute('data-delete-message') || 'Are you sure you want to delete this item?';
                const confirmText = button.getAttribute('data-delete-confirm') || 'Delete';

                modalForm.setAttribute('action', action);
                modalTitle.textContent = title;
                modalMessage.textContent = message;
                modalSubmit.textContent = confirmText;
            });
        }

        const lessonsAccordion = document.getElementById('lessonsAccordion');
        const openAllButton = document.getElementById('openAllLessons');
        const collapseAllButton = document.getElementById('collapseAllLessons');

        if (!lessonsAccordion || !openAllButton || !collapseAllButton) {
            return;
        }

        const lessonPanels = lessonsAccordion.querySelectorAll('.accordion-collapse');
        const getTrigger = function (panelId) {
            return lessonsAccordion.querySelector('[data-bs-target="#' + panelId + '"]');
        };

        const showPanel = function (panel) {
            if (window.bootstrap?.Collapse) {
                window.bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false }).show();
                return;
            }

            panel.classList.add('show');
            const trigger = getTrigger(panel.id);
            trigger?.setAttribute('aria-expanded', 'true');
            trigger?.classList.remove('collapsed');
        };

        const hidePanel = function (panel) {
            if (window.bootstrap?.Collapse) {
                window.bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false }).hide();
                return;
            }

            panel.classList.remove('show');
            const trigger = getTrigger(panel.id);
            trigger?.setAttribute('aria-expanded', 'false');
            trigger?.classList.add('collapsed');
        };

        openAllButton.addEventListener('click', function () {
            lessonPanels.forEach(function (panel) {
                showPanel(panel);
            });
        });

        collapseAllButton.addEventListener('click', function () {
            lessonPanels.forEach(function (panel) {
                hidePanel(panel);
            });
        });
    });
</script>
@endsection
