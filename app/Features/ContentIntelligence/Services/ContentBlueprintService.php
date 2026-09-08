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
use App\Features\ContentIntelligence\DTOs\ResearchPlanDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\Enums\ArticleArchetype;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\ContentMission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stage 4: Strategic Content Blueprint Service
 *
 * Synthesizes article angle, unique value proposition, target transformation,
 * required/optional sections, and required entities directly from research and Article Archetypes.
 */
class ContentBlueprintService
{
    /**
     * Alias for backward compatibility with older tests.
     */
    public function generate(
        ContentMission $mission,
        ContentMissionDTO $missionDTO,
        SearchIntelligenceDTO $searchIntel,
        KnowledgeFabricDTO $knowledgeFabric,
        ?ResearchPlanDTO $plan = null
    ): ContentBlueprintDTO {
        $director = new ResearchDirectorService;
        $planObj = $plan ?? $director->formulatePlan($missionDTO, $searchIntel);
        return $this->synthesize($mission, $missionDTO, $searchIntel, $planObj, $knowledgeFabric);
    }

    /**
     * Synthesize a Strategic Content Blueprint from mission, search intelligence, and knowledge fabric.
     */
    public function synthesize(
        ContentMission $mission,
        ContentMissionDTO $missionDTO,
        SearchIntelligenceDTO $searchIntel,
        ?ResearchPlanDTO $plan,
        KnowledgeFabricDTO $knowledgeFabric
    ): ContentBlueprintDTO {
        return DB::transaction(function () use ($mission, $missionDTO, $searchIntel, $knowledgeFabric) {
            $topic = $mission->topic;
            $thesis = $mission->primary_objective;
            $persona = $missionDTO->targetAudience['persona'] ?? 'Enterprise Practitioner';
            $expertise = $missionDTO->targetAudience['expertise_level'] ?? 'Intermediate';
            $minWords = $missionDTO->targetWordCountRange['min'] ?? 1500;
            $maxWords = $missionDTO->targetWordCountRange['max'] ?? 3000;
            $archetype = $missionDTO->archetype;

            Log::info("[ContentBlueprint] STEP 4: AI synthesizing Content Blueprint for: '{$topic}' (Archetype: {$archetype->value})");

            // ══════════════════════════════════════════════════════════════
            // AI-Powered Strategic Angle & UVP Generation
            // ══════════════════════════════════════════════════════════════

            $anglePrompt = "Synthesize an authoritative strategic blueprint for an in-depth publication on: \"{$topic}\"
Article Archetype: {$archetype->label()} ({$archetype->description()})
Target Audience: {$persona} ({$expertise} level)
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
                'target_transformation' => ['current_pain_points' => [], 'desired_mastery' => "Master {$topic}"],
            ]);

            $articleAngle = $aiBlueprint['article_angle'] ?? "Comprehensive guide to {$topic}";
            $uvp = $aiBlueprint['unique_value_proposition'] ?? "Expert insights on {$topic} for {$persona}";
            $targetTransformation = $aiBlueprint['target_transformation'] ?? [
                'current_pain_points' => $missionDTO->targetAudience['pain_points'] ?? ['Limited understanding of the topic'],
                'desired_mastery' => "Complete understanding and practical mastery of {$topic}",
            ];

            // ══════════════════════════════════════════════════════════════
            // AI-Powered Archetype-Aware Section Structure Generation
            // ══════════════════════════════════════════════════════════════

            $fallbackSections = $this->buildFallbackSections($topic, $thesis, $archetype);

            $sectionPrompt = "Create a structured, publication-grade article outline for an authoritative {$archetype->label()} on: \"{$topic}\"
Article Archetype: {$archetype->value} ({$archetype->description()})
Target Audience: {$persona} ({$expertise} level)
Word Count Target: {$minWords}-{$maxWords} words

Core Objective & Inquiries to cover:
{$thesis}

Generate 5-8 required sections that strictly follow the structural expectations of a {$archetype->label()}.
Each section heading must be specific, compelling, and relevant.

Return JSON:
{
  \"required_sections\": [
    \"Heading 1\",
    \"Heading 2\",
    \"Heading 3\",
    \"Heading 4\",
    \"Heading 5\"
  ],
  \"optional_sections\": [\"Advanced benchmarks & FAQ\"]
}";

