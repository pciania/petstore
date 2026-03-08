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
    <label for="category-select" class="form-label fw-semibold">Category</label>
    @php
        $currentCategoryId   = old('category.id',   $pet->category['id']   ?? '');
        $currentCategoryName = old('category.name', $pet->category['name'] ?? '');
    @endphp
    <select
        id="category-select"
        class="form-select @error('category') is-invalid @enderror"
        data-id-target="category_id"
        data-name-target="category_name"
    >
        <option value="" data-id="" data-name="">— None —</option>
        @foreach (\App\Http\Requests\StorePetRequest::CATEGORIES as $cat)
            <option
                value="{{ $cat['name'] }}"
                data-id="{{ $cat['id'] }}"
                data-name="{{ $cat['name'] }}"
                {{ (int) $currentCategoryId === $cat['id'] ? 'selected' : '' }}
            >{{ $cat['name'] }}</option>
        @endforeach
    </select>
    <input type="hidden" id="category_id"   name="category[id]"   value="{{ $currentCategoryId }}">
    <input type="hidden" id="category_name" name="category[name]" value="{{ $currentCategoryName }}">
    @error('category')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Tags</label>
    @php
        $existingTags = old('tags', isset($pet) ? array_column($pet->tags, 'name') : []);
        if (empty($existingTags)) { $existingTags = ['']; }
    @endphp
    <div id="tags-container">
        @foreach ($existingTags as $i => $tagName)
            <div class="input-group mb-2 tag-row">
                <input
                    type="text"
                    name="tags[]"
                    class="form-control @error('tags.' . $i) is-invalid @enderror"
                    value="{{ $tagName }}"
                    placeholder="e.g. friendly"
                    maxlength="60"
                >
                <button type="button" class="btn btn-outline-danger remove-tag" title="Remove tag">
                    <i class="bi bi-x-lg"></i>
                </button>
                @error('tags.' . $i)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
    </div>
    <button type="button" id="add-tag" class="btn btn-outline-secondary btn-sm mt-1">
        <i class="bi bi-plus-circle me-1"></i>Add tag
    </button>
    @error('tags')
        <div class="text-danger small mt-1">{{ $message }}</div>
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

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Category select → hidden inputs ──────────────────────────────────────
    const categorySelect = document.getElementById('category-select');
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            document.getElementById('category_id').value   = opt.dataset.id   ?? '';
            document.getElementById('category_name').value = opt.dataset.name ?? '';
        });
    }

    // ── Tags dynamic rows ─────────────────────────────────────────────────────
    const container = document.getElementById('tags-container');
    const addBtn    = document.getElementById('add-tag');

    function bindRemove(row) {
        row.querySelector('.remove-tag').addEventListener('click', function () {
            if (container.querySelectorAll('.tag-row').length > 1) {
                row.remove();
            } else {
                row.querySelector('input').value = '';
            }
        });
    }

    container.querySelectorAll('.tag-row').forEach(bindRemove);

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 tag-row';
        row.innerHTML = `
            <input type="text" name="tags[]" class="form-control"
                   placeholder="e.g. friendly" maxlength="60">
            <button type="button" class="btn btn-outline-danger remove-tag" title="Remove tag">
                <i class="bi bi-x-lg"></i>
            </button>`;
        container.appendChild(row);
        bindRemove(row);
        row.querySelector('input').focus();
    });
});
</script>
@endpush
@endonce

