<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - KeywordUniverse DTO
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

namespace App\Features\AI\Data;

/**
 * Stage 05 & 06: Keyword & Entity Universe DTO
 * Assigns purpose, location, and semantic roles to every keyword rather than raw counts.
 */
class KeywordUniverse
{
    public function __construct(
        public string $primaryKeyword = '',
        public array $secondaryKeywords = [],
        public array $longtailKeywords = [],
        public array $questionKeywords = [],
        public array $semanticEntities = [],
        public array $keywordIntentMap = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            primaryKeyword: $data['primary_keyword'] ?? '',
            secondaryKeywords: $data['secondary_keywords'] ?? [],
            longtailKeywords: $data['longtail_keywords'] ?? [],
            questionKeywords: $data['question_keywords'] ?? [],
            semanticEntities: $data['semantic_entities'] ?? [],
            keywordIntentMap: $data['keyword_intent_map'] ?? []
        );
    }

    public function addKeywordWithPurpose(string $keyword, string $topic, string $importance = 'high', array $locations = ['H2', 'body']): void
    {
        $this->keywordIntentMap[] = [
            'keyword' => $keyword,
            'topic' => $topic,
            'importance' => $importance,
            'recommended_locations' => $locations,
            'usage' => 'natural_explanation',
        ];
    }

    public function toArray(): array
    {
        return [
            'primary_keyword' => $this->primaryKeyword,
            'secondary_keywords' => $this->secondaryKeywords,
            'longtail_keywords' => $this->longtailKeywords,
            'question_keywords' => $this->questionKeywords,
            'semantic_entities' => $this->semanticEntities,
            'keyword_intent_map' => $this->keywordIntentMap,
        ];
    }
}
