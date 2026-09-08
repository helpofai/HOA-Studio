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

            $sectionPrompt = "Create a structured, publication-grade article outline for an authoritative guide on: \"{$topic}\"
Target Audience: {$persona} ({$expertise} level)
Word Count Target: {$minWords}-{$maxWords} words

Core Objective & Inquiries to cover:
{$thesis}

Generate 5-7 required sections that directly address and answer the user's core inquiries and cover the full technical landscape of \"{$topic}\".
Each section heading must be specific, compelling, and relevant (NOT generic like 'Introduction' or 'Key Concepts').

Return JSON:
{
  \"required_sections\": [
    \"Heading 1: Direct answer to primary question/overview\",
    \"Heading 2: Deep technical mechanism or comparison\",
    \"Heading 3: Architecture & capability breakdown\",
    \"Heading 4: Practical implementation & workflows\",
    \"Heading 5: Enterprise considerations & roadmap\"
  ],
  \"optional_sections\": [\"Advanced benchmarks\", \"Ecosystem FAQ\"]
}";

            $fallbackSections = $this->buildFallbackSections($topic, $thesis);

            $aiSections = DynamicContentProvider::askJSON($sectionPrompt, [
                'required_sections' => $fallbackSections,
                'optional_sections' => ["Frequently Asked Questions", "Performance Benchmarks & Comparisons"]
            ]);

            $requiredSections = $aiSections['required_sections'] ?? [];
            $optionalSections = $aiSections['optional_sections'] ?? ["Frequently Asked Questions", "Performance Benchmarks & Comparisons"];

            // If empty or fewer than 4 sections returned, populate with topic & inquiry-grounded headers
            if (count($requiredSections) < 4) {
                $requiredSections = $fallbackSections;
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

    /**
     * Extract specific user questions/inquiries from the thesis/objective string.
     */
    protected function extractUserInquiries(string $thesis, string $topic): array
    {
        $inquiries = [];

        // Check for questions ending in '?' or separate lines/bullets
        $lines = preg_split('/(?:\r\n|\r|\n|\?)/u', $thesis, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $trimmed = trim(preg_replace('/^[\s\-\*\d\.\)]+/', '', $line));
            if (strlen($trimmed) > 8) {
                // If it looks like a question or substantive inquiry
                if (preg_match('/^(what|how|why|does|can|is|are|which|when|where|who|will|should)/i', $trimmed)) {
                    $inquiry = rtrim($trimmed, '?') . '?';
                    if (!in_array($inquiry, $inquiries)) {
                        $inquiries[] = $inquiry;
                    }
                } elseif (strlen($trimmed) > 15 && !in_array($trimmed, $inquiries)) {
                    $inquiries[] = $trimmed;
                }
            }
        }

        return $inquiries;
    }

    /**
     * Build high-quality, topic & inquiry grounded section headings.
     */
    protected function buildFallbackSections(string $topic, string $thesis): array
    {
        $inquiries = $this->extractUserInquiries($thesis, $topic);
        $cleanTopic = ucwords(trim($topic));
        $sections = [];

        if (!empty($inquiries) && count($inquiries) >= 2) {
            foreach ($inquiries as $inq) {
                $cleanInq = rtrim($inq, '?');
                $sections[] = ucfirst($cleanInq) . (str_ends_with($inq, '?') ? '?' : '');
            }
            // Add an operational / deployment section if fewer than 5 sections
            if (count($sections) < 5) {
                $sections[] = "Practical Implementation, Workflows & Best Practices for {$cleanTopic}";
            }
        } else {
            $sections = [
                "What Is {$cleanTopic} & How Does It Work?",
                "Core Architecture, Capabilities & Engine Mechanics",
                "Key Features, Integrations & Real-World Use Cases",
                "Practical Deployment Workflows & Configuration Guide",
                "Strategic Roadmap, Best Practices & Performance Optimization"
            ];
        }

        return array_values(array_unique($sections));
    }
}