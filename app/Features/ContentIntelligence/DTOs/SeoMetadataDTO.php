<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - SEO Metadata DTO
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

final class SeoMetadataDTO
{
    /**
     * @param  array<string>  $secondaryKeywords
     * @param  array<string, mixed>  $schemaJsonLd
     * @param  int  $seoScore  0 - 100
     * @param  array<string, float>  $keywordDensity
     * @param  array<string, string>  $openGraph
     * @param  array<string, string>  $twitterCard
     */
    public function __construct(
        public readonly string $metaTitle,
        public readonly string $metaDescription,
        public readonly string $canonicalUrl,
        public readonly string $primaryKeyword,
        public readonly array $secondaryKeywords = [],
        public readonly array $schemaJsonLd = [],
        public readonly int $seoScore = 90,
        public readonly array $keywordDensity = [],
        public readonly bool $headingHierarchyValid = true,
        public readonly array $openGraph = [],
        public readonly array $twitterCard = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            metaTitle: (string) ($data['meta_title'] ?? ''),
            metaDescription: (string) ($data['meta_description'] ?? ''),
            canonicalUrl: (string) ($data['canonical_url'] ?? ''),
            primaryKeyword: (string) ($data['primary_keyword'] ?? ''),
            secondaryKeywords: (array) ($data['secondary_keywords'] ?? []),
            schemaJsonLd: (array) ($data['schema_json_ld'] ?? []),
            seoScore: (int) ($data['seo_score'] ?? 85),
            keywordDensity: (array) ($data['keyword_density'] ?? []),
            headingHierarchyValid: (bool) ($data['heading_hierarchy_valid'] ?? true),
            openGraph: (array) ($data['open_graph'] ?? []),
            twitterCard: (array) ($data['twitter_card'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'canonical_url' => $this->canonicalUrl,
            'primary_keyword' => $this->primaryKeyword,
            'secondary_keywords' => $this->secondaryKeywords,
            'schema_json_ld' => $this->schemaJsonLd,
            'seo_score' => $this->seoScore,
            'keyword_density' => $this->keywordDensity,
            'heading_hierarchy_valid' => $this->headingHierarchyValid,
            'open_graph' => $this->openGraph,
            'twitter_card' => $this->twitterCard,
        ];
    }
}
