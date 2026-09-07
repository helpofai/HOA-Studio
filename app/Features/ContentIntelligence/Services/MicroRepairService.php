<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Micro Repair Service
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

use App\Features\ContentIntelligence\DTOs\MicroRepairActionDTO;
use App\Features\ContentIntelligence\Enums\ProblemCategory;
use App\Features\ContentIntelligence\Enums\RepairStatus;
use App\Features\ContentIntelligence\Enums\RepairUnitType;
use App\Features\ContentIntelligence\Models\MicroRepair;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use Illuminate\Support\Collection;

class MicroRepairService
{
    /**
     * Detect localized problems across text units.
     *
     * @return array<int, array{unit_pointer: string, text: string, category: ProblemCategory, root_cause: string, unit_type: RepairUnitType}>
     */
    public function detectProblems(string $content, array $context = []): array
    {
        $problems = [];
        $sentences = preg_split('/(?<=[.?!])\s+/', trim(strip_tags($content)), -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($sentences)) {
            $sentences = [$content];
        }

        foreach ($sentences as $idx => $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) {
                continue;
            }

            $wordCount = str_word_count($sentence);

            // 1. Run-on Sentence / High Complexity (Readability)
            if ($wordCount > 35) {
                $problems[] = [
                    'unit_pointer' => "s-{$idx}",
                    'text' => $sentence,
                    'category' => ProblemCategory::READABILITY,
                    'root_cause' => "Sentence contains {$wordCount} words, exceeding standard cognitive readability threshold (35 words).",
                    'unit_type' => RepairUnitType::SENTENCE,
                ];

                continue;
            }

            // 2. Repetitive / Cliché AI Fluff (Repetition)
            if (preg_match('/\b(in conclusion|delve into|testament to|it is crucial to remember that|in today\'s fast-paced world)\b/i', $sentence, $matches)) {
                $problems[] = [
                    'unit_pointer' => "s-{$idx}",
                    'text' => $sentence,
                    'category' => ProblemCategory::REPETITION,
                    'root_cause' => "Detected stereotypical AI filler phrase: '{$matches[0]}'.",
                    'unit_type' => RepairUnitType::SENTENCE,
                ];

                continue;
            }

            // 3. Ungrounded Absolute Claims (Weak Evidence / Factual Risk)
            if (preg_match('/\b(guaranteed 100%|everyone knows that|without any doubt whatsoever|proven beyond question)\b/i', $sentence, $matches)) {
                $problems[] = [
                    'unit_pointer' => "s-{$idx}",
                    'text' => $sentence,
                    'category' => ProblemCategory::WEAK_EVIDENCE,
                    'root_cause' => "Absolute assertion '{$matches[0]}' lacks empirical citation grounding.",
                    'unit_type' => RepairUnitType::SENTENCE,
                ];

                continue;
            }
        }

