<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Adaptive Outline Service
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

use App\Features\ContentIntelligence\DTOs\AdaptiveOutlineDTO;
use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\ResearchPlanDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\DTOs\SectionNodeDTO;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\ContentOutline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stage 5: Adaptive Outline & Section Dependency Graph Service
 *
 * NOW USES REAL AI GENERATION via DynamicContentProvider + OmniRoute Gateway
 * Synthesizes adaptive section nodes, target word count budgets, must-answer questions,
 * and dependency edges dynamically.
 */
class AdaptiveOutlineService
{
    /**
     * Alias for backward compatibility with feature tests.
     */
    public function build(
        ContentMission $mission,
        ?ContentBlueprint $blueprintModel,
        ContentBlueprintDTO $blueprint,
        KnowledgeFabricDTO $knowledgeFabric,
        ?ContentMissionDTO $missionDTO = null
    ): AdaptiveOutlineDTO {
        $mDto = $missionDTO ?? $mission->toDTO();
        $searchIntel = (new SearchIntelligenceService)->analyze($mDto);
        $plan = (new ResearchDirectorService)->formulatePlan($mDto, $searchIntel);

        return $this->synthesize($mission, $mDto, $searchIntel, $plan, $knowledgeFabric, $blueprint);
    }

    /**
     * Synthesize an Adaptive Outline from blueprint, search intel, and knowledge fabric.
     */
    public function synthesize(
        ContentMission $mission,
        ContentMissionDTO $missionDTO,
        SearchIntelligenceDTO $searchIntel,
        ?ResearchPlanDTO $plan,
        KnowledgeFabricDTO $knowledgeFabric,
        ContentBlueprintDTO $blueprint
    ): AdaptiveOutlineDTO {
        return DB::transaction(function () use ($mission, $missionDTO, $searchIntel, $knowledgeFabric, $blueprint) {
            $topic = $mission->topic;
            $thesis = $mission->primary_objective;
            $persona = $missionDTO->targetAudience['persona'] ?? 'Enterprise Practitioner';
            $expertise = $missionDTO->targetAudience['expertise_level'] ?? 'Intermediate';
            $targetWordCount = (int) (($missionDTO->targetWordCountRange['min'] + $missionDTO->targetWordCountRange['max']) / 2);
            if ($targetWordCount <= 0) {
                $targetWordCount = 2500;
            }

            $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->latest()->first();
            if (! $blueprintModel) {
                $blueprintModel = ContentBlueprint::create([
                    'mission_id' => $mission->id,
                    'article_angle' => $blueprint->articleAngle,
                    'unique_value_proposition' => $blueprint->uniqueValueProposition,
                    'target_reader_transformation' => $blueprint->targetReaderTransformation,
                    'required_sections' => $blueprint->requiredSections,
                    'optional_sections' => $blueprint->optionalSections,
                    'required_entities' => $blueprint->requiredEntities,
                    'required_claims' => $blueprint->requiredClaims,
                    'status' => 'approved',
                ]);
            }

            Log::info("[AdaptiveOutline] STEP 5: AI synthesizing Adaptive Outline for: '{$topic}'");

            // ══════════════════════════════════════════════════════════════
            // AI-Powered Outline & Must-Answer Questions Generation
            // ══════════════════════════════════════════════════════════════

            $sectionsList = implode("\n- ", $blueprint->requiredSections);

            $outlinePrompt = "You are a senior content architect. Create detailed section metadata for an authoritative guide on: \"{$topic}\"
Target Audience: {$persona} ({$expertise} level)
Target Word Count: {$targetWordCount} words

Blueprint Required Sections:
- {$sectionsList}

For each section in the list above, provide:
1. 'heading': exact or polished section heading
2. 'target_word_count': estimated words for this section (sum should be approx {$targetWordCount})
3. 'must_answer_questions': 2-3 hyper-specific technical questions this section must answer
4. 'assigned_entities': list of relevant entities/tools

Return JSON:
{
  \"sections\": [
    {
      \"heading\": \"...\",
      \"target_word_count\": 500,
      \"must_answer_questions\": [\"...\", \"...\"],
      \"assigned_entities\": [\"...\"]
    }
  ]
}";

            $aiOutline = DynamicContentProvider::askJSON($outlinePrompt, ['sections' => []]);
            $aiSections = $aiOutline['sections'] ?? [];

            $requiredList = $blueprint->requiredSections;
            if (count($requiredList) < 5) {
                $domain = ContentDomainClassifier::classify($topic, $thesis);
                if ($domain === ContentDomainClassifier::DOMAIN_GAMING) {
                    $requiredList = [
                        "Introduction to {$topic} & Modern PC Gaming Landscape",
                        "Sandbox & Creative Worlds: Minecraft & Roblox Deep-Dive",
                        "Competitive Action & Hero Shooters: Valorant, Fortnite & Deadlock",
                        "PC Hardware Optimization, Refresh Rates & Input Latency",
                        "Final Verdict, Community Recommendations & FAQ",
                    ];
                } else {
                    $requiredList = [
                        "Foundational Architecture & Executive Overview of {$topic}",
                        "Core Engine Mechanics, Internal Pipeline & Configuration",
                        "Practical Production Implementation & Deployment Guide",
                        "Performance Benchmarking, Latency Optimization & Security",
                        "Strategic Roadmap, Best Practices & Final Recommendations",
                    ];
                }
            }

            $sections = [];
            $totalReq = count($requiredList);
            $baseWordCount = (int) ($targetWordCount / max(1, $totalReq));
            $claims = $knowledgeFabric->claims;
            $claimsPerSection = max(1, (int) ceil(count($claims) / max(1, $totalReq)));
            $dependencyMap = [];

            foreach ($requiredList as $index => $heading) {
                $sectionKey = 'sec_' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $isFirst = $index === 0;
                $isLast = $index === ($totalReq - 1);

                // Match with AI outline if available
                $aiData = null;
                foreach ($aiSections as $as) {
                    if (str_contains(strtolower($as['heading'] ?? ''), strtolower(substr($heading, 0, 15)))) {
                        $aiData = $as;
                        break;
                    }
                }

                $mustAnswer = ! empty($aiData['must_answer_questions'])
                    ? $aiData['must_answer_questions']
                    : $this->generateSectionMustAnswerQuestions($heading, $topic);

                // Determine target word count
                $allocatedWords = ! empty($aiData['target_word_count'])
                    ? (int) $aiData['target_word_count']
                    : ($isFirst || $isLast ? (int) ($baseWordCount * 0.8) : $baseWordCount);

                // Assign subset of claims to this section
                $assignedClaims = array_slice($claims, $index * $claimsPerSection, $claimsPerSection);
                if (empty($assignedClaims) && ! empty($claims)) {
                    $assignedClaims = [$claims[$index % count($claims)]];
                }
                $assignedClaimIds = array_map(fn ($c) => $c->claimId ?? ($c->id ?? 'claim_0'), $assignedClaims);

                // Assign entities
                $assignedEntities = ! empty($aiData['assigned_entities'])
                    ? $aiData['assigned_entities']
                    : array_slice($searchIntel->targetEntities, $index * 2, 3);

                // Determine dependencies (sequential chain)
                $dependencies = $isFirst ? [] : ['sec_' . str_pad($index, 2, '0', STR_PAD_LEFT)];
                $dependencyMap[$sectionKey] = $dependencies;

                $sections[] = new SectionNodeDTO(
                    sectionId: $sectionKey,
                    heading: $heading,
                    level: 'H2',
                    intentRole: $isFirst ? 'Introduction' : ($isLast ? 'Conclusion' : 'Technical Guide'),
                    purpose: "Detailed analysis covering {$heading}",
                    targetWordCount: $allocatedWords,
                    mustAnswerQuestions: $mustAnswer,
                    assignedClaimIds: $assignedClaimIds,
                    requiredKeywords: array_slice($searchIntel->primaryQueries, 0, 3),
                    requiredEntities: $assignedEntities,
                    requiredCodeSnippets: [],
                    dependencySections: $dependencies,
                    writingPriority: $index + 1
                );
            }

            Log::info('[AdaptiveOutline] Created ' . count($sections) . " section nodes for '{$topic}'");

            $serializedSections = array_map(fn (SectionNodeDTO $s) => $s->toArray(), $sections);

            ContentOutline::create([
                'blueprint_id' => $blueprintModel->id,
                'mission_id' => $mission->id,
                'total_sections' => count($sections),
                'target_word_count' => $targetWordCount,
                'section_nodes' => $serializedSections,
                'status' => 'ready',
            ]);

            return new AdaptiveOutlineDTO(
                totalSections: count($sections),
                targetWordCount: $targetWordCount,
                sections: $sections,
                sectionDependencyMap: $dependencyMap,
                outlineConfidence: 0.95
            );
        });
    }

    /**
     * Fallback section-specific questions generator.
     */
    protected function generateSectionMustAnswerQuestions(string $heading, string $topic): array
    {
        $hLower = strtolower($heading);

        if (str_contains($hLower, 'intro') || str_contains($hLower, 'overview') || str_contains($hLower, 'landscape')) {
            return [
                "What is the current state and significance of {$topic}?",
                "What are the primary factors driving engagement and adoption in {$topic}?",
            ];
        }

        if (str_contains($hLower, 'mechanic') || str_contains($hLower, 'architecture') || str_contains($hLower, 'engine') || str_contains($hLower, 'feature')) {
            return [
                "What are the core technical systems and mechanics governing {$heading}?",
                "How does {$heading} compare against alternative approaches in the market?",
            ];
        }

        if (str_contains($hLower, 'spec') || str_contains($hLower, 'hardware') || str_contains($hLower, 'requirement') || str_contains($hLower, 'optimization')) {
            return [
                "What hardware specifications and configurations yield maximum performance?",
                "How can practitioners optimize latency, frame rates, and resource utilization?",
            ];
        }

        return [
            "What actionable takeaways and methodologies should be applied to {$heading}?",
            "What best practices ensure long-term stability, security, and quality?",
        ];
    }
}
