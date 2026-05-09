@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Create Subject</h1>

<form method="post" action="{{ route('subjects.store') }}" class="card card-body">
    @csrf

    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
        @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="estimated_minutes">Estimated Minutes</label>
        <input class="form-control" id="estimated_minutes" type="number" name="estimated_minutes" value="{{ old('estimated_minutes', 0) }}" min="0" max="100000">
        @error('estimated_minutes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn btn-outline-secondary" href="{{ route('subjects.index') }}">Cancel</a>
    </div>
</form>
@endsection
