<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Search Intelligence Service
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
|
|--------------------------------------------------------------------------
*/

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use Illuminate\Support\Facades\Log;

class SearchIntelligenceService
{
    /**
     * Deconstruct search intent, generate query clusters, build topic universe,
     * and identify content gaps from the Content Mission.
     *
     * NOW USES REAL AI to perform search intelligence analysis
     */
    public function analyze(ContentMissionDTO $mission): SearchIntelligenceDTO
    {
        $topic = $mission->topic;

        Log::info("[SearchIntel] STEP 2: AI analyzing search intent for: {$topic}");

        $primaryIntent = $this->determinePrimaryIntent($mission);
        $journeyStage = $this->determineJourneyStage($primaryIntent, $mission);

        // ══════════════════════════════════════════════════════════════
        // AI-Powered Search Intelligence
        // ══════════════════════════════════════════════════════════════

        $targetPersona = $mission->targetAudience['persona'] ?? 'General';
        $intentPrompt = "Analyze the search landscape for the topic: \"{$topic}\"
Primary Objective: {$mission->primaryObjective}
Target Audience: {$targetPersona}
Search Intent: {$primaryIntent}
Journey Stage: {$journeyStage}

Generate comprehensive search intelligence:

1. query_clusters: Group related search queries
   - primary: 3-5 main search queries people use
   - secondary: 5-7 related/related queries
   - long_tail: 5-7 specific long-tail queries
   - paa_questions: 5-7 People Also Ask questions

2. topic_universe: Map the topic ecosystem
   - core_topics: 3-5 core sub-topics
   - supporting_topics: 4-6 supporting topics
   - related_topics: 4-6 related/broader topics

3. target_entities: 5-8 key named entities (people, tools, concepts)

4. content_gaps: What do current search results miss?
   - missing_topics: important topics not covered
   - weak_angles: poorly covered angles
   - outdated_points: outdated assumptions
   - underexplored_opportunities: unique angles to exploit

5. serp_competitors: 2-3 typical competitor profiles
   - url pattern, title, typical headings, word count, weaknesses

Return strictly valid JSON:
{
  \"query_clusters\": {\"primary\": [...], \"secondary\": [...], \"long_tail\": [...], \"paa_questions\": [...]},
  \"topic_universe\": {\"core_topics\": [...], \"supporting_topics\": [...], \"related_topics\": [...]},
  \"target_entities\": [...],
  \"content_gaps\": {\"missing_topics\": [...], \"weak_angles\": [...], \"outdated_points\": [...], \"underexplored_opportunities\": [...]},
  \"serp_competitors\": [{\"url\": \"...\", \"title\": \"...\", \"headings\": [...], \"word_count\": 0, \"weaknesses\": [...]}]
}";

        $aiIntel = DynamicContentProvider::askJSON($intentPrompt, [
            'query_clusters' => ['primary' => [], 'secondary' => [], 'long_tail' => [], 'paa_questions' => []],
            'topic_universe' => ['core_topics' => [], 'supporting_topics' => [], 'related_topics' => []],
            'target_entities' => [],
            'content_gaps' => ['missing_topics' => [], 'weak_angles' => [], 'outdated_points' => [], 'underexplored_opportunities' => []],
            'serp_competitors' => []
        ]);

        // Merge AI data with fallback defaults
        $queryClusters = $this->mergeQueryClusters($topic, $aiIntel['query_clusters'] ?? []);
        $topicUniverse = $this->mergeTopicUniverse($topic, $aiIntel['topic_universe'] ?? []);
        $targetEntities = $this->mergeEntities($topic, $mission, $aiIntel['target_entities'] ?? []);
        $contentGaps = $this->mergeContentGaps($aiIntel['content_gaps'] ?? []);
        $serpCompetitors = $this->mergeCompetitors($topic, $aiIntel['serp_competitors'] ?? []);

        Log::info("[SearchIntel] STEP 2 COMPLETE: " . count($queryClusters['primary']) . " primary queries, " . count($targetEntities) . " entities");

        return new SearchIntelligenceDTO(
            primaryIntent: $primaryIntent,
            secondaryIntents: array_slice($aiIntel['secondary_intents'] ?? ['Practical Implementation', 'Troubleshooting & Optimization'], 0, 3),
            userJourneyStage: $journeyStage,
            queryClusters: $queryClusters,
            serpCompetitors: $serpCompetitors,
            topicUniverse: $topicUniverse,
            contentGaps: $contentGaps,
            targetEntities: $targetEntities,
            intentConfidence: 0.92
        );
    }

    protected function determinePrimaryIntent(ContentMissionDTO $mission): string
    {
        $text = strtolower($mission->topic.' '.$mission->primaryObjective);

        if (str_contains($text, 'vs') || str_contains($text, 'comparison') || str_contains($text, 'alternative')) {
            return 'Comparative / Commercial Investigation';
        }

        if (str_contains($text, 'how to') || str_contains($text, 'tutorial') || str_contains($text, 'guide') || str_contains($text, 'setup') || str_contains($text, 'config')) {
            return 'Informational / Practical Implementation';
        }

        if (str_contains($text, 'fix') || str_contains($text, 'error') || str_contains($text, 'problem') || str_contains($text, 'debug')) {
            return 'Troubleshooting';
        }

        return 'Informational / Comprehensive Authority';
    }

