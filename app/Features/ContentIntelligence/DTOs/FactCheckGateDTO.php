<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Fact Check Gate DTO
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

final class FactCheckGateDTO
{
    /**
     * @param  array<string, mixed>  $auditLog
     */
    public function __construct(
        public readonly int $verifiedClaimsCount,
        public readonly int $unverifiedClaimsCount = 0,
        public readonly int $hallucinationsDetected = 0,
        public readonly int $citationAnchorsInjected = 0,
        public readonly bool $passed = true,
        public readonly array $auditLog = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            verifiedClaimsCount: (int) ($data['verified_claims_count'] ?? 0),
            unverifiedClaimsCount: (int) ($data['unverified_claims_count'] ?? 0),
            hallucinationsDetected: (int) ($data['hallucinations_detected'] ?? 0),
            citationAnchorsInjected: (int) ($data['citation_anchors_injected'] ?? 0),
            passed: (bool) ($data['passed'] ?? true),
            auditLog: (array) ($data['audit_log'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'verified_claims_count' => $this->verifiedClaimsCount,
            'unverified_claims_count' => $this->unverifiedClaimsCount,
            'hallucinations_detected' => $this->hallucinationsDetected,
            'citation_anchors_injected' => $this->citationAnchorsInjected,
            'passed' => $this->passed,
            'audit_log' => $this->auditLog,
        ];
    }
}
