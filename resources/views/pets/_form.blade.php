{{-- Shared form partial for create and edit --}}

<div class="mb-3">
    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
    <input
        type="text"
        id="name"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $pet->name ?? '') }}"
        placeholder="e.g. Buddy"
        maxlength="120"
        required
    >
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
        @foreach (['available' => 'Available', 'pending' => 'Pending', 'sold' => 'Sold'] as $value => $label)
            <option
                value="{{ $value }}"
                {{ old('status', $pet->status ?? 'available') === $value ? 'selected' : '' }}
            >{{ $label }}</option>
        @endforeach
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="photo_urls" class="form-label fw-semibold">Photo URLs</label>
    <textarea
        id="photo_urls"
        name="photo_urls"
        class="form-control @error('photo_urls') is-invalid @enderror"
        rows="3"
        placeholder="One URL per line"
    >{{ old('photo_urls', isset($pet) ? implode("\n", $pet->photoUrls) : '') }}</textarea>
    <div class="form-text">Enter one URL per line.</div>
    @error('photo_urls')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

