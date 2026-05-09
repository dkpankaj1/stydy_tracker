@extends('layouts.app')

@section('content')
@php($isReadMode = (bool) (config('app.read_mode') || auth()->user()?->read_mode_enabled))
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Subjects</h1>
    @unless ($isReadMode)
        <a href="{{ route('subjects.create') }}" class="btn btn-primary">New Subject</a>
    @endunless
</div>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse ($subjects as $subject)
            <a href="{{ route('subjects.show', $subject) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>{{ $subject->name }}</span>
                <span>{{ number_format((float) $subject->completion_percentage, 2) }}%</span>
            </a>
        @empty
            <div class="list-group-item text-body-secondary">No subjects yet.</div>
        @endforelse
    </div>
</div>

<div class="mt-3">
    {{ $subjects->links() }}
</div>
@endsection
