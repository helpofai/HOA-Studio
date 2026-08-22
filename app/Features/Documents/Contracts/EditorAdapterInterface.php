<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Documents\Contracts;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Data\ConversionRiskAssessment;

interface EditorAdapterInterface
{
    /**
     * Convert from this editor's format into the canonical database format.
     */
    public function toCanonical(string|array $content): array;

    /**
     * Convert from canonical format into this editor's expected format.
     */
    public function fromCanonical(array $canonical): string|array;

    public function extractPlainText(string|array $editorContent): string;

    public function getEditorKey(): string;

    public function getDisplayName(): string;

    public function getSupportedNodeTypes(): array;

    public function getSupportedMarkTypes(): array;

    public function assessConversionRisk(array $canonicalAst): ConversionRiskAssessment;

    public function sanitize(string|array $editorContent): string|array;

    public function getSupportedSchemaVersion(): int;
}
