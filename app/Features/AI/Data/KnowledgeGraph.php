<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - KnowledgeGraph DTO
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

namespace App\Features\AI\Data;

/**
 * Stage 10 - 14: Source -> Evidence -> Claim Knowledge Lineage Graph DTO
 * Ensures zero unverified claims enter the drafting engine.
 */
class KnowledgeGraph
{
    /** @var array<int, array> */
    public array $sources = [];

    /** @var array<string, array> */
    public array $evidence = [];

    /** @var array<string, array> */
    public array $claims = [];

    public function __construct(array $data = [])
    {
        $this->sources = $data['sources'] ?? [];
        $this->evidence = $data['evidence'] ?? [];
        $this->claims = $data['claims'] ?? [];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function addSource(string $id, string $urlOrTitle, float $trustScore = 0.9, string $tier = 'Tier 1'): void
    {
        $this->sources[$id] = [
            'id' => $id,
            'url_or_title' => $urlOrTitle,
            'trust_score' => $trustScore,
            'tier' => $tier,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function addEvidence(string $evidenceId, string $sourceId, string $claimText, float $confidence = 0.9, string $evidenceType = 'technical_fact'): void
    {
        $this->evidence[$evidenceId] = [
            'id' => $evidenceId,
            'source_id' => $sourceId,
            'claim_text' => $claimText,
            'confidence' => $confidence,
            'evidence_type' => $evidenceType,
            'assigned_section_ids' => [],
        ];
    }

    public function addApprovedClaim(string $claimId, string $evidenceId, string $statement): void
    {
        $this->claims[$claimId] = [
            'id' => $claimId,
            'evidence_id' => $evidenceId,
            'statement' => $statement,
            'verification_status' => 'approved',
        ];
    }

    public function getApprovedClaimsForSection(array $sectionEvidenceIds): array
    {
        $approved = [];
        foreach ($this->claims as $claim) {
            $evidenceId = $claim['evidence_id'] ?? null;
            if ($evidenceId && in_array($evidenceId, $sectionEvidenceIds, true) && ($claim['verification_status'] === 'approved')) {
                $approved[] = $claim;
            }
        }
        return $approved;
    }

    public function toArray(): array
    {
        return [
            'sources' => $this->sources,
            'evidence' => $this->evidence,
            'claims' => $this->claims,
        ];
    }
}
