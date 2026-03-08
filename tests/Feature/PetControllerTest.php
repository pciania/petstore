<?php

use App\Data\PetData;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Integrations\Petstore\PetstoreException;
use App\Services\PetService;
use Illuminate\Testing\TestResponse;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function fakePetData(array $overrides = []): PetData
{
    return PetData::fromArray(array_merge([
        'id'        => 123456,
        'name'      => 'Buddy',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [],
        'category'  => null,
    ], $overrides));
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /pets  —  index
// ─────────────────────────────────────────────────────────────────────────────

it('displays the pets index page', function () {
    $this->mock(PetService::class)
        ->shouldReceive('listByStatus')
        ->once()
        ->with('available')
        ->andReturn([fakePetData()]);

    $this->get(route('pets.index'))
        ->assertOk()
        ->assertSee('Buddy')
        ->assertSee('Available');
});

it('filters pets by status via query param', function () {
    $this->mock(PetService::class)
        ->shouldReceive('listByStatus')
        ->once()
        ->with('sold')
        ->andReturn([fakePetData(['status' => 'sold'])]);

    $this->get(route('pets.index', ['status' => 'sold']))
        ->assertOk()
        ->assertSee('Sold');
});

it('shows an error flash when index API call fails', function () {
    $this->mock(PetService::class)
        ->shouldReceive('listByStatus')
        ->once()
        ->andThrow(new PetstoreException('Network error'));

    $this->get(route('pets.index'))
        ->assertRedirect();
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /pets/create
// ─────────────────────────────────────────────────────────────────────────────

it('displays the create form', function () {
    $this->get(route('pets.create'))
        ->assertOk()
        ->assertSee('Add New Pet');
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /pets  —  store
// ─────────────────────────────────────────────────────────────────────────────

it('creates a pet and redirects to its show page', function () {
    $pet = fakePetData(['id' => 999, 'name' => 'Rex']);

    $this->mock(PetService::class)
        ->shouldReceive('create')
        ->once()
        ->andReturn($pet);

    $this->post(route('pets.store'), [
        'name'     => 'Rex',
        'status'   => 'available',
        'category' => ['id' => 1, 'name' => 'Dogs'],
        'tags'     => ['friendly', 'trained'],
    ])
        ->assertRedirect(route('pets.show', 999))
        ->assertSessionHas('success');
});

it('fails validation when name is too short', function () {
    $this->post(route('pets.store'), [
        'name'   => 'X',
        'status' => 'available',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('fails validation when status is invalid', function () {
    $this->post(route('pets.store'), [
        'name'   => 'Fluffy',
        'status' => 'unknown',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('status');
});

it('fails validation when category is invalid', function () {
    $this->post(route('pets.store'), [
        'name'     => 'Fluffy',
        'status'   => 'available',
        'category' => ['id' => 99, 'name' => 'Reptiles'],
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('category');
});

it('fails validation when a tag is too long', function () {
    $this->post(route('pets.store'), [
        'name'   => 'Fluffy',
        'status' => 'available',
        'tags'   => [str_repeat('a', 61)],
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('tags.0');
});

it('passes validation when tags array contains an empty string', function () {
    $pet = fakePetData(['id' => 1, 'name' => 'Fluffy']);

    $this->mock(PetService::class)
        ->shouldReceive('create')
        ->once()
        ->andReturn($pet);

    $this->post(route('pets.store'), [
        'name'   => 'Fluffy',
        'status' => 'available',
        'tags'   => [''],
    ])
        ->assertRedirect()
        ->assertSessionMissing('errors');
});

it('passes validation when no category is selected', function () {
    $pet = fakePetData(['id' => 1, 'name' => 'Fluffy']);

    $this->mock(PetService::class)
        ->shouldReceive('create')
        ->once()
        ->andReturn($pet);

    $this->post(route('pets.store'), [
        'name'     => 'Fluffy',
        'status'   => 'available',
        'category' => ['id' => null, 'name' => null],
    ])
        ->assertRedirect()
        ->assertSessionMissing('errors');
});

it('flashes an error when store API call fails', function () {
    $this->mock(PetService::class)
        ->shouldReceive('create')
        ->once()
        ->andThrow(PetstoreException::fromHttpError(500, 'Server Error'));

    $this->post(route('pets.store'), [
        'name'   => 'Buddy',
        'status' => 'available',
    ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /pets/{id}  —  show
// ─────────────────────────────────────────────────────────────────────────────

it('shows a single pet', function () {
    $this->mock(PetService::class)
        ->shouldReceive('find')
        ->once()
        ->with(123456)
        ->andReturn(fakePetData());

    $this->get(route('pets.show', 123456))
        ->assertOk()
        ->assertSee('Buddy');
});

it('redirects with error when pet is not found', function () {
    $this->mock(PetService::class)
        ->shouldReceive('find')
        ->once()
        ->andThrow(PetstoreException::fromHttpError(404, 'Not Found'));

    $this->get(route('pets.show', 999999))
        ->assertRedirect(route('pets.index'))
        ->assertSessionHas('error');
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /pets/{id}/edit  —  edit
// ─────────────────────────────────────────────────────────────────────────────

it('displays the edit form pre-filled with pet data', function () {
    $this->mock(PetService::class)
        ->shouldReceive('find')
        ->once()
        ->with(123456)
        ->andReturn(fakePetData());

    $this->get(route('pets.edit', 123456))
        ->assertOk()
        ->assertSee('Buddy');
});

// ─────────────────────────────────────────────────────────────────────────────
// PUT /pets/{id}  —  update
// ─────────────────────────────────────────────────────────────────────────────

it('updates a pet and redirects to its show page', function () {
    $pet = fakePetData(['id' => 123456, 'name' => 'Buddy Updated']);

    $this->mock(PetService::class)
        ->shouldReceive('update')
        ->once()
        ->andReturn($pet);

    $this->put(route('pets.update', 123456), [
        'id'       => 123456,
        'name'     => 'Buddy Updated',
        'status'   => 'pending',
        'category' => ['id' => 2, 'name' => 'Cats'],
        'tags'     => ['playful'],
    ])
        ->assertRedirect(route('pets.show', 123456))
        ->assertSessionHas('success');
});

it('fails validation on update when id is missing', function () {
    $this->put(route('pets.update', 123456), [
        'name'   => 'Buddy',
        'status' => 'available',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('id');
});

it('flashes an error when update API call fails', function () {
    $this->mock(PetService::class)
        ->shouldReceive('update')
        ->once()
        ->andThrow(PetstoreException::fromHttpError(400, 'Bad Request'));

    $this->put(route('pets.update', 123456), [
        'id'     => 123456,
        'name'   => 'Buddy',
        'status' => 'available',
    ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

// ─────────────────────────────────────────────────────────────────────────────
// DELETE /pets/{id}  —  destroy
// ─────────────────────────────────────────────────────────────────────────────

it('deletes a pet and redirects to index', function () {
    $this->mock(PetService::class)
        ->shouldReceive('delete')
        ->once()
        ->with(123456);

    $this->delete(route('pets.destroy', 123456))
        ->assertRedirect(route('pets.index'))
        ->assertSessionHas('success');
});

it('flashes an error when delete API call fails', function () {
    $this->mock(PetService::class)
        ->shouldReceive('delete')
        ->once()
        ->andThrow(new PetstoreException('Network error'));

    $this->delete(route('pets.destroy', 123456))
        ->assertRedirect(route('pets.index'))
        ->assertSessionHas('error');
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /pets/{id}/upload  —  uploadForm
// ─────────────────────────────────────────────────────────────────────────────

it('displays the upload image form', function () {
    $this->mock(PetService::class)
        ->shouldReceive('find')
        ->once()
        ->with(123456)
        ->andReturn(fakePetData());

    $this->get(route('pets.upload.form', 123456))
        ->assertOk()
        ->assertSee('Upload Image');
});

it('redirects with error when pet is not found on upload form', function () {
    $this->mock(PetService::class)
        ->shouldReceive('find')
        ->once()
        ->andThrow(PetstoreException::fromHttpError(404, 'Not Found'));

    $this->get(route('pets.upload.form', 999999))
        ->assertRedirect(route('pets.index'))
        ->assertSessionHas('error');
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /pets/{id}/upload  —  uploadImage
// ─────────────────────────────────────────────────────────────────────────────

it('uploads an image and redirects to show page', function () {
    $this->mock(PetService::class)
        ->shouldReceive('uploadImage')
        ->once();

    $file = Illuminate\Http\UploadedFile::fake()->create('buddy.jpg', 64, 'image/jpeg');

    $this->post(route('pets.upload', 123456), ['image' => $file])
        ->assertRedirect(route('pets.show', 123456))
        ->assertSessionHas('success');
});

it('fails validation when no image is provided on upload', function () {
    $this->post(route('pets.upload', 123456), [])
        ->assertRedirect()
        ->assertSessionHasErrors('image');
});

it('flashes an error when upload API call fails', function () {
    $this->mock(PetService::class)
        ->shouldReceive('uploadImage')
        ->once()
        ->andThrow(PetstoreException::fromHttpError(404, 'Not Found'));

    $file = Illuminate\Http\UploadedFile::fake()->create('buddy.jpg', 64, 'image/jpeg');

    $this->post(route('pets.upload', 123456), ['image' => $file])
        ->assertRedirect()
        ->assertSessionHas('error');
});
