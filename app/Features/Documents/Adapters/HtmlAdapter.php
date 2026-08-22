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

class HtmlAdapter implements EditorAdapterInterface
{
    protected array $supportedNodeTypes = [
        'doc', 'paragraph', 'heading', 'blockquote', 'code_block',
        'bullet_list', 'ordered_list', 'list_item', 'horizontal_rule',
        'image', 'link', 'table', 'table_row', 'table_cell', 'table_header',
        'text', 'hard_break'
    ];

    protected array $supportedMarkTypes = [
        'bold', 'italic', 'strike', 'code', 'link', 'highlight'
    ];

    public function toCanonical(string|array $content): array
    {
        $html = is_array($content) ? ($content['html'] ?? '') : $content;
        $plainText = strip_tags($html);

        // Wrap in canonical document structure
        $canonical = CanonicalDocumentSchema::createDocument([
            CanonicalDocumentSchema::createNode('paragraph', [], [
                CanonicalDocumentSchema::createTextNode($plainText)
            ])
        ]);

        // Preserve original HTML in attrs for round-trip
        $canonical['attrs']['original_html'] = $html;
        $canonical['attrs']['schema_version'] = CanonicalDocumentSchema::SCHEMA_VERSION;
        $canonical['attrs']['source_editor'] = 'html';
        $canonical['attrs']['converted_at'] = now()->toISOString();

        return $canonical;
    }

    public function fromCanonical(array $canonical): string
    {
        // Return the original HTML if available, otherwise extract from canonical
        return $canonical['attrs']['original_html'] ?? $this->extractPlainText($canonical);
    }

    public function extractPlainText(string|array $editorContent): string
    {
        $html = is_array($editorContent) ? ($editorContent['html'] ?? '') : $editorContent;
        return strip_tags($html);
    }

    public function getEditorKey(): string
    {
        return 'html';
    }

    public function getDisplayName(): string
    {
        return 'HTML Editor';
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
        return new ConversionRiskAssessment(ConversionRiskAssessment::RISK_LOW);
    }

    public function sanitize(string|array $editorContent): string|array
    {
        $html = is_array($editorContent) ? ($editorContent['html'] ?? '') : $editorContent;
        // Basic sanitization - in production use a proper HTML sanitizer
        return strip_tags($html, '<p><br><strong><em><u><s><code><a><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><pre><img><table><tr><td><th>');
    }

    public function getSupportedSchemaVersion(): int
    {
        return CanonicalDocumentSchema::SCHEMA_VERSION;
    }
}