<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Downstream Impact DTO
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

class DownstreamImpactDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $staleNodes
     */
    public function __construct(
        public int $sourceId,
        public string $sourceTitle,
        public int $affectedDocumentsCount,
        public int $affectedSentencesCount,
        public array $staleNodes = [],
        public string $recommendedAction = 'Repair affected sentences using freshest evidence'
    ) {}

    public function toArray(): array
    {
        return [
            'source_id' => $this->sourceId,
            'source_title' => $this->sourceTitle,
            'affected_documents_count' => $this->affectedDocumentsCount,
            'affected_sentences_count' => $this->affectedSentencesCount,
            'stale_nodes' => $this->staleNodes,
            'recommended_action' => $this->recommendedAction,
        ];
    }
}
