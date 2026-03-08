@extends('layouts.app')

@section('title', 'Add Pet')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pets.index') }}">Pets</a></li>
                <li class="breadcrumb-item active">Add Pet</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle me-1"></i> Add New Pet
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pets.store') }}">
                    @csrf

                    @include('pets._form')

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('pets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Create Pet
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

