<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Create Content Mission Action
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

namespace App\Features\ContentIntelligence\Actions;

use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\Enums\ArticleArchetype;
use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateContentMission
{
    /**
     * Create a new Content Mission and initialize its dynamic workflow execution run.
     *
     * @param  ContentMissionDTO|array<string, mixed>  $missionData
     * @return array{mission: ContentMission, run: WorkflowRun}
     */
    public function execute(User $user, ContentMissionDTO|array $missionData, ?int $projectId = null): array
    {
        $dto = $missionData instanceof ContentMissionDTO
            ? $missionData
            : ContentMissionDTO::fromArray($missionData);

        return DB::transaction(function () use ($user, $dto, $projectId) {
            $mission = ContentMission::create([
                'user_id' => $user->id,
                'project_id' => $projectId,
                'topic' => $dto->topic,
                'primary_objective' => $dto->primaryObjective,
                'secondary_objectives' => $dto->secondaryObjectives,
                'target_audience' => $dto->targetAudience,
                'market_geo' => $dto->marketGeo,
                'language' => $dto->language,
                'content_type' => $dto->contentType,
                'article_archetype' => $dto->archetype,
                'business_goal' => $dto->businessGoal,
                'search_goal' => $dto->searchGoal,
                'brand_profile_id' => $dto->brandProfileId,
                'freshness_requirement' => $dto->freshnessRequirement,
                'trust_requirement' => $dto->trustRequirement,
                'evidence_requirement' => $dto->evidenceRequirement,
                'target_word_count_min' => $dto->targetWordCountRange['min'],
                'target_word_count_max' => $dto->targetWordCountRange['max'],
                'risk_level' => $dto->riskLevel,
                'research_budget_tier' => $dto->researchBudgetTier,
                'success_criteria' => $dto->successCriteria,
                'custom_constraints' => $dto->customConstraints,
                'status' => 'initialized',
            ]);

            $run = WorkflowRun::create([
                'mission_id' => $mission->id,
                'user_id' => $user->id,
                'current_node' => 'mission_intake',
                'status' => ContentWorkflowStatus::QUEUED,
                'graph_state' => [
                    'mission' => $dto->toArray(),
                    'history' => [],
                    'active_stage' => 'mission_intake',
                ],
                'overall_confidence' => 1.0000,
            ]);

            return [
                'mission' => $mission,
                'run' => $run,
            ];
        });
    }
}
