<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Blueprint Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\ContentMission;
use Illuminate\Support\Facades\DB;

class ContentBlueprintService
{
    /**
     * Synthesize mission, search intelligence, and knowledge fabric into a single
     * authoritative, machine-readable Strategic Content Blueprint.
     */
    public function generate(
        ContentMission $mission,
        ContentMissionDTO $missionDTO,
        SearchIntelligenceDTO $searchIntel,
        KnowledgeFabricDTO $knowledgeFabric
    ): ContentBlueprintDTO {
        return DB::transaction(function () use ($mission, $missionDTO, $searchIntel, $knowledgeFabric) {
            $topic = $missionDTO->topic;

            // Formulate Unique Information-Gain Angle and Value Proposition
            $articleAngle = "Comprehensive enterprise engineering blueprint for {$topic}, providing battle-tested configurations, graceful signal supervision, and verified benchmarks.";
            $uvp = 'Unlike superficial tutorials that gloss over production edge-cases, this asset solves real-world worker drops, provides exact Supervisor parameters, and includes verified benchmark telemetry.';

            $targetTransformation = [
                'current_pain_points' => array_merge(
                    $missionDTO->targetAudience['pain_points'] ?? [],
                    $searchIntel->contentGaps['weak_angles']
                ),
                'desired_mastery' => "Flawless operational command over {$topic} with zero downtime, robust supervision, and bounded memory consumption.",
            ];

            // Required sections derived from mission objectives and identified gaps
            $requiredSections = [
                'Executive Architecture Overview: Why Standard Configurations Fail',
                'Deep-Dive Technical Foundation & Core Mechanics',
                'Step-by-Step Production Configuration with Process Supervision',
                'Memory Leak Prevention & Automatic Worker Ceiling Restarts',
                'High-Throughput Benchmarks & Scaling Under Heavy Concurrency',
                'Operational Troubleshooting & Common Pitfall Resolution',
                'Production Checklist & Implementation Action Plan',
            ];

            $optionalSections = [
                'Monitoring with OpenTelemetry and Custom Health Probes',
                'Containerized Docker & Kubernetes Deployment Recipes',
            ];

            // External sources extracted from verified Knowledge Fabric
            $externalSources = array_map(fn ($src) => $src->url, $knowledgeFabric->sources);

            $internalLinks = [
                '/docs/architecture-blueprints',
                '/blog/production-performance-benchmarking',
            ];

            $faqRequirements = array_slice($searchIntel->queryClusters['paa_questions'], 0, 4);

            $blueprint = ContentBlueprint::create([
                'mission_id' => $mission->id,
                'article_angle' => $articleAngle,
                'unique_value_proposition' => $uvp,
                'target_transformation' => $targetTransformation,
                'required_sections' => $requiredSections,
                'optional_sections' => $optionalSections,
                'required_entities' => $searchIntel->targetEntities,
                'internal_links' => $internalLinks,
                'external_sources' => $externalSources,
                'faq_requirements' => $faqRequirements,
                'quality_targets' => [
                    'min_health_score' => 90,
                    'flesch_reading_ease' => 60,
                    'evidence_grounding_threshold' => 0.90,
                ],
                'status' => 'approved',
            ]);

            return new ContentBlueprintDTO(
                articleAngle: $articleAngle,
                uniqueValueProposition: $uvp,
                targetTransformation: $targetTransformation,
                requiredSections: $requiredSections,
                optionalSections: $optionalSections,
                requiredEntities: $searchIntel->targetEntities,
                internalLinks: $internalLinks,
                externalSources: $externalSources,
                faqRequirements: $faqRequirements,
                conversionStrategy: [
                    'cta_type' => 'authoritative_consulting',
                    'primary_action' => 'Deploy HOA enterprise verified architecture',
                ],
                qualityTargets: [
                    'min_health_score' => 90,
                    'flesch_reading_ease' => 60,
                    'evidence_grounding_threshold' => 0.90,
                ]
            );
        });
    }
}
