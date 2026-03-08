@extends('layouts.app')

@section('title', 'Pets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-list-ul me-2"></i>Pets</h1>
    <a href="{{ route('pets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add Pet
    </a>
</div>

{{-- Status filter --}}
<form method="GET" action="{{ route('pets.index') }}" class="mb-4">
    <div class="input-group" style="max-width: 320px;">
        <label class="input-group-text" for="status">Status</label>
        <select id="status" name="status" class="form-select" onchange="this.form.submit()">
            @foreach (['available' => 'Available', 'pending' => 'Pending', 'sold' => 'Sold'] as $value => $label)
                <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</form>

@if (count($pets) === 0)
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        No pets found with status <strong>{{ $status }}</strong>.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white shadow-sm rounded">
            <thead class="table-dark">
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Photo</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pets as $pet)
                    <tr>
                        <td class="text-muted small">{{ $pet->id }}</td>
                        <td class="fw-semibold">{{ $pet->name }}</td>
                        <td>
                            <span class="badge status-badge-{{ $pet->status }}">
                                {{ ucfirst($pet->status) }}
                            </span>
                        </td>
                        <td>
                            @if ($pet->photoUrls)
                                <img
                                    src="{{ $pet->photoUrls[0] }}"
                                    alt="{{ $pet->name }}"
                                    style="width:40px;height:40px;object-fit:cover;"
                                    class="rounded"
                                    onerror="this.style.display='none'"
                                >
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('pets.show', $pet->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pets.edit', $pet->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('pets.destroy', $pet->id) }}" class="d-inline" onsubmit="return confirm('Delete {{ addslashes($pet->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-muted small">Showing {{ count($pets) }} pets.</p>
@endif
@endsection