    protected function determineJourneyStage(string $primaryIntent, ContentMissionDTO $mission): string
    {
        if (str_contains($primaryIntent, 'Implementation') || str_contains($primaryIntent, 'Troubleshooting')) {
            return 'Implementation';
        }

        if (str_contains($primaryIntent, 'Commercial') || str_contains($primaryIntent, 'Comparative')) {
            return 'Decision';
        }

        $expertise = strtolower($mission->targetAudience['expertise_level'] ?? 'intermediate');

        return ($expertise === 'beginner') ? 'Awareness' : 'Consideration';
    }

    protected function mergeQueryClusters(string $topic, array $aiData): array
    {
        $default = [
            'primary' => [$topic, "{$topic} best practices", "{$topic} guide"],
            'secondary' => ["{$topic} features", "{$topic} comparison", "{$topic} pricing"],
            'long_tail' => ["what is {$topic} and how does it work", "how to use {$topic} effectively"],
            'paa_questions' => ["What is {$topic}?", "How does {$topic} work?", "Is {$topic} worth using?"],
        ];

        return [
            'primary' => !empty($aiData['primary']) ? $aiData['primary'] : $default['primary'],
            'secondary' => !empty($aiData['secondary']) ? $aiData['secondary'] : $default['secondary'],
            'long_tail' => !empty($aiData['long_tail']) ? $aiData['long_tail'] : $default['long_tail'],
            'paa_questions' => !empty($aiData['paa_questions']) ? $aiData['paa_questions'] : $default['paa_questions'],
        ];
    }

    protected function mergeTopicUniverse(string $topic, array $aiData): array
    {
        return [
            'core_topics' => !empty($aiData['core_topics']) ? $aiData['core_topics'] : [$topic],
            'supporting_topics' => !empty($aiData['supporting_topics']) ? $aiData['supporting_topics'] : ['Features & Capabilities', 'Getting Started'],
            'related_topics' => !empty($aiData['related_topics']) ? $aiData['related_topics'] : ['Alternatives', 'Pricing'],
        ];
    }

    protected function mergeEntities(string $topic, ContentMissionDTO $mission, array $aiEntities): array
    {
        $entities = [$topic];
        foreach ($aiEntities as $e) {
            if (is_string($e) && strlen($e) > 2) {
                $entities[] = $e;
            }
        }
        foreach ($mission->secondaryObjectives as $obj) {
            $words = array_filter(explode(' ', $obj), fn($w) => strlen($w) > 3);
            if (!empty($words)) {
                $entities[] = implode(' ', array_slice($words, 0, 3));
            }
        }
        return array_values(array_unique(array_slice($entities, 0, 10)));
    }

    protected function mergeContentGaps(array $aiData): array
    {
        return [
            'missing_topics' => !empty($aiData['missing_topics']) ? $aiData['missing_topics'] : ['Comprehensive comparison with alternatives', 'Real-world case studies'],
            'weak_angles' => !empty($aiData['weak_angles']) ? $aiData['weak_angles'] : ['Generic overviews without actionable insights', 'Lack of specific use cases'],
            'outdated_points' => !empty($aiData['outdated_points']) ? $aiData['outdated_points'] : ['References to deprecated features', 'Outdated pricing information'],
            'underexplored_opportunities' => !empty($aiData['underexplored_opportunities']) ? $aiData['underexplored_opportunities'] : ['Detailed comparison tables', 'Industry-specific use cases'],
        ];
    }

    protected function mergeCompetitors(string $topic, array $aiCompetitors): array
    {
        if (!empty($aiCompetitors) && count($aiCompetitors) >= 2) {
            return array_slice($aiCompetitors, 0, 3);
        }

        $slug = \Illuminate\Support\Str::slug($topic);
        return [
            [
                'url' => "https://medium.com/topic/" . $slug . "-overview",
                'title' => "Complete Guide to {$topic}",
                'headings' => ["Overview of {$topic}", 'Key Capabilities', 'Implementation Workflow', 'Summary'],
                'word_count' => 1500,
                'content_formats' => ['prose', 'screenshots'],
                'weaknesses' => ['Generic overview without depth', 'No comparisons or benchmarks', 'Outdated information'],
            ],
            [
                'url' => "https://dev.to/t/" . $slug . "/guide",
                'title' => "{$topic} In-Depth Technical Review",
                'headings' => ['Core Mechanics', 'Ecosystem Tools', 'Pros & Cons', 'Verdict'],
                'word_count' => 1200,
                'content_formats' => ['prose'],
                'weaknesses' => ['Superficial analysis', 'No real-world testing data', 'Missing enterprise use cases'],
            ],
        ];
    }
}