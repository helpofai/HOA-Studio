<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Research Plan DTO
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

namespace App\Features\ContentIntelligence\DTOs;

use App\Features\ContentIntelligence\Enums\ResearchBudgetTier;

final class ResearchPlanDTO
{
    /**
     * @param  int  $allocatedBudget  Maximum task count
     * @param  array<ResearchTaskDTO>  $tasks  Scheduled research tasks
     * @param  string  $rationale  Rationale behind selected research vectors
     * @param  float  $researchConfidence  Estimated research confidence (0.00 to 1.00)
     * @param  bool  $requiresAdditionalResearch  Whether budget or gaps warrant secondary deep dive
     * @param  string|null  $additionalResearchReason  Rationale for re-budgeting if needed
     */
    public function __construct(
        public readonly ResearchBudgetTier $budgetTier,
        public readonly int $allocatedBudget,
        public readonly array $tasks = [],
        public readonly string $rationale = '',
        public readonly float $researchConfidence = 0.90,
        public readonly bool $requiresAdditionalResearch = false,
        public readonly ?string $additionalResearchReason = null
    ) {}

    public static function fromArray(array $data): self
    {
        $tasks = [];
        foreach (($data['tasks'] ?? []) as $taskData) {
            $tasks[] = $taskData instanceof ResearchTaskDTO ? $taskData : ResearchTaskDTO::fromArray($taskData);
        }

        $tier = isset($data['budget_tier']) && $data['budget_tier'] instanceof ResearchBudgetTier
            ? $data['budget_tier']
            : ResearchBudgetTier::tryFrom($data['budget_tier'] ?? '') ?? ResearchBudgetTier::STANDARD;

        return new self(
            budgetTier: $tier,
            allocatedBudget: (int) ($data['allocated_budget'] ?? $tier->maxQueries()),
            tasks: $tasks,
            rationale: (string) ($data['rationale'] ?? 'Targeted domain evidence discovery plan'),
            researchConfidence: (float) ($data['research_confidence'] ?? 0.90),
            requiresAdditionalResearch: (bool) ($data['requires_additional_research'] ?? false),
            additionalResearchReason: $data['additional_research_reason'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'budget_tier' => $this->budgetTier->value,
            'allocated_budget' => $this->allocatedBudget,
            'tasks' => array_map(fn (ResearchTaskDTO $t) => $t->toArray(), $this->tasks),
            'rationale' => $this->rationale,
            'research_confidence' => $this->researchConfidence,
            'requires_additional_research' => $this->requiresAdditionalResearch,
            'additional_research_reason' => $this->additionalResearchReason,
        ];
    }
}
