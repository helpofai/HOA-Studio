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
|
|--------------------------------------------------------------------------
*/

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\AdaptiveOutlineDTO;
use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\SectionNodeDTO;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\ContentOutline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdaptiveOutlineService
{
    /**
     * Build an adaptive, dependency-aware hierarchical outline tree
     * grounding sections with verified claims, entities, and questions.
     *
     * NOW USES REAL AI to generate specific section directives and must-answer questions.
     */
    public function build(
        ContentMission $mission,
        ContentBlueprint $blueprintModel,
        ContentBlueprintDTO $blueprint,
        KnowledgeFabricDTO $knowledgeFabric,
        ContentMissionDTO $missionDTO
    ): AdaptiveOutlineDTO {
        return DB::transaction(function () use ($mission, $blueprintModel, $blueprint, $knowledgeFabric, $missionDTO) {
            $requiredSections = $blueprint->requiredSections;
            $totalCount = count($requiredSections);
            $targetWordCount = (int) round(($missionDTO->targetWordCountRange['min'] + $missionDTO->targetWordCountRange['max']) / 2);
            $wordsPerSection = (int) round($targetWordCount / max(1, $totalCount));
            $topic = $missionDTO->topic;
            $expertise = is_array($missionDTO->targetAudience) ? ($missionDTO->targetAudience['expertise_level'] ?? 'Intermediate') : 'Intermediate';

            Log::info("[AdaptiveOutline] STEP 5: AI generating outline directives for {$totalCount} sections");

            // Build full context of claims available
            $claimsList = [];
            foreach ($knowledgeFabric->claims as $claim) {
                $claimsList[] = [
                    'id' => $claim->claimId,
                    'statement' => $claim->statement,
                    'section' => $claim->sectionTarget
                ];
            }

            // Map available claim IDs from the verified Claim Graph
            $availableClaimIds = array_map(fn ($c) => $c->claimId, $knowledgeFabric->claims);

            // ══════════════════════════════════════════════════════════════
            // AI-Powered Outline Enhancement
            // ══════════════════════════════════════════════════════════════

            // For complex documents, we ask AI to enhance each section with specific directions
            // To save time and keep it reliable, we bundle them into one prompt

            $sectionHeadingsList = json_encode($requiredSections);
            $claimsListJson = json_encode($claimsList);

            $outlinePrompt = "Generate detailed directives for an article outline about \"{$topic}\".
Expertise Level: {$expertise}

Sections provided:
{$sectionHeadingsList}

Claims available to use:
{$claimsListJson}

For each section, define:
1. role (e.g. 'Introduction', 'Deep-Dive Technical Analysis', 'Conclusion')
2. must_answer_questions (2 specific questions this section must answer)
3. assigned_claim_ids (array of claim IDs from the list provided that fit this section - maximum 2 per section)
4. media_placeholder (if a chart, table, or diagram is needed, e.g. 'architecture_flow_diagram' or null)

Return strictly valid JSON:
{
  \"sections\": [
    {
      \"heading\": \"Exact section heading\",
      \"role\": \"Role description\",
      \"must_answer_questions\": [\"Question 1?\", \"Question 2?\"],
      \"assigned_claim_ids\": [\"clm_xxx\"],
      \"media_placeholder\": \"placeholder_name_or_null\"
    }
  ]
}";

            // We use the dynamic provider to get the structured JSON outline schema
            $aiOutline = DynamicContentProvider::askJSON($outlinePrompt, ['sections' => []]);
            $aiSectionData = [];
            if (!empty($aiOutline['sections'])) {
                foreach ($aiOutline['sections'] as $sData) {
                    if (isset($sData['heading'])) {
                        $aiSectionData[$sData['heading']] = $sData;
                    }
                }
            }

            $sections = [];
            $dependencyMap = [];

            foreach ($requiredSections as $index => $heading) {
                $sectionNum = $index + 1;
                $sectionId = sprintf('sec_%02d', $sectionNum);

                // Try to get AI generated data for this section, fallback to smart defaults
                $sData = $aiSectionData[$heading] ?? [];

                // Assign subset of claims to each section
                $assignedClaims = $sData['assigned_claim_ids'] ?? [];
                // If AI didn't assign any, fallback to round-robin
                if (empty($assignedClaims) && !empty($availableClaimIds)) {
                    $assignedClaims[] = $availableClaimIds[$index % count($availableClaimIds)];
                }

                $dependencies = ($index > 0) ? [sprintf('sec_%02d', $index)] : [];
                $dependencyMap[$sectionId] = $dependencies;

                // Smart role resolution
                $defaultRole = match (true) {
                    $index === 0 => 'Introduction & High-Level Architecture',
                    $index === $totalCount - 1 => 'Conclusion & Actionable Checklist',
                    str_contains(strtolower($heading), 'benchmark') || str_contains(strtolower($heading), 'compare') => 'Empirical Benchmark Analysis',
                    str_contains(strtolower($heading), 'troubleshoot') || str_contains(strtolower($heading), 'error') => 'Troubleshooting & Pitfall Resolution',
                    str_contains(strtolower($heading), 'config') || str_contains(strtolower($heading), 'setup') => 'Practical Technical Implementation',
                    default => 'Deep-Dive Technical Analysis',
                };

                $role = $sData['role'] ?? $defaultRole;

                $defaultMedia = match (true) {
                    str_contains(strtolower($heading), 'architecture') || str_contains(strtolower($heading), 'structure') => 'architecture_flow_diagram',
                    str_contains(strtolower($heading), 'benchmark') || str_contains(strtolower($heading), 'compare') => 'throughput_comparison_table',
                    default => null,
                };

                $mediaPlaceholder = $sData['media_placeholder'] ?? $defaultMedia;
                if ($mediaPlaceholder === 'null' || $mediaPlaceholder === '') $mediaPlaceholder = null;

                $mustAnswer = $sData['must_answer_questions'] ?? [
                    "What are the core mechanics of {$heading}?",
                    "How does this apply to enterprise environments?"
                ];

                $sections[] = new SectionNodeDTO(
                    sectionId: $sectionId,
                    heading: $heading,
                    level: 'H2',
                    intentRole: $role,
                    purpose: "Thoroughly address {$heading} with verified evidence and proper technical depth.",
                    targetWordCount: $wordsPerSection,
                    mustAnswerQuestions: $mustAnswer,
                    assignedClaimIds: $assignedClaims,
                    requiredKeywords: array_slice($missionDTO->secondaryObjectives, 0, 2),
                    requiredEntities: array_slice($blueprint->requiredEntities, 0, 3),
                    requiredCodeSnippets: str_contains(strtolower($heading), 'code') || str_contains(strtolower($heading), 'script') ? ['example_snippet.js'] : [],
                    dependencySections: $dependencies,
                    mediaPlaceholder: $mediaPlaceholder,
                    writingPriority: $sectionNum
                );
            }

            Log::info("[AdaptiveOutline] STEP 5 COMPLETE: Outline graph prepared");

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
                outlineConfidence: 0.98
            );
        });
    }
}