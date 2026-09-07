<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Claim Verifier Interface
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

namespace App\Features\ContentIntelligence\Contracts;

use App\Features\ContentIntelligence\DTOs\TruthAuditReportDTO;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\ContentMission;

interface ClaimVerifierInterface
{
    /**
     * Audit and verify an individual claim against linked evidence and world model relationships.
     *
     * @return array<string, mixed>
     */
    public function verifyClaim(ClaimNode $claim): array;

    /**
     * Perform a comprehensive epistemic audit across all claims for a content mission.
     */
    public function auditMission(ContentMission $mission): TruthAuditReportDTO;
}
