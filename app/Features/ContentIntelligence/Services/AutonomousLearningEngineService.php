<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Autonomous Learning Engine Service
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

use App\Features\ContentIntelligence\Enums\StrategyCategory;
use App\Features\ContentIntelligence\Enums\StrategyStatus;
use App\Features\ContentIntelligence\Models\QualityHealthAudit;
use App\Features\ContentIntelligence\Models\StrategyMemory;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use Illuminate\Support\Collection;

class AutonomousLearningEngineService
{
    /**
     * Record a new strategic observation or increment evidence if it already exists.
     * Follows brain.md Section 27: Observation -> Candidate -> Validated -> Adopted.
     *
     * @param  array<string, mixed>  $payload
     */
    public function recordObservation(
        int $userId,
        string $strategyKey,
        StrategyCategory|string $category,
        array $payload,
        float $initialConfidence = 0.40
    ): StrategyMemory {
        $categoryEnum = is_string($category)
            ? StrategyCategory::tryFrom($category) ?? StrategyCategory::STRUCTURE
            : $category;

        $existing = StrategyMemory::where('user_id', $userId)
            ->where('strategy_key', $strategyKey)
            ->first();

        if ($existing) {
            return $this->incrementEvidence($userId, $strategyKey, $payload);
        }

        return StrategyMemory::create([
            'user_id' => $userId,
            'strategy_key' => $strategyKey,
            'category' => $categoryEnum,
            'evidence_count' => 1,
            'confidence' => min(1.0, max(0.1, $initialConfidence)),
            'status' => StrategyStatus::OBSERVATION,
            'learning_payload' => $payload,
            'adopted_at' => null,
        ]);
    }

    /**
     * Increment evidence count and update confidence score.
     *
     * @param  array<string, mixed>  $additionalEvidence
     */
    public function incrementEvidence(int $userId, string $strategyKey, array $additionalEvidence = []): StrategyMemory
    {
        $strategy = StrategyMemory::where('user_id', $userId)
            ->where('strategy_key', $strategyKey)
            ->firstOrFail();

        $newCount = $strategy->evidence_count + 1;
        $newConfidence = min(0.98, $strategy->confidence + 0.12);

        $mergedPayload = array_merge($strategy->learning_payload ?? [], [
            'last_evidence' => $additionalEvidence,
            'total_observations' => $newCount,
        ]);

        $strategy->update([
            'evidence_count' => $newCount,
            'confidence' => $newConfidence,
            'learning_payload' => $mergedPayload,
        ]);

        return $this->evaluateProgression($strategy);
    }

    /**
     * Evaluate whether the strategy qualifies to advance its lifecycle status.
     */
    public function evaluateProgression(StrategyMemory $strategy): StrategyMemory
    {
        $count = $strategy->evidence_count;
        $confidence = $strategy->confidence;

        // Skip if already rejected or adopted
        if (in_array($strategy->status, [StrategyStatus::ADOPTED, StrategyStatus::REJECTED], true)) {
            return $strategy;
        }

        if ($count >= 6 && $confidence >= 0.88) {
            $strategy->update([
                'status' => StrategyStatus::ADOPTED,
                'adopted_at' => now(),
            ]);
        } elseif ($count >= 4 && $confidence >= 0.75) {
            $strategy->update(['status' => StrategyStatus::VALIDATED]);
        } elseif ($count >= 2) {
            $strategy->update(['status' => StrategyStatus::CANDIDATE]);
        }

        return $strategy->fresh();
    }

    /**
     * Manually or programmatically adopt a strategy candidate.
     */
    public function adoptStrategy(int $strategyId): StrategyMemory
    {
        $strategy = StrategyMemory::findOrFail($strategyId);
        $strategy->update([
            'status' => StrategyStatus::ADOPTED,
            'confidence' => max($strategy->confidence, 0.95),
            'adopted_at' => now(),
        ]);

        return $strategy;
    }

    /**
     * Reject a candidate strategy that caused regressions or was disapproved by human.
     */
    public function rejectStrategy(int $strategyId, string $reason): StrategyMemory
    {
        $strategy = StrategyMemory::findOrFail($strategyId);
        $payload = array_merge($strategy->learning_payload ?? [], [
            'rejection_reason' => $reason,
            'rejected_at' => now()->toIso8601String(),
        ]);

        $strategy->update([
            'status' => StrategyStatus::REJECTED,
            'learning_payload' => $payload,
        ]);

        return $strategy;
    }

    /**
     * Retrieve all adopted strategies to inject into upcoming generation prompts and blueprints.
     *
     * @return Collection<int, StrategyMemory>
     */
    public function getActiveAdoptedStrategies(int $userId, ?StrategyCategory $category = null): Collection
    {
        return StrategyMemory::where('user_id', $userId)
            ->where('status', StrategyStatus::ADOPTED)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('confidence')
            ->get();
    }

    /**
     * Analyze a completed workflow run and extract learned strategies.
     *
     * @return Collection<int, StrategyMemory>
     */
    public function harvestWorkflowRunLessons(WorkflowRun $workflowRun): Collection
    {
        $userId = $workflowRun->user_id;
        $mission = $workflowRun->mission;
        $latestAudit = QualityHealthAudit::where('workflow_run_id', $workflowRun->id)->latest()->first();

        $learned = collect();

        $overallScore = $latestAudit?->overall_score ?? $latestAudit?->overall_health_score ?? 0;
        $dimensions = $latestAudit?->dimensions ?? $latestAudit?->dimension_scores ?? [];
        $evidenceDensity = $dimensions['evidence_density'] ?? 0;

        // 1. Structure Strategy: If article passed with high readability and depth
        if ($latestAudit && $overallScore >= 85) {
            $structureKey = 'high_depth_outline_strategy';
            $mem = $this->recordObservation($userId, $structureKey, StrategyCategory::STRUCTURE, [
                'rule' => 'Generate minimum 5 detailed H2 sections with technical FAQs for authority queries',
                'proven_health_score' => $overallScore,
                'target_topic' => $mission?->topic,
            ], 0.60);
            $learned->push($mem);
        }

        // 2. Sources Strategy: If primary citations were present
        if ($latestAudit && $evidenceDensity >= 80) {
            $sourcesKey = 'primary_source_benchmarks_preferred';
            $mem = $this->recordObservation($userId, $sourcesKey, StrategyCategory::SOURCES, [
                'rule' => 'Always anchor quantitative assertions with verified benchmark tables',
                'evidence_density_score' => $evidenceDensity,
            ], 0.55);
            $learned->push($mem);
        }

        // 3. SEO Strategy: Search intent alignment
        if ($mission && ! empty($mission->audience)) {
            $seoKey = 'audience_tailored_lexicon_'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', substr($mission->audience, 0, 30)));
            $mem = $this->recordObservation($userId, $seoKey, StrategyCategory::SEO, [
                'rule' => "Use specialized terminology suited for {$mission->audience}",
                'audience' => $mission->audience,
            ], 0.50);
            $learned->push($mem);
        }

        return $learned;
    }
}
