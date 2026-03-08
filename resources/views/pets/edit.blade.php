@extends('layouts.app')

@section('title', 'Edit — ' . $pet->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Pets</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pets.show', $pet->id) }}">{{ $pet->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-pencil me-1"></i> Edit Pet <strong>{{ $pet->name }}</strong>
                <span class="text-muted small">#{{ $pet->id }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pets.update', $pet->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $pet->id }}">

                    @include('pets._form')

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('pets.show', $pet->id) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

