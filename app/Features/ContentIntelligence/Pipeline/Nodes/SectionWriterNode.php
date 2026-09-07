<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Section Writer Workflow Node
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
use App\Features\ContentIntelligence\DTOs\AdaptiveOutlineDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Memory\MemoryManager;
use App\Features\ContentIntelligence\Models\SectionDraft;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\CriticAgentService;
use App\Features\ContentIntelligence\Services\FactCheckGateService;
use App\Features\ContentIntelligence\Services\SectionDraftsmanService;

class SectionWriterNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?SectionDraftsmanService $draftsmanService = null,
        protected ?CriticAgentService $criticService = null,
        protected ?FactCheckGateService $factGateService = null
    ) {
        $this->draftsmanService = $draftsmanService ?? new SectionDraftsmanService;
        $this->criticService = $criticService ?? new CriticAgentService;
        $this->factGateService = $factGateService ?? new FactCheckGateService;
    }

    public function getName(): string
    {
        return 'section_draftsman';
    }

    public function getDescription(): string
    {
        return 'Drafts section prose with active Critic evaluation, self-correction loops, and Fact-Checking Gate validation.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $outlinePayload = $context['adaptive_outline']['adaptive_outline'] ?? null;
        $knowledgePayload = $context['knowledge_fabric']['knowledge_fabric'] ?? null;

        if (! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing mission_intake payload in workflow context.');
        }

        if (! $outlinePayload) {
            return WorkflowNodeResultDTO::failed('Missing adaptive_outline payload in workflow context.');
        }

        if (! $knowledgePayload) {
            return WorkflowNodeResultDTO::failed('Missing knowledge_fabric payload in workflow context.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $outlineDTO = AdaptiveOutlineDTO::fromArray($outlinePayload);
        $knowledgeDTO = KnowledgeFabricDTO::fromArray($knowledgePayload);

        $draftResults = [];
        $totalWords = 0;
        $criticScoresSum = 0.0;
        $allFactGatesPassed = true;

        foreach ($outlineDTO->sections as $sectionNode) {
            // 1. Initial Section Draft
            $draft = $this->draftsmanService->draft($run, $sectionNode, $missionDTO, $knowledgeDTO, [], 0);

            // 2. Critic & Reflection Evaluation
            $criticScore = $this->criticService->evaluate($draft, $sectionNode, $missionDTO, $knowledgeDTO);

            // 3. Self-Correction Loop (Up to 2 surgical revisions if score < 80 or has directives)
            $iteration = 0;
            while ((! $criticScore->passed || $criticScore->overallScore < 80.0) && $iteration < 2) {
                $iteration++;
                $draft = $this->draftsmanService->draft(
                    $run,
                    $sectionNode,
                    $missionDTO,
                    $knowledgeDTO,
                    $criticScore->revisionDirectives,
                    $iteration
                );
                $criticScore = $this->criticService->evaluate($draft, $sectionNode, $missionDTO, $knowledgeDTO);
            }

            // 4. Fact-Checking Gate Audit
            $factGateResult = $this->factGateService->audit($draft, $knowledgeDTO);
            if (! $factGateResult->passed) {
                $allFactGatesPassed = false;
            }

            // 5. Persist Final Critic & Fact Status to SectionDraft Record
            SectionDraft::where('workflow_run_id', $run->id)
                ->where('section_id', $sectionNode->sectionId)
                ->update([
                    'critic_score' => $criticScore->overallScore,
                    'critic_feedback' => [
                        'score' => $criticScore->toArray(),
                        'fact_gate' => $factGateResult->toArray(),
                    ],
                    'status' => $criticScore->passed ? 'approved' : 'flagged',
                ]);

            $draftResults[] = [
                'draft' => $draft->toArray(),
                'critic' => $criticScore->toArray(),
                'fact_gate' => $factGateResult->toArray(),
            ];

            // 6. Record Episodic Event into Cognitive Memory OS
            try {
                if (class_exists(MemoryManager::class)) {
                    app(MemoryManager::class)->recordEpisode(
                        userId: (int) $run->user_id,
                        eventType: 'section_draft_completed',
                        missionId: $run->mission_id,
                        context: [
                            'section_id' => $sectionNode->sectionId,
                            'heading' => $sectionNode->heading,
                            'critic_score' => $criticScore->overallScore,
                            'iterations' => $iteration,
                        ],
                        actionTaken: 'Executed Section Draftsman and Critic Reflection Gate',
                        outcomeScore: round($criticScore->overallScore / 100, 4),
                        lessonsLearned: $criticScore->passed ? 'Prose passed standards with zero critical flaws.' : 'Critic required surgical self-correction.'
                    );
                }
            } catch (\Throwable) {
                // Graceful fallback
            }

            $totalWords += $draft->wordCount;
            $criticScoresSum += $criticScore->overallScore;
        }

        $sectionCount = count($outlineDTO->sections);
        $avgScore = $sectionCount > 0 ? round($criticScoresSum / $sectionCount, 1) : 0.0;
        $overallConfidence = round(min(0.98, max(0.60, $avgScore / 100)), 2);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'sections' => $draftResults,
                'total_sections_drafted' => $sectionCount,
                'total_word_count' => $totalWords,
                'average_critic_score' => $avgScore,
                'fact_gates_all_passed' => $allFactGatesPassed,
                'drafted_at' => now()->toIso8601String(),
            ],
            confidence: $overallConfidence,
            nextSuggestedNode: 'seo_optimization'
        );
    }
}
