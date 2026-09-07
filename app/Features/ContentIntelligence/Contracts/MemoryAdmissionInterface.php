<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Admission Interface
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

use App\Features\ContentIntelligence\DTOs\AdmissionResultDTO;
use App\Features\ContentIntelligence\DTOs\MemoryCandidateDTO;

interface MemoryAdmissionInterface
{
    /**
     * Evaluate a candidate memory through the multi-stage cognitive admission gate.
     */
    public function evaluate(MemoryCandidateDTO $candidate): AdmissionResultDTO;
}