            $aiSections = DynamicContentProvider::askJSON($sectionPrompt, [
                'required_sections' => $fallbackSections,
                'optional_sections' => ['Frequently Asked Questions', 'Performance Benchmarks & Comparisons'],
            ]);

            $requiredSections = $aiSections['required_sections'] ?? [];
            $optionalSections = $aiSections['optional_sections'] ?? ['Frequently Asked Questions', 'Performance Benchmarks & Comparisons'];

            // If empty or fewer than 4 sections returned, populate with archetype & inquiry-grounded headers
            if (count($requiredSections) < 4) {
                $requiredSections = $fallbackSections;
            }

            Log::info('[ContentBlueprint] Generated ' . count($requiredSections) . ' required sections for archetype ' . $archetype->value);

            // External sources extracted from verified Knowledge Fabric
            $externalSources = array_map(fn ($src) => $src->url, $knowledgeFabric->sources);

            // Internal links - make them topic-relevant
            $internalLinks = [
                '/dashboard/content-intelligence?topic=' . urlencode($topic),
                '/blog?tag=' . strtolower(str_replace(' ', '-', $topic)),
            ];

            // FAQ Requirements from search intelligence
            $faqRequirements = [];
            if (! empty($searchIntel->queryClusters['paa_questions'])) {
                $faqRequirements = array_slice($searchIntel->queryClusters['paa_questions'], 0, 4);
            } else {
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

            Log::info('[ContentBlueprint] STEP 4 COMPLETE: Blueprint approved');

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
     * Extract specific user questions, named entities, or comparison items from the thesis string.
     */
    protected function extractUserInquiries(string $thesis, string $topic): array
    {
        $cleanThesis = ContentDomainClassifier::cleanRawText($thesis);
        $inquiries = [];

        // 1. Extract explicit entities mentioned in the thesis
        $extractedItems = ContentDomainClassifier::extractEntitiesFromThesis($cleanThesis, $topic);
        if (count($extractedItems) >= 2) {
            $inquiries[] = 'Introduction to ' . ucwords(trim($topic)) . ' & Landscape Overview';
            foreach ($extractedItems as $item) {
                $inquiries[] = "{$item}: Gameplay, Features & Player Experience";
            }
            $inquiries[] = 'Performance, System Requirements & Cross-Platform Comparison';
            $inquiries[] = 'Final Verdict & Best Recommendations for Players';

            return $inquiries;
        }

        // 2. Check for named items formatted as "Item Name: Description"
        if (preg_match_all('/(?:Top Alternatives\s*)?([A-Za-z0-9][A-Za-z0-9\s\(\)\:\/\-]{2,30}):\s*([A-Z][^:]+?)(?=(?:[A-Za-z0-9\s\(\)\:\/\-]{2,30}:)|$)/u', $cleanThesis, $matches, PREG_SET_ORDER)) {
            $extracted = [];
            foreach ($matches as $match) {
                $itemName = trim($match[1]);
                if (! preg_match('/^(top alternatives|alternatives|features|modes|options|note|summary)$/i', $itemName) && strlen($itemName) >= 3 && strlen($itemName) <= 35) {
                    $extracted[] = $itemName;
                }
            }

            if (count($extracted) >= 2) {
                $inquiries[] = 'Top Alternatives & Standout Options in ' . ucwords(trim($topic));
                foreach ($extracted as $item) {
                    $inquiries[] = "{$item}: Gameplay, Features & Player Experience";
                }
                $inquiries[] = 'Technical Specs, Performance & Hardware Optimization';
                $inquiries[] = 'Final Verdict & Strategic Recommendations';

                return $inquiries;
            }
        }

        // 3. Check for questions ending in '?' or interrogative statements
        $lines = preg_split('/(?:\r\n|\r|\n|\?)/u', $cleanThesis, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $trimmed = trim(preg_replace('/^[\s\-\*\d\.\)]+/', '', $line));
            if (strlen($trimmed) > 8 && strlen($trimmed) <= 85) {
                if (preg_match('/^(what|how|why|does|can|is|are|which|when|where|who|will|should)/i', $trimmed)) {
                    $inquiry = rtrim($trimmed, '?') . '?';
                    $words = explode(' ', $inquiry);
                    if (count($words) <= 12) {
                        $inquiries[] = ucfirst($inquiry);
                    }
                } elseif (preg_match('/^(step|phase|part|stage|method|approach|technique|mechanism|architecture|strategy|benchmarks?)\b/i', $trimmed)) {
                    $words = explode(' ', $trimmed);
                    if (count($words) <= 8) {
                        $inquiries[] = ucfirst(trim($trimmed, '.'));
                    }
                }
            }
        }

        return $inquiries;
    }

    /**
     * Build high-quality, archetype-grounded section headings.
     */
    protected function buildFallbackSections(string $topic, string $thesis, ArticleArchetype $archetype = ArticleArchetype::AUTO_DETECT): array
    {
        $inquiries = $this->extractUserInquiries($thesis, $topic);
        $cleanTopic = ucwords(trim($topic));

        // If explicit named entities / items were found in thesis, honor them
        if (! empty($inquiries) && count($inquiries) >= 3) {
            return array_values(array_unique($inquiries));
        }

        // If specific archetype requested (other than auto-detect), use archetype canonical templates
        if ($archetype !== ArticleArchetype::AUTO_DETECT) {
            return $archetype->defaultSectionTemplates($topic);
        }

        // Fall back to domain classification
        $domain = ContentDomainClassifier::classify($topic, $thesis);

        return match ($domain) {
            ContentDomainClassifier::DOMAIN_GAMING => [
                "Introduction to {$cleanTopic} & Gameplay Overview",
                "Top Titles, Alternatives & Gameplay Dynamics",
                "Core Mechanics, Controls & Graphic Quality Comparison",
                "Hardware Requirements, Frame Rate Optimization & Settings",
                "Final Verdict & Best Recommendations for Players",
            ],
            ContentDomainClassifier::DOMAIN_SOFTWARE => [
                "Overview of {$cleanTopic} & Core Architecture",
                "Key Features, Framework Capabilities & Developer Workflow",
                "Step-by-Step Implementation & Configuration Guide",
                "Performance Optimization, Scaling & Error Handling",
                "Production Best Practices & Deployment Blueprint",
            ],
            ContentDomainClassifier::DOMAIN_BUSINESS => [
                "Market Overview & Strategic Value of {$cleanTopic}",
                "Core Strategies, Methodologies & Implementation Playbook",
                "Key Tools, Platforms & Competitive Analysis",
                "ROI Optimization, Risk Management & Execution Framework",
                "Long-Term Growth & Future Industry Trends",
            ],
            ContentDomainClassifier::DOMAIN_HEALTH => [
                "Understanding {$cleanTopic}: Core Principles & Key Benefits",
                "Essential Techniques, Step-by-Step Guide & Best Practices",
                "Common Pitfalls, Safety Considerations & Practical Tips",
                "Personalized Routines & Daily Implementation Strategies",
                "Long-Term Maintenance & Expert Recommendations",
            ],
            default => [
                "What Is {$cleanTopic} & How Does It Work?",
                "Core Architecture, Capabilities & Engine Mechanics",
                "Key Features, Integrations & Real-World Use Cases",
                "Practical Deployment Workflows & Configuration Guide",
                "Strategic Roadmap, Best Practices & Performance Optimization",
            ],
        };
    }
}
