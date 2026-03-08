@extends('layouts.app')

@section('title', 'Upload Image — ' . $pet->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Pets</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pets.show', $pet->id) }}">{{ $pet->name }}</a></li>
                <li class="breadcrumb-item active">Upload Image</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <i class="bi bi-cloud-upload me-1"></i>
                Upload Image for <strong>{{ $pet->name }}</strong>
                <span class="text-white-50 small">#{{ $pet->id }}</span>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('pets.upload', $pet->id) }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">
                            Image file <span class="text-danger">*</span>
                        </label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            class="form-control @error('image') is-invalid @enderror"
                            accept="image/jpeg,image/png,image/gif"
                        >
                        <div class="form-text">JPG, PNG or GIF — max 10 MB.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="additional_metadata" class="form-label fw-semibold">
                            Additional metadata
                        </label>
                        <input
                            type="text"
                            id="additional_metadata"
                            name="additional_metadata"
                            class="form-control @error('additional_metadata') is-invalid @enderror"
                            value="{{ old('additional_metadata') }}"
                            maxlength="255"
                            placeholder="Optional description"
                        >
                        @error('additional_metadata')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('pets.show', $pet->id) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-cloud-upload me-1"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

