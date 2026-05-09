@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Estimated Time Planner</h1>
        <p class="text-body-secondary mb-0">Subject: {{ $subject->name }}</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('subjects.show', $subject) }}">Back to Subject</a>
</div>

<form method="post" action="{{ route('subjects.time-plan.update', $subject) }}" class="card">
    @csrf
    @method('patch')

    <div class="card-body">
        <p class="text-body-secondary small mb-3">
            Set estimated minutes at topic level. Lesson and subject totals are auto-calculated.
        </p>

        @foreach ($subject->lessons->sortBy('order_index') as $lesson)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h6 mb-0">{{ $lesson->name }}</h2>
                    <span class="badge text-bg-light">Current total: {{ (int) $lesson->estimated_minutes }} min</span>
                </div>

                @if ($lesson->topics->isEmpty())
                    <p class="text-body-secondary small mb-0">No topics in this lesson yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Topic</th>
                                    <th style="width: 180px;">Estimated Minutes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lesson->topics->sortBy('order_index') as $topic)
                                    <tr>
                                        <td>{{ $topic->name }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                class="form-control form-control-sm"
                                                min="0"
                                                max="100000"
                                                name="topic_minutes[{{ $topic->id }}]"
                                                value="{{ old('topic_minutes.' . $topic->id, (int) $topic->estimated_minutes) }}"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach

        @error('topic_minutes')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        @error('topic_minutes.*')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="text-body-secondary small">Subject current total: {{ (int) $subject->estimated_minutes }} min</span>
        <button class="btn btn-primary" type="submit">Save Time Plan</button>
    </div>
</form>
@endsection
