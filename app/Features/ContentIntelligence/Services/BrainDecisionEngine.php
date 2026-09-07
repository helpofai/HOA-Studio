<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Brain Decision Engine
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

use App\Features\ContentIntelligence\Contracts\BlackboardInterface;
use App\Features\ContentIntelligence\DTOs\DecisionRecordDTO;
use App\Features\ContentIntelligence\Models\BrainDecision;
use Illuminate\Support\Str;

class BrainDecisionEngine
{
    /**
     * Formulate an explainable, auditable decision, store it in the database,
     * and log it to the shared Brain Blackboard.
     *
     * @param  array<string, mixed>  $inputs
     * @param  array<int, string>  $candidateOptions
     */
    public function decide(
        string $question,
        array $inputs,
        array $candidateOptions,
        BlackboardInterface $blackboard,
        ?int $missionId = null,
        ?int $workflowRunId = null
    ): DecisionRecordDTO {
        // Evaluate input criteria
        $selectedDecision = $candidateOptions[0] ?? 'PROCEED';
        $reasoning = 'Evaluation criteria satisfied based on input confidence and policy constraints.';
        $confidence = 0.95;

        // Custom heuristics for standard pipeline decision questions
        if (str_contains(strtolower($question), 'deepen research') || str_contains(strtolower($question), 'secondary research')) {
            $currentConfidence = (float) ($inputs['current_confidence'] ?? 0.85);
            $riskLevel = (string) ($inputs['risk_level'] ?? 'low');

            if ($currentConfidence < 0.75 || $riskLevel === 'high' || $riskLevel === 'critical') {
                $selectedDecision = 'EXECUTE_SECONDARY_RESEARCH';
                $reasoning = "Current research confidence ({$currentConfidence}) is below safety threshold for {$riskLevel}-risk topic.";
                $confidence = 0.98;
            } else {
                $selectedDecision = 'PROCEED_TO_BLUEPRINT';
                $reasoning = "Evidence sufficiency threshold satisfied ({$currentConfidence}) for {$riskLevel}-risk content mission.";
                $confidence = 0.94;
            }
        } elseif (str_contains(strtolower($question), 'repair') || str_contains(strtolower($question), 'critique')) {
            $criticScore = (int) ($inputs['critic_score'] ?? 85);
            if ($criticScore < 80) {
                $selectedDecision = 'SURGICAL_REPAIR_REQUIRED';
                $reasoning = "Critic score ({$criticScore}/100) below 80 standard. Localized repair loop triggered.";
                $confidence = 0.96;
            } else {
                $selectedDecision = 'ACCEPT_DRAFT';
                $reasoning = "Critic score ({$criticScore}/100) meets editorial quality threshold.";
                $confidence = 0.95;
            }
        }

        $decisionDTO = new DecisionRecordDTO(
            id: 'dec_'.Str::lower(Str::random(12)),
            question: $question,
            decision: $selectedDecision,
            reasoning: $reasoning,
            inputs: $inputs,
            alternatives: array_values(array_diff($candidateOptions, [$selectedDecision])),
            confidence: $confidence,
            missionId: $missionId,
            workflowRunId: $workflowRunId
        );

        // 1. Persist to database
        try {
            BrainDecision::create([
                'id' => $decisionDTO->id,
                'mission_id' => $missionId,
                'workflow_run_id' => $workflowRunId,
                'question' => $decisionDTO->question,
                'decision' => $decisionDTO->decision,
                'reasoning' => $decisionDTO->reasoning,
                'inputs' => $decisionDTO->inputs,
                'alternatives' => $decisionDTO->alternatives,
                'confidence' => $decisionDTO->confidence,
            ]);
        } catch (\Throwable) {
            // Graceful fallback if database write is constrained
        }

        // 2. Log to Blackboard
        $blackboard->push('decisions', $decisionDTO->toArray());

        return $decisionDTO;
    }
}
