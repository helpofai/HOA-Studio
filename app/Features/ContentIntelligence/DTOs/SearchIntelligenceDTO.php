<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Search Intelligence DTO
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

final class SearchIntelligenceDTO
{
    /**
     * @param  string  $primaryIntent  Informational, Commercial, Navigational, Transactional, Comparative
     * @param  array<string>  $secondaryIntents
     * @param  string  $userJourneyStage  Awareness, Consideration, Decision, Implementation, Troubleshooting
     * @param array{
     *     primary: array<string>,
     *     secondary: array<string>,
     *     long_tail: array<string>,
     *     paa_questions: array<string>
     * } $queryClusters
     * @param array<array{
     *     url: string,
     *     title: string,
     *     headings: array<string>,
     *     word_count: int,
     *     content_formats: array<string>,
     *     weaknesses: array<string>
     * }> $serpCompetitors
     * @param array{
     *     core_topics: array<string>,
     *     supporting_topics: array<string>,
     *     related_topics: array<string>
     * } $topicUniverse
     * @param array{
     *     missing_topics: array<string>,
     *     weak_angles: array<string>,
     *     outdated_points: array<string>,
     *     underexplored_opportunities: array<string>
     * } $contentGaps
     * @param  array<string>  $targetEntities
     */
    public function __construct(
        public readonly string $primaryIntent,
        public readonly array $secondaryIntents = [],
        public readonly string $userJourneyStage = 'Implementation',
        public readonly array $queryClusters = [
            'primary' => [],
            'secondary' => [],
            'long_tail' => [],
            'paa_questions' => [],
        ],
        public readonly array $serpCompetitors = [],
        public readonly array $topicUniverse = [
            'core_topics' => [],
            'supporting_topics' => [],
            'related_topics' => [],
        ],
        public readonly array $contentGaps = [
            'missing_topics' => [],
            'weak_angles' => [],
            'outdated_points' => [],
            'underexplored_opportunities' => [],
        ],
        public readonly array $targetEntities = [],
        public readonly float $intentConfidence = 0.95
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            primaryIntent: (string) ($data['primary_intent'] ?? 'Informational'),
            secondaryIntents: (array) ($data['secondary_intents'] ?? []),
            userJourneyStage: (string) ($data['user_journey_stage'] ?? 'Implementation'),
            queryClusters: [
                'primary' => (array) ($data['query_clusters']['primary'] ?? []),
                'secondary' => (array) ($data['query_clusters']['secondary'] ?? []),
                'long_tail' => (array) ($data['query_clusters']['long_tail'] ?? []),
                'paa_questions' => (array) ($data['query_clusters']['paa_questions'] ?? []),
            ],
            serpCompetitors: (array) ($data['serp_competitors'] ?? []),
            topicUniverse: [
                'core_topics' => (array) ($data['topic_universe']['core_topics'] ?? []),
                'supporting_topics' => (array) ($data['topic_universe']['supporting_topics'] ?? []),
                'related_topics' => (array) ($data['topic_universe']['related_topics'] ?? []),
            ],
            contentGaps: [
                'missing_topics' => (array) ($data['content_gaps']['missing_topics'] ?? []),
                'weak_angles' => (array) ($data['content_gaps']['weak_angles'] ?? []),
                'outdated_points' => (array) ($data['content_gaps']['outdated_points'] ?? []),
                'underexplored_opportunities' => (array) ($data['content_gaps']['underexplored_opportunities'] ?? []),
            ],
            targetEntities: (array) ($data['target_entities'] ?? []),
            intentConfidence: (float) ($data['intent_confidence'] ?? 0.95)
        );
    }

    public function toArray(): array
    {
        return [
            'primary_intent' => $this->primaryIntent,
            'secondary_intents' => $this->secondaryIntents,
            'user_journey_stage' => $this->userJourneyStage,
            'query_clusters' => $this->queryClusters,
            'serp_competitors' => $this->serpCompetitors,
            'topic_universe' => $this->topicUniverse,
            'content_gaps' => $this->contentGaps,
            'target_entities' => $this->targetEntities,
            'intent_confidence' => $this->intentConfidence,
        ];
    }
}
