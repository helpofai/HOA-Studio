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

namespace App\Features\Documents\Adapters;

use App\Features\Documents\Contracts\EditorAdapterInterface;
use App\Features\Documents\Data\CanonicalDocumentSchema;
use App\Features\Documents\Data\ConversionRiskAssessment;

class PlainTextAdapter implements EditorAdapterInterface
{
    protected array $supportedNodeTypes = ['doc', 'paragraph', 'text'];
    protected array $supportedMarkTypes = [];

    public function toCanonical(string|array $content): array
    {
        $text = is_array($content) ? ($content['text'] ?? '') : $content;
        $sanitized = strip_tags($text);

        return CanonicalDocumentSchema::createDocument([
            CanonicalDocumentSchema::createNode('paragraph', [], [
                CanonicalDocumentSchema::createTextNode($sanitized)
            ])
        ]);
    }

    public function fromCanonical(array $canonical): string
    {
        return $this->extractPlainText($canonical);
    }

    public function extractPlainText(string|array $editorContent): string
    {
        if (is_array($editorContent)) {
            $text = $editorContent['text'] ?? '';
            if (isset($editorContent['content']) && is_array($editorContent['content'])) {
                foreach ($editorContent['content'] as $child) {
                    $text .= ' ' . $this->extractPlainText($child);
                }
            }
            return trim($text);
        }
        return strip_tags($editorContent);
    }

    public function getEditorKey(): string
    {
        return 'plain_text';
    }

    public function getDisplayName(): string
    {
        return 'Plain Text';
    }

    public function getSupportedNodeTypes(): array
    {
        return $this->supportedNodeTypes;
    }

    public function getSupportedMarkTypes(): array
    {
        return $this->supportedMarkTypes;
    }

    public function assessConversionRisk(array $canonicalAst): ConversionRiskAssessment
    {
        return new ConversionRiskAssessment(ConversionRiskAssessment::LOW, []);
    }

    public function sanitize(string|array $editorContent): string|array
    {
        return is_array($editorContent) ? $editorContent : strip_tags($editorContent);
    }

    public function getSupportedSchemaVersion(): int
    {
        return CanonicalDocumentSchema::SCHEMA_VERSION;
    }
}
