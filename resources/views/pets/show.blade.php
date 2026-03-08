@extends('layouts.app')

@section('title', $pet->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Pets</a></li>
                <li class="breadcrumb-item active">{{ $pet->name }}</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-5">{{ $pet->name }}</span>
                <span class="badge status-badge-{{ $pet->status }} fs-6">{{ ucfirst($pet->status) }}</span>
            </div>

            @if ($pet->photoUrls)
                <img
                    src="{{ $pet->photoUrls[0] }}"
                    alt="{{ $pet->name }}"
                    class="card-img-top"
                    style="max-height:300px;object-fit:cover;"
                    onerror="this.style.display='none'"
                >
            @endif

            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">ID</dt>
                    <dd class="col-sm-9 text-muted">{{ $pet->id }}</dd>

                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $pet->name }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <span class="badge status-badge-{{ $pet->status }}">{{ ucfirst($pet->status) }}</span>
                    </dd>

                    @if ($pet->category)
                        <dt class="col-sm-3">Category</dt>
                        <dd class="col-sm-9">{{ $pet->category['name'] ?? '—' }}</dd>
                    @endif

                    @if ($pet->tags)
                        <dt class="col-sm-3">Tags</dt>
                        <dd class="col-sm-9">
                            @foreach ($pet->tags as $tag)
                                <span class="badge bg-secondary me-1">{{ $tag['name'] ?? '' }}</span>
                            @endforeach
                        </dd>
                    @endif

                    @if (count($pet->photoUrls) > 1)
                        <dt class="col-sm-3">Photos</dt>
                        <dd class="col-sm-9">
                            <ul class="list-unstyled mb-0">
                                @foreach ($pet->photoUrls as $url)
                                    <li><a href="{{ $url }}" target="_blank" rel="noopener" class="text-break small">{{ $url }}</a></li>
                                @endforeach
                            </ul>
                        </dd>
                    @endif
                </dl>
            </div>

            <div class="card-footer d-flex gap-2 justify-content-end">
                <a href="{{ route('pets.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('pets.upload.form', $pet->id) }}" class="btn btn-info text-white">
                    <i class="bi bi-cloud-upload me-1"></i>Upload Image
                </a>
                <a href="{{ route('pets.edit', $pet->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('pets.destroy', $pet->id) }}" onsubmit="return confirm('Delete {{ addslashes($pet->name) }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

