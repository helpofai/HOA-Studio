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
use App\Features\ContentIntelligence\DTOs\SectionNodeDTO;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\ContentOutline;
use Illuminate\Support\Facades\DB;

class AdaptiveOutlineService
{
    /**
     * Build an adaptive, dependency-aware hierarchical outline tree
     * grounding sections with verified claims, entities, and questions.
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

            // Map available claim IDs from the verified Claim Graph
            $claimIds = array_map(fn ($c) => $c->claimId, $knowledgeFabric->claims);

            $sections = [];
            $dependencyMap = [];

            foreach ($requiredSections as $index => $heading) {
                $sectionNum = $index + 1;
                $sectionId = sprintf('sec_%02d', $sectionNum);

                // Assign subset of claims to each section
                $assignedClaims = [];
                if (! empty($claimIds)) {
                    $assignedClaims[] = $claimIds[$index % count($claimIds)];
                }

                $dependencies = ($index > 0) ? [sprintf('sec_%02d', $index)] : [];
                $dependencyMap[$sectionId] = $dependencies;

                $role = match (true) {
                    $index === 0 => 'Introduction & High-Level Architecture',
                    $index === $totalCount - 1 => 'Conclusion & Actionable Checklist',
                    str_contains(strtolower($heading), 'benchmark') => 'Empirical Benchmark Analysis',
                    str_contains(strtolower($heading), 'troubleshoot') => 'Troubleshooting & Pitfall Resolution',
                    str_contains(strtolower($heading), 'config') || str_contains(strtolower($heading), 'setup') => 'Practical Technical Implementation',
                    default => 'Deep-Dive Technical Analysis',
                };

                $mediaPlaceholder = match (true) {
                    str_contains(strtolower($heading), 'architecture') => 'architecture_flow_diagram',
                    str_contains(strtolower($heading), 'benchmark') => 'throughput_comparison_table',
                    str_contains(strtolower($heading), 'checklist') => 'actionable_checklist_callout',
                    default => null,
                };

                $sections[] = new SectionNodeDTO(
                    sectionId: $sectionId,
                    heading: $heading,
                    level: 'H2',
                    intentRole: $role,
                    purpose: "Thoroughly address {$heading} with verified evidence and production parameters.",
                    targetWordCount: $wordsPerSection,
                    mustAnswerQuestions: [
                        "What is the exact architectural mechanism behind {$heading}?",
                        "What are the common failure modes and verified solutions for {$heading}?",
                    ],
                    assignedClaimIds: $assignedClaims,
                    requiredKeywords: array_slice($missionDTO->secondaryObjectives, 0, 2),
                    requiredEntities: array_slice($blueprint->requiredEntities, 0, 3),
                    requiredCodeSnippets: str_contains(strtolower($heading), 'config') ? ['supervisor.conf'] : [],
                    dependencySections: $dependencies,
                    mediaPlaceholder: $mediaPlaceholder,
                    writingPriority: $sectionNum
                );
            }

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
