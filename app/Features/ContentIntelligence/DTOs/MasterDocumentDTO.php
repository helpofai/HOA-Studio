<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Master Document DTO
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

final class MasterDocumentDTO
{
    /**
     * @param  array<string, mixed>  $tiptapJson  Canonical ProseMirror document AST
     * @param  string  $html  Full article HTML
     * @param  string  $markdown  Full article Markdown
     * @param  array<string, mixed>  $schemaJsonLd
     */
    public function __construct(
        public readonly ?int $documentId,
        public readonly string $title,
        public readonly string $slug,
        public readonly array $tiptapJson,
        public readonly string $html,
        public readonly string $markdown,
        public readonly int $wordCount,
        public readonly int $readingTimeMinutes,
        public readonly int $seoScore,
        public readonly array $schemaJsonLd = [],
        public readonly string $assembledAt = ''
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            documentId: isset($data['document_id']) ? (int) $data['document_id'] : null,
            title: (string) ($data['title'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            tiptapJson: (array) ($data['tiptap_json'] ?? []),
            html: (string) ($data['html'] ?? ''),
            markdown: (string) ($data['markdown'] ?? ''),
            wordCount: (int) ($data['word_count'] ?? 0),
            readingTimeMinutes: (int) ($data['reading_time_minutes'] ?? 1),
            seoScore: (int) ($data['seo_score'] ?? 90),
            schemaJsonLd: (array) ($data['schema_json_ld'] ?? []),
            assembledAt: (string) ($data['assembled_at'] ?? now()->toIso8601String())
        );
    }

    public function toArray(): array
    {
        return [
            'document_id' => $this->documentId,
            'title' => $this->title,
            'slug' => $this->slug,
            'tiptap_json' => $this->tiptapJson,
            'html' => $this->html,
            'markdown' => $this->markdown,
            'word_count' => $this->wordCount,
            'reading_time_minutes' => $this->readingTimeMinutes,
            'seo_score' => $this->seoScore,
            'schema_json_ld' => $this->schemaJsonLd,
            'assembled_at' => $this->assembledAt,
        ];
    }
}
