<?php

namespace App\Data;

class PetData
{
    /**
     * @param  string[]  $photoUrls
     * @param  array<int, array{id?: int, name?: string}>  $tags
     * @param  array{id?: int, name?: string}|null  $category
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly array $photoUrls = [],
        public readonly array $tags = [],
        public readonly ?array $category = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            status: (string) ($data['status'] ?? 'available'),
            photoUrls: array_values(array_filter((array) ($data['photoUrls'] ?? []))),
            tags: array_values(array_filter((array) ($data['tags'] ?? []), 'is_array')),
            category: isset($data['category']) && is_array($data['category'])
                ? $data['category']
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = [
            'id'        => $this->id,
            'name'      => $this->name,
            'status'    => $this->status,
            'photoUrls' => $this->photoUrls ?: [],
            'tags'      => $this->tags ?: [],
        ];

        if ($this->category !== null) {
            $payload['category'] = $this->category;
        }

        return $payload;
    }
}

