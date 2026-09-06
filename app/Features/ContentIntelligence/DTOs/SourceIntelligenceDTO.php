<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Source Intelligence DTO
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

use App\Features\ContentIntelligence\Enums\SourceReliabilityTier;

final class SourceIntelligenceDTO
{
    public function __construct(
        public readonly string $url,
        public readonly string $title,
        public readonly SourceReliabilityTier $sourceType,
        public readonly int $reliabilityScore,
        public readonly int $domainAuthority = 50,
        public readonly bool $isPrimary = false,
        public readonly ?string $publicationDate = null,
        public readonly ?string $lastVerifiedAt = null,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        $type = isset($data['source_type']) && $data['source_type'] instanceof SourceReliabilityTier
            ? $data['source_type']
            : SourceReliabilityTier::tryFrom($data['source_type'] ?? '') ?? SourceReliabilityTier::INDUSTRY_PUBLICATION;

        return new self(
            url: (string) ($data['url'] ?? ''),
            title: (string) ($data['title'] ?? 'Untitled Source'),
            sourceType: $type,
            reliabilityScore: (int) ($data['reliability_score'] ?? $type->defaultReliabilityScore()),
            domainAuthority: (int) ($data['domain_authority'] ?? 50),
            isPrimary: (bool) ($data['is_primary'] ?? $type->isPrimary()),
            publicationDate: $data['publication_date'] ?? null,
            lastVerifiedAt: $data['last_verified_at'] ?? null,
            metadata: (array) ($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'title' => $this->title,
            'source_type' => $this->sourceType->value,
            'reliability_score' => $this->reliabilityScore,
            'domain_authority' => $this->domainAuthority,
            'is_primary' => $this->isPrimary,
            'publication_date' => $this->publicationDate,
            'last_verified_at' => $this->lastVerifiedAt,
            'metadata' => $this->metadata,
        ];
    }
}
