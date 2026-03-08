<?php

use App\Data\PetData;
use App\Http\Requests\StorePetRequest;

// ─────────────────────────────────────────────────────────────────────────────
// PetData::fromArray — category mapping
// ─────────────────────────────────────────────────────────────────────────────

it('maps category from API response correctly', function () {
    $pet = PetData::fromArray([
        'id'       => 1,
        'name'     => 'Buddy',
        'status'   => 'available',
        'category' => ['id' => 1, 'name' => 'Dogs'],
        'photoUrls' => [],
        'tags'     => [],
    ]);

    expect($pet->category)->toBe(['id' => 1, 'name' => 'Dogs']);
});

it('sets category to null when missing from API response', function () {
    $pet = PetData::fromArray([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [],
    ]);

    expect($pet->category)->toBeNull();
});

it('sets category to null when category has no name', function () {
    $pet = PetData::fromArray([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'category'  => ['id' => 0],
        'photoUrls' => [],
        'tags'      => [],
    ]);

    expect($pet->category)->toBeNull();
});

it('sets category to null when name is an empty string', function () {
    $pet = PetData::fromArray([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'category'  => ['id' => 0, 'name' => ''],
        'photoUrls' => [],
        'tags'      => [],
    ]);

    expect($pet->category)->toBeNull();
});

it('sets category to null when API returns placeholder string value', function () {
    $pet = PetData::fromArray([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'category'  => ['id' => 0, 'name' => 'string'],
        'photoUrls' => [],
        'tags'      => [],
    ]);

    expect($pet->category)->toBeNull();
});

// ─────────────────────────────────────────────────────────────────────────────
// PetData::fromArray — tags mapping
// ─────────────────────────────────────────────────────────────────────────────

it('maps tags from API response correctly', function () {
    $pet = PetData::fromArray([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [
            ['id' => 1, 'name' => 'friendly'],
            ['id' => 2, 'name' => 'trained'],
        ],
    ]);

    expect($pet->tags)->toBe([
        ['id' => 1, 'name' => 'friendly'],
        ['id' => 2, 'name' => 'trained'],
    ]);
});

it('ignores non-array entries in tags', function () {
    $pet = PetData::fromArray([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => ['invalid-string', ['id' => 1, 'name' => 'ok']],
    ]);

    expect($pet->tags)->toHaveCount(1)
        ->and($pet->tags[0]['name'])->toBe('ok');
});

// ─────────────────────────────────────────────────────────────────────────────
// PetData::toApiPayload — category and tags included
// ─────────────────────────────────────────────────────────────────────────────

it('includes category in API payload when set', function () {
    $pet = new PetData(
        id: 1,
        name: 'Buddy',
        status: 'available',
        photoUrls: [],
        tags: [['id' => 1, 'name' => 'friendly']],
        category: ['id' => 1, 'name' => 'Dogs'],
    );

    $payload = $pet->toApiPayload();

    expect($payload['category'])->toBe(['id' => 1, 'name' => 'Dogs'])
        ->and($payload['tags'])->toBe([['id' => 1, 'name' => 'friendly']]);
});

it('omits category from API payload when null', function () {
    $pet = new PetData(id: 1, name: 'Buddy', status: 'available');

    expect($pet->toApiPayload())->not->toHaveKey('category');
});

// ─────────────────────────────────────────────────────────────────────────────
// StorePetRequest::CATEGORIES constant
// ─────────────────────────────────────────────────────────────────────────────

it('has exactly three predefined categories', function () {
    expect(StorePetRequest::CATEGORIES)->toHaveCount(3)
        ->and(array_keys(StorePetRequest::CATEGORIES))->toBe(['dogs', 'cats', 'birds']);
});

it('each category has a numeric id and a name', function () {
    foreach (StorePetRequest::CATEGORIES as $cat) {
        expect($cat)->toHaveKeys(['id', 'name'])
            ->and($cat['id'])->toBeInt()
            ->and($cat['name'])->toBeString();
    }
});

it('category objects match expected values', function () {
    expect(StorePetRequest::CATEGORIES['dogs'])->toBe(['id' => 1, 'name' => 'Dogs'])
        ->and(StorePetRequest::CATEGORIES['cats'])->toBe(['id' => 2, 'name' => 'Cats'])
        ->and(StorePetRequest::CATEGORIES['birds'])->toBe(['id' => 3, 'name' => 'Birds']);
});

