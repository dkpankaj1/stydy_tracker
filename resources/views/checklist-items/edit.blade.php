@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Edit Checklist Item</h1>
<p class="text-body-secondary">Subject: {{ $subject->name }} | Lesson: {{ $lesson->name }} | Topic: {{ $topic->name }}</p>

<form method="post" action="{{ route('subjects.lessons.topics.checklist-items.update', [$subject, $lesson, $topic, $checklistItem]) }}" class="card card-body">
    @csrf
    @method('put')

    <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" id="title" name="title" value="{{ old('title', $checklistItem->title) }}" required maxlength="255">
        @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="order_index">Order Index</label>
        <input class="form-control" id="order_index" type="number" name="order_index" value="{{ old('order_index', $checklistItem->order_index) }}" min="0" max="100000">
        @error('order_index') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Update</button>
        <a class="btn btn-outline-secondary" href="{{ route('subjects.show', $subject) }}">Cancel</a>
    </div>
</form>
@endsection
