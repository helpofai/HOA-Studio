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

/**
 * Tiptap Adapter - Translates between Tiptap/ProseMirror JSON and Canonical AST
 */
class TiptapAdapter implements EditorAdapterInterface
{
    protected array $supportedNodeTypes = [
        'doc', 'paragraph', 'heading', 'blockquote', 'code_block',
        'bullet_list', 'ordered_list', 'list_item', 'horizontal_rule',
        'image', 'link', 'table', 'table_row', 'table_cell', 'table_header',
        'text', 'hard_break', 'task_list', 'task_item',
    ];

    protected array $supportedMarkTypes = [
        'bold', 'italic', 'strike', 'code', 'link', 'highlight',
    ];

    public function toCanonical(string|array $editorContent): array
    {
        // Handle both JSON string and decoded array
        $tiptapJson = is_string($editorContent) ? json_decode($editorContent, true) : $editorContent;

        if (!$tiptapJson || !isset($tiptapJson['type'])) {
            return CanonicalDocumentSchema::getEmptyDocument();
        }

        // Ensure schema version
        $canonical = $this->normalizeTiptapNode($tiptapJson);
        $canonical['attrs']['schema_version'] = CanonicalDocumentSchema::SCHEMA_VERSION;
        $canonical['attrs']['source_editor'] = 'tiptap';
        $canonical['attrs']['converted_at'] = now()->toISOString();

        // Validate
        if (!CanonicalDocumentSchema::validate($canonical)) {
            // Fallback to empty document with warning
            return CanonicalDocumentSchema::getEmptyDocument();
        }

        return $canonical;
    }

    public function fromCanonical(array $canonicalAst): string|array
    {
        // Ensure it's a valid canonical document
        if (!CanonicalDocumentSchema::validate($canonicalAst)) {
            return CanonicalDocumentSchema::getEmptyDocument();
        }

        // Convert back to Tiptap/ProseMirror format
        return $this->convertToTiptap($canonicalAst);
    }

    public function extractPlainText(string|array $editorContent): string
    {
        $tiptapJson = is_string($editorContent) ? json_decode($editorContent, true) : $editorContent;
        if (!$tiptapJson) return '';

        return $this->extractTextFromNode($tiptapJson);
    }

    public function getEditorKey(): string
    {
        return 'tiptap';
    }

    public function getDisplayName(): string
    {
        return 'Tiptap Editor';
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
        $tiptapJson = is_string($editorContent) ? json_decode($editorContent, true) : $editorContent;
        return $this->sanitizeNode($tiptapJson);
    }

    public function getSupportedSchemaVersion(): int
    {
        return CanonicalDocumentSchema::SCHEMA_VERSION;
    }

    /**
     * Normalize a Tiptap node to canonical format
     */
    protected function normalizeTiptapNode(array $node): array
    {
        $normalized = [
            'type' => $node['type'] ?? 'doc',
            'attrs' => $node['attrs'] ?? [],
        ];

        if (isset($node['marks']) && is_array($node['marks'])) {
            $normalized['marks'] = $node['marks'];
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $normalized['content'] = array_map([$this, 'normalizeTiptapNode'], $node['content']);
        } elseif (isset($node['text'])) {
            $normalized['text'] = $node['text'];
        }

        return $normalized;
    }

    /**
     * Convert canonical AST to Tiptap format
     */
    protected function convertToTiptap(array $canonical): array
    {
        $tiptap = [
            'type' => $canonical['type'] ?? 'doc',
            'attrs' => $canonical['attrs'] ?? [],
        ];

        if (isset($canonical['marks'])) {
            $tiptap['marks'] = $canonical['marks'];
        }

        if (isset($canonical['content']) && is_array($canonical['content'])) {
            $tiptap['content'] = array_map([$this, 'convertToTiptap'], $canonical['content']);
        } elseif (isset($canonical['text'])) {
            $tiptap['text'] = $canonical['text'];
        }

        return $tiptap;
    }

    /**
     * Extract plain text from a Tiptap node recursively
     */
    protected function extractTextFromNode(array $node): string
    {
        $text = $node['text'] ?? '';

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $text .= ' ' . $this->extractTextFromNode($child);
            }
        }

        return trim($text);
    }

    /**
     * Sanitize a Tiptap node - remove unsupported marks/nodes
     */
    protected function sanitizeNode(array $node): array
    {
        $sanitized = [
            'type' => $node['type'] ?? 'doc',
            'attrs' => $node['attrs'] ?? [],
        ];

        if (isset($node['marks']) && is_array($node['marks'])) {
            $sanitized['marks'] = array_filter($node['marks'], function($mark) {
                return in_array($mark['type'] ?? '', $this->supportedMarkTypes);
            });
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $sanitized['content'] = array_map([$this, 'sanitizeNode'], $node['content']);
            // Filter out unsupported node types
            $sanitized['content'] = array_filter($sanitized['content'], function($child) {
                return in_array($child['type'] ?? '', $this->supportedNodeTypes);
            });
        } elseif (isset($node['text'])) {
            $sanitized['text'] = $node['text'];
        }

        return $sanitized;
    }
}