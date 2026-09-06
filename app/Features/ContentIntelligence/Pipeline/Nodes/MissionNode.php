<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Mission Intake Workflow Node
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

namespace App\Features\ContentIntelligence\Pipeline\Nodes;

use App\Features\ContentIntelligence\Contracts\WorkflowNodeInterface;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;

class MissionNode implements WorkflowNodeInterface
{
    public function getName(): string
    {
        return 'mission_intake';
    }

    public function getDescription(): string
    {
        return 'Validates and compiles the machine-readable content mission specification.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $mission = $run->mission;

        if (! $mission) {
            return WorkflowNodeResultDTO::failed('Workflow run does not have an associated Content Mission.');
        }

        $dto = $mission->toDTO();

        // Structured payload delivered to downstream stages
        $outputPayload = [
            'mission_id' => $mission->id,
            'mission' => $dto->toArray(),
            'machine_prompt' => $dto->toMachinePrompt(),
            'validated_at' => now()->toIso8601String(),
            'target_depth' => $dto->targetWordCountRange['max'] > 2500 ? 'deep_authority' : 'standard_overview',
            'required_evidence_level' => $dto->evidenceRequirement,
        ];

        return WorkflowNodeResultDTO::success(
            outputPayload: $outputPayload,
            confidence: 1.0,
            nextSuggestedNode: 'search_intelligence'
        );
    }
}
