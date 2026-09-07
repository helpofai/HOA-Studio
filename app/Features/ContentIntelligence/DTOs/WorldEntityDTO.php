<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - World Entity DTO
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\ContentIntelligence\DTOs;

use Illuminate\Support\Str;

final class WorldEntityDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $category = 'technology',
        public readonly ?string $description = null,
        public readonly ?int $userId = null,
        public readonly array $aliases = [],
        public readonly array $attributes = [],
        public readonly ?string $temporalValidFrom = null,
        public readonly ?string $temporalValidUntil = null
    ) {}

    public static function fromArray(array $data): self
    {
        $name = (string) ($data['name'] ?? '');

        return new self(
            id: (string) ($data['id'] ?? 'ent_'.Str::lower(Str::random(10))),
            name: $name,
            slug: (string) ($data['slug'] ?? Str::slug($name)),
            category: (string) ($data['category'] ?? 'technology'),
            description: $data['description'] ?? null,
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            aliases: (array) ($data['aliases'] ?? []),
            attributes: (array) ($data['attributes'] ?? []),
            temporalValidFrom: $data['temporal_valid_from'] ?? null,
            temporalValidUntil: $data['temporal_valid_until'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'description' => $this->description,
            'aliases' => $this->aliases,
            'attributes' => $this->attributes,
            'temporal_valid_from' => $this->temporalValidFrom,
            'temporal_valid_until' => $this->temporalValidUntil,
        ];
    }
}
