@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Edit Subject</h1>

<form method="post" action="{{ route('subjects.update', $subject) }}" class="card card-body">
    @csrf
    @method('put')

    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" value="{{ old('name', $subject->name) }}" required maxlength="255">
        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $subject->description) }}</textarea>
        @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="estimated_minutes">Estimated Minutes</label>
        <input class="form-control" id="estimated_minutes" type="number" name="estimated_minutes" value="{{ old('estimated_minutes', $subject->estimated_minutes) }}" min="0" max="100000">
        @error('estimated_minutes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Update</button>
        <a class="btn btn-outline-secondary" href="{{ route('subjects.show', $subject) }}">Cancel</a>
    </div>
</form>
@endsection
