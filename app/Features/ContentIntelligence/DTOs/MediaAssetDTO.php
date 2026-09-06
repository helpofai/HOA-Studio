<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Media Asset DTO
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

final class MediaAssetDTO
{
    /**
     * @param  string  $assetType  'diagram' | 'table' | 'callout' | 'snippet'
     * @param  string  $content  HTML or raw syntax (e.g. Mermaid markdown)
     * @param  string  $placement  'before_body' | 'in_body' | 'after_body'
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $assetId,
        public readonly string $assetType,
        public readonly string $title,
        public readonly string $content,
        public readonly string $targetSectionId,
        public readonly string $placement = 'in_body',
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            assetId: (string) ($data['asset_id'] ?? uniqid('asset_')),
            assetType: (string) ($data['asset_type'] ?? 'callout'),
            title: (string) ($data['title'] ?? ''),
            content: (string) ($data['content'] ?? ''),
            targetSectionId: (string) ($data['target_section_id'] ?? ''),
            placement: (string) ($data['placement'] ?? 'in_body'),
            metadata: (array) ($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'asset_id' => $this->assetId,
            'asset_type' => $this->assetType,
            'title' => $this->title,
            'content' => $this->content,
            'target_section_id' => $this->targetSectionId,
            'placement' => $this->placement,
            'metadata' => $this->metadata,
        ];
    }
}
