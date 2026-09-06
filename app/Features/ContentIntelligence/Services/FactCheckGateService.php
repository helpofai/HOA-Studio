<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Fact Check Gate Service
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

use App\Features\ContentIntelligence\DTOs\FactCheckGateDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;

class FactCheckGateService
{
    /**
     * Audit prose against Claim Graph and Source Intelligence to enforce strict truthfulness.
     */
    public function audit(SectionDraftDTO $draft, KnowledgeFabricDTO $knowledge): FactCheckGateDTO
    {
        $auditLog = [];
        $verifiedCount = 0;
        $unverifiedCount = 0;
        $hallucinationsDetected = 0;
        $citationAnchors = 0;

        $claimsMap = [];
        foreach ($knowledge->claims as $claim) {
            $claimsMap[$claim->claimId] = $claim;
        }

        $content = strtolower($draft->contentHtml);

        // Audit cited claims
        foreach ($draft->citedClaimIds as $claimId) {
            $claim = $claimsMap[$claimId] ?? null;

            if (! $claim) {
                // Claim does not exist in the verified knowledge fabric
                $hallucinationsDetected++;
                $auditLog[] = [
                    'claim_id' => $claimId,
                    'status' => 'hallucination_detected',
                    'message' => "Referenced claim [{$claimId}] does not exist in the verified Claim Graph.",
                ];

                continue;
            }

            if ($claim->epistemicState === EpistemicState::CONTRADICTED) {
                $hallucinationsDetected++;
                $auditLog[] = [
                    'claim_id' => $claimId,
                    'status' => 'contradiction_rejected',
                    'message' => "Referenced claim [{$claimId}] is flagged as CONTRADICTED by truth layer.",
                ];
            } elseif ($claim->epistemicState === EpistemicState::UNVERIFIED) {
                $unverifiedCount++;
                $auditLog[] = [
                    'claim_id' => $claimId,
                    'status' => 'unverified_warning',
                    'message' => "Claim [{$claimId}] lacks primary source verification.",
                ];
            } else {
                $verifiedCount++;
                $citationAnchors++;
                $auditLog[] = [
                    'claim_id' => $claimId,
                    'status' => 'verified',
                    'statement' => $claim->statement,
                    'source_url' => $claim->sourceUrl,
                    'confidence' => $claim->confidenceScore,
                ];
            }
        }

        // Count HTML citation tags
        $injectedCitations = substr_count($content, 'verified claim [') + substr_count($content, 'verified claim');
        if ($injectedCitations > $citationAnchors) {
            $citationAnchors = $injectedCitations;
        }

        $passed = ($hallucinationsDetected === 0) && ($unverifiedCount === 0);

        return new FactCheckGateDTO(
            verifiedClaimsCount: $verifiedCount,
            unverifiedClaimsCount: $unverifiedCount,
            hallucinationsDetected: $hallucinationsDetected,
            citationAnchorsInjected: $citationAnchors,
            passed: $passed,
            auditLog: $auditLog
        );
    }
}
