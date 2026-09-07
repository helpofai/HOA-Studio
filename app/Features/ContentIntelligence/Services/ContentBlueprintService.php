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
use Illuminate\Support\Facades\Log;

class ContentBlueprintService
{
    /**
     * Synthesize mission, search intelligence, and knowledge fabric into a single
     * authoritative, machine-readable Strategic Content Blueprint.
     *
     * NOW USES REAL AI to generate dynamic article structure based on topic
     */
    public function generate(
        ContentMission $mission,
        ContentMissionDTO $missionDTO,
        SearchIntelligenceDTO $searchIntel,
        KnowledgeFabricDTO $knowledgeFabric
    ): ContentBlueprintDTO {
        return DB::transaction(function () use ($mission, $missionDTO, $searchIntel, $knowledgeFabric) {
            $topic = $missionDTO->topic;
            $persona = is_array($missionDTO->targetAudience) ? ($missionDTO->targetAudience['persona'] ?? 'General Technical Audience') : (string) $missionDTO->targetAudience;
            $expertise = is_array($missionDTO->targetAudience) ? ($missionDTO->targetAudience['expertise_level'] ?? 'Intermediate') : 'Intermediate';
            $riskLevel = $missionDTO->riskLevel instanceof \App\Features\ContentIntelligence\Enums\RiskLevel ? $missionDTO->riskLevel->value : (string) ($missionDTO->riskLevel ?? 'medium');
            $thesis = $missionDTO->primaryObjective ?? $topic;
            $minWords = $missionDTO->targetWordCountRange['min'] ?? 1800;
            $maxWords = $missionDTO->targetWordCountRange['max'] ?? 3500;

            Log::info("[ContentBlueprint] STEP 4: AI generating article blueprint for: {$topic}");

            // ══════════════════════════════════════════════════════════════
            // AI-Powered Article Angle and Value Proposition
            // ══════════════════════════════════════════════════════════════

            $anglePrompt = "You are a content strategist. Create a compelling article angle and unique value proposition for an article about: \"{$topic}\"
Target Audience: {$persona}
Expertise Level: {$expertise}
Primary Thesis: {$thesis}
Word Count Target: {$minWords}-{$maxWords} words

Return JSON with:
{
  \"article_angle\": \"A compelling 1-sentence angle that captures the unique value of this article\",
  \"unique_value_proposition\": \"Why this article is different from others on this topic - what specific value does it provide?\",
  \"target_transformation\": {
    \"current_pain_points\": [\"What problems does the reader currently face?\"],
    \"desired_mastery\": \"What will the reader be able to do after reading?\"
  }
}";

            $aiBlueprint = DynamicContentProvider::askJSON($anglePrompt, [
                'article_angle' => "Comprehensive guide to {$topic}",
                'unique_value_proposition' => "This article provides expert insights on {$topic}.",
                'target_transformation' => ['current_pain_points' => [], 'desired_mastery' => "Master {$topic}"]
            ]);

            $articleAngle = $aiBlueprint['article_angle'] ?? "Comprehensive guide to {$topic}";
            $uvp = $aiBlueprint['unique_value_proposition'] ?? "Expert insights on {$topic} for {$persona}";
            $targetTransformation = $aiBlueprint['target_transformation'] ?? [
                'current_pain_points' => $missionDTO->targetAudience['pain_points'] ?? ['Limited understanding of the topic'],
                'desired_mastery' => "Complete understanding and practical mastery of {$topic}"
            ];

            // ══════════════════════════════════════════════════════════════
            // AI-Powered Section Structure Generation
            // ══════════════════════════════════════════════════════════════

            $sectionPrompt = "Create an optimal article outline for: \"{$topic}\"
Target Audience: {$persona}
Expertise Level: {$expertise}
Word Count: {$minWords}-{$maxWords} words

The article should address these key questions from the thesis:
{$thesis}

Generate 5-7 required sections and 2-3 optional sections. Each section should:
- Have a clear, specific heading
- Be essential to covering this topic thoroughly
- Match the {$expertise} level of the audience

Return JSON:
{
  \"required_sections\": [\"Section 1 heading\", \"Section 2 heading\", ...],
  \"optional_sections\": [\"Optional section 1\", ...]
}";

            $aiSections = DynamicContentProvider::askJSON($sectionPrompt, [
                'required_sections' => ["Introduction to {$topic}", "Key Concepts", "Practical Applications", "Best Practices", "Conclusion"],
                'optional_sections' => ["Advanced Topics", "Case Studies"]
            ]);

            $requiredSections = $aiSections['required_sections'] ?? ["Introduction to {$topic}"];
            $optionalSections = $aiSections['optional_sections'] ?? [];

            // Ensure we have enough sections for a comprehensive article
            if (count($requiredSections) < 5) {
                $requiredSections = array_merge($requiredSections, [
                    "Understanding the Fundamentals",
                    "Deep Dive into Core Concepts",
                    "Practical Implementation Guide",
                    "Common Challenges and Solutions",
                    "Summary and Next Steps"
                ]);
            }

            Log::info("[ContentBlueprint] Generated " . count($requiredSections) . " required sections");

            // External sources extracted from verified Knowledge Fabric
            $externalSources = array_map(fn ($src) => $src->url, $knowledgeFabric->sources);

            // Internal links - make them topic-relevant
            $internalLinks = [
                "/dashboard/content-intelligence?topic=" . urlencode($topic),
                "/blog?tag=" . strtolower(str_replace(' ', '-', $topic)),
            ];

            // FAQ Requirements from search intelligence
            $faqRequirements = [];
            if (!empty($searchIntel->queryClusters['paa_questions'])) {
                $faqRequirements = array_slice($searchIntel->queryClusters['paa_questions'], 0, 4);
            } else {
                // Generate FAQs via AI if PAA not available
                $faqPrompt = "Generate 3-5 frequently asked questions about: \"{$topic}\" for {$persona} at {$expertise} level.
Return JSON: {\"faqs\": [\"Question 1?\", \"Question 2?\", ...]}";
                $aiFaqs = DynamicContentProvider::askJSON($faqPrompt, ['faqs' => []]);
                $faqRequirements = $aiFaqs['faqs'] ?? [];
            }

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

            Log::info("[ContentBlueprint] STEP 4 COMPLETE: Blueprint approved");

            return new ContentBlueprintDTO(
                articleAngle: $blueprint->article_angle,
                uniqueValueProposition: $blueprint->unique_value_proposition,
                targetTransformation: $blueprint->target_transformation,
                requiredSections: $blueprint->required_sections,
                optionalSections: $blueprint->optional_sections,
                requiredEntities: $blueprint->required_entities,
                internalLinks: $blueprint->internal_links,
                externalSources: $blueprint->external_sources,
                faqRequirements: $blueprint->faq_requirements
            );
        });
    }
}