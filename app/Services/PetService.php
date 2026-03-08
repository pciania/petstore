<?php

namespace App\Services;

use App\Data\PetData;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Integrations\Petstore\PetstoreClient;

class PetService
{
    public function __construct(
        private readonly PetstoreClient $client,
    ) {}

    /**
     * @return PetData[]
     */
    public function listByStatus(string $status = 'available'): array
    {
        $items = $this->client->findByStatus($status);

        return array_map(
            fn (array $item) => PetData::fromArray($item),
            $items,
        );
    }

    public function find(int $id): PetData
    {
        return PetData::fromArray($this->client->getPet($id));
    }

    public function create(StorePetRequest $request): PetData
    {
        $pet = $this->buildFromStoreRequest($request);

        $response = $this->client->createPet($pet->toApiPayload());

        return PetData::fromArray($response);
    }

    public function update(UpdatePetRequest $request): PetData
    {
        $pet = $this->buildFromUpdateRequest($request);

        $response = $this->client->updatePet($pet->toApiPayload());

        return PetData::fromArray($response);
    }

    public function delete(int $id): void
    {
        $this->client->deletePet($id);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildFromStoreRequest(StorePetRequest $request): PetData
    {
        return new PetData(
            id: random_int(100_000, 999_999_999),
            name: $request->validated('name'),
            status: $request->validated('status'),
            photoUrls: $this->normalizePhotoUrls($request->validated('photo_urls', '')),
            tags: [],
        );
    }

    private function buildFromUpdateRequest(UpdatePetRequest $request): PetData
    {
        return new PetData(
            id: (int) $request->validated('id'),
            name: $request->validated('name'),
            status: $request->validated('status'),
            photoUrls: $this->normalizePhotoUrls($request->validated('photo_urls', '')),
            tags: [],
        );
    }

    /**
     * @return string[]
     */
    private function normalizePhotoUrls(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }

        if (is_string($raw) && $raw !== '') {
            return array_values(array_filter(array_map('trim', explode("\n", $raw))));
        }

        return [];
    }
}

