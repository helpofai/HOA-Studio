<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Evidence Validator
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

namespace App\Features\ContentIntelligence\Memory\Admission;

use App\Features\ContentIntelligence\DTOs\MemoryCandidateDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Models\SourceIntelligence;

class EvidenceValidator
{
    /**
     * Validate the evidence backing a memory candidate.
     *
     * @return array{isValid: bool, confidence: float, epistemicState: EpistemicState, notes: array<string, mixed>}
     */
    public function validate(MemoryCandidateDTO $candidate): array
    {
        $confidence = $candidate->confidence;
        $notes = [];
        $epistemicState = EpistemicState::VERIFIED;

        // Check if backed by registered source intelligence
        if ($candidate->sourceId) {
            $source = SourceIntelligence::find($candidate->sourceId);
            if ($source) {
                $notes['source_title'] = $source->title;
                $notes['domain_authority'] = $source->domain_authority;
                $notes['is_primary'] = $source->is_primary;

                if ($source->is_primary) {
                    $confidence = max($confidence, 0.96);
                    $epistemicState = EpistemicState::VERIFIED;
                } elseif ($source->reliability_score >= 80) {
                    $confidence = max($confidence, 0.88);
                    $epistemicState = EpistemicState::VERIFIED;
                } else {
                    $confidence = min($confidence, 0.79);
                    $epistemicState = EpistemicState::PARTIALLY_VERIFIED;
                }
            }
        } elseif (! empty($candidate->sourceUrl)) {
            // Evaluated by URL presence
            $url = (string) $candidate->sourceUrl;
            $notes['raw_url'] = $url;

            // Check authoritative official domains
            if (preg_match('/\.(gov|edu|org)(\/|$)/i', $url) || str_contains($url, 'github.com') || str_contains($url, 'docs.')) {
                $confidence = max($confidence, 0.90);
                $epistemicState = EpistemicState::VERIFIED;
            } else {
                $confidence = max($confidence, 0.82);
                $epistemicState = EpistemicState::PARTIALLY_VERIFIED;
            }
        } elseif ($candidate->layer->isLongTerm() && empty($candidate->provenance)) {
            // Long-term facts without provenance receive a confidence penalty
            $confidence = min($confidence, 0.65);
            $epistemicState = EpistemicState::UNVERIFIED;
            $notes['warning'] = 'No source URL or provenance provided for long-term knowledge fact.';
        }

        $isValid = $confidence >= 0.80;

        return [
            'isValid' => $isValid,
            'confidence' => round($confidence, 4),
            'epistemicState' => $epistemicState,
            'notes' => $notes,
        ];
    }
}