        return $problems;
    }

    /**
     * Execute a surgical localized repair on the smallest affected unit without rewriting the entire document.
     */
    public function repairSmallestUnit(
        WorkflowRun $run,
        string $fullContent,
        string $targetUnit,
        RepairUnitType $unitType,
        ProblemCategory $category,
        string $rootCause,
        ?string $unitPointer = null,
        int $iteration = 1
    ): MicroRepair {
        $repairedUnit = $this->synthesizeRepairedUnit($targetUnit, $category);
        $diffSummary = $this->generateDiffSummary($targetUnit, $repairedUnit);

        // Replace only the target unit within fullContent
        $repairedFullContent = str_replace($targetUnit, $repairedUnit, $fullContent);

        $dto = new MicroRepairActionDTO(
            workflowRunId: $run->id,
            unitType: $unitType,
            unitPointer: $unitPointer ?? 'unit-'.substr(md5($targetUnit), 0, 8),
            problemCategory: $category,
            rootCause: $rootCause,
            originalText: $targetUnit,
            repairedText: $repairedUnit,
            diffSummary: $diffSummary,
            escalationLevel: $unitType->level(),
            iteration: $iteration,
            status: RepairStatus::RESOLVED,
            diagnosticNotes: [
                'original_words' => str_word_count($targetUnit),
                'repaired_words' => str_word_count($repairedUnit),
                'delta' => str_word_count($repairedUnit) - str_word_count($targetUnit),
                'repaired_at' => now()->toIso8601String(),
            ]
        );

        return MicroRepair::create($dto->toArray());
    }

    /**
     * Escalate a repair to a larger unit container when localized micro-repair fails standard verification.
     */
    public function escalateRepair(
        MicroRepair $existingRepair,
        string $expandedUnit,
        WorkflowRun $run,
        string $expandedRootCause
    ): MicroRepair {
        $nextUnit = $existingRepair->unit_type->escalate() ?? RepairUnitType::ARTICLE;

        $existingRepair->update([
            'status' => RepairStatus::ESCALATED,
        ]);

        $repairedExpanded = $this->synthesizeRepairedUnit($expandedUnit, $existingRepair->problem_category);
        $diffSummary = $this->generateDiffSummary($expandedUnit, $repairedExpanded);

        $escalatedDTO = new MicroRepairActionDTO(
            workflowRunId: $run->id,
            unitType: $nextUnit,
            unitPointer: $existingRepair->unit_pointer.'-esc',
            problemCategory: $existingRepair->problem_category,
            rootCause: $expandedRootCause,
            originalText: $expandedUnit,
            repairedText: $repairedExpanded,
            diffSummary: $diffSummary,
            escalationLevel: $nextUnit->level(),
            iteration: $existingRepair->iteration + 1,
            status: RepairStatus::RESOLVED,
            diagnosticNotes: [
                'escalated_from_repair_id' => $existingRepair->id,
                'escalated_from_unit' => $existingRepair->unit_type->value,
                'escalated_to_unit' => $nextUnit->value,
            ]
        );

        return MicroRepair::create($escalatedDTO->toArray());
    }

    /**
     * Synthesize clean, targeted repair text according to problem category.
     */
    public function synthesizeRepairedUnit(string $originalText, ProblemCategory $category): string
    {
        return match ($category) {
            ProblemCategory::READABILITY => $this->repairReadability($originalText),
            ProblemCategory::REPETITION => $this->repairRepetition($originalText),
            ProblemCategory::WEAK_EVIDENCE, ProblemCategory::FACTUAL_ERROR => $this->repairEvidence($originalText),
            ProblemCategory::TONE_MISMATCH => $this->repairTone($originalText),
            default => trim(preg_replace('/\s+/', ' ', $originalText)),
        };
    }

    /**
     * Public helper to repair any text unit for a given category.
     */
    public function repairProseUnit(string $text, ProblemCategory|string|null $category = null): string
    {
        if (is_string($category)) {
            $category = ProblemCategory::tryFrom($category) ?? ProblemCategory::READABILITY;
        } elseif (! ($category instanceof ProblemCategory)) {
            $category = ProblemCategory::READABILITY;
        }

        return $this->synthesizeRepairedUnit($text, $category);
    }

    protected function repairReadability(string $text): string
    {
        // Split complex conjunctions into two concise sentences
        if (preg_match('/^(.*?)(,\s+(?:which|whereby|and furthermore|in order to|as a consequence of which)\s+)(.*)$/i', $text, $matches)) {
            $part1 = rtrim($matches[1], ',').'.';
            $part2 = ucfirst(trim($matches[3]));

            return "{$part1} {$part2}";
        }

        return preg_replace('/,\s*and\s*/i', '. ', $text);
    }

    protected function repairRepetition(string $text): string
    {
        $patterns = [
            '/\bIn conclusion,\s*/i' => 'Ultimately, ',
            '/\bdelve into\b/i' => 'examine',
            '/\ba testament to\b/i' => 'proof of',
            '/\bit is crucial to remember that\s*/i' => 'Notably, ',
            '/\bin today\'s fast-paced world,?\s*/i' => 'Currently, ',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }

    protected function repairEvidence(string $text): string
    {
        $patterns = [
            '/\bguaranteed 100%(?!\w)/i' => 'empirically observed in controlled benchmarks',
            '/\beveryone knows that\b/i' => 'industry consensus indicates that',
            '/\bwithout any doubt whatsoever\b/i' => 'with high measured confidence',
            '/\bproven beyond question\b/i' => 'corroborated by verified findings',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }

    protected function repairTone(string $text): string
    {
        return preg_replace('/\b(kinda|gonna|wanna|pretty cool|super awesome)\b/i', 'effective', $text);
    }

    protected function generateDiffSummary(string $original, string $repaired): string
    {
        $origWords = explode(' ', trim($original));
        $repWords = explode(' ', trim($repaired));

        $deleted = array_diff($origWords, $repWords);
        $added = array_diff($repWords, $origWords);

        $summary = '';
        if (! empty($deleted)) {
            $summary .= '- ['.implode(', ', array_slice($deleted, 0, 5)).'] ';
        }
        if (! empty($added)) {
            $summary .= '+ ['.implode(', ', array_slice($added, 0, 5)).']';
        }

        return trim($summary) ?: 'Cleaned sentence cadence and structure.';
    }

    public function getRunRepairs(WorkflowRun $run): Collection
    {
        return MicroRepair::where('workflow_run_id', $run->id)
            ->latest('id')
            ->get();
    }
}
