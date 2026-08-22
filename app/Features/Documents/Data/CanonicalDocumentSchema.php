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

namespace App\Features\Documents\Data;

/**
 * Canonical Document Schema - Universal Content AST
 * 
 * This represents the single source of truth for document content across
 * all editor engines (Tiptap, Gutenberg, Notion, Markdown, HTML, PlainText).
 * 
 * The schema follows a ProseMirror-inspired structure but is editor-agnostic.
 * All editor adapters MUST convert to/from this canonical format.
 * 
 * Version: 1.0
 * Schema Evolution: Use 'schema_version' field for migrations.
 */
class CanonicalDocumentSchema
{
    public const SCHEMA_VERSION = 1;
    
    // Node Types - Universal block/inline node taxonomy
    public const NODE_TYPES = [
        // Document structure
        'doc'           => 'Root document node',
        'paragraph'     => 'Text paragraph',
        'heading'       => 'Heading (level 1-6)',
        'blockquote'    => 'Block quote',
        'code_block'    => 'Fenced code block',
        'horizontal_rule' => 'Horizontal rule (hr)',
        
        // Lists
        'bullet_list'   => 'Unordered list',
        'ordered_list'  => 'Ordered list',
        'list_item'     => 'List item',
        'task_list'     => 'Task list (checkbox items)',
        'task_item'     => 'Task list item',
        
        // Media & Embeds
        'image'         => 'Image with metadata',
        'video'         => 'Video embed',
        'audio'         => 'Audio embed',
        'embed'         => 'Generic embed (iframe, oEmbed)',
        'link'          => 'Hyperlink (inline)',
        
        // Tables
        'table'         => 'Table container',
        'table_row'     => 'Table row',
        'table_cell'    => 'Table cell',
        'table_header'  => 'Table header cell',
        
        // Text formatting (inline marks)
        'text'          => 'Plain text node',
        'hard_break'    => 'Hard line break',
        
        // Custom/Extension nodes
        'callout'       => 'Callout/alert box',
        'divider'       => 'Visual divider',
        'toc'           => 'Table of contents placeholder',
        'mention'       => '@mention',
        'placeholder'   => 'Placeholder text',
    ];

    // Marks (Inline formatting) - Applied to text nodes
    public const MARK_TYPES = [
        'bold'          => 'Strong emphasis',
        'italic'        => 'Emphasis',
        'strike'        => 'Strikethrough',
        'underline'     => 'Underline',
        'code'          => 'Inline code',
        'link'          => 'Hyperlink mark',
        'highlight'     => 'Highlight/background color',
        'subscript'     => 'Subscript',
        'superscript'   => 'Superscript',
        'font_size'     => 'Custom font size',
        'text_color'    => 'Text color',
        'font_family'   => 'Font family',
    ];

    // Heading levels
    public const HEADING_LEVELS = [1, 2, 3, 4, 5, 6];

    // List types
    public const LIST_TYPES = ['bullet', 'ordered', 'task'];

    /**
     * Get the default empty document structure.
     * 
     * @return array
     */
    public static function getEmptyDocument(): array
    {
        return [
            'type' => 'doc',
            'attrs' => [
                'schema_version' => self::SCHEMA_VERSION,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => '',
                            'marks' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Validate a canonical document structure.
     * 
     * @param array $document
     * @return bool
     */
    public static function validate(array $document): bool
    {
        if (!isset($document['type']) || $document['type'] !== 'doc') {
            return false;
        }

        if (!isset($document['attrs']['schema_version'])) {
            return false;
        }

        if (!isset($document['content']) || !is_array($document['content'])) {
            return false;
        }

        return self::validateNodes($document['content']);
    }

    /**
     * Recursively validate nodes.
     */
    protected static function validateNodes(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if (!isset($node['type']) || !isset(self::NODE_TYPES[$node['type']])) {
                // Allow unknown types for forward compatibility but log warning
                continue;
            }

            if (isset($node['content']) && is_array($node['content'])) {
                if (!self::validateNodes($node['content'])) {
                    return false;
                }
            }

            if (isset($node['marks']) && is_array($node['marks'])) {
                foreach ($node['marks'] as $mark) {
                    if (!isset($mark['type']) || !isset(self::MARK_TYPES[$mark['type']])) {
                        // Unknown mark - allow but could warn
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get schema metadata for editor registration.
     * 
     * @return array
     */
    public static function getSchemaMetadata(): array
    {
        return [
            'version' => self::SCHEMA_VERSION,
            'node_types' => array_keys(self::NODE_TYPES),
            'mark_types' => array_keys(self::MARK_TYPES),
            'heading_levels' => self::HEADING_LEVELS,
            'list_types' => self::LIST_TYPES,
        ];
    }

    /**
     * Create a document container.
     */
    public static function createDocument(array $content = []): array
    {
        return [
            'type' => 'doc',
            'attrs' => [
                'schema_version' => self::SCHEMA_VERSION,
                'source_editor' => 'system',
                'converted_at' => now()->toISOString(),
            ],
            'content' => $content,
        ];
    }

    /**
     * Create a node of a specific type with attributes.
     * 
     * @param string $type
     * @param array $attrs
     * @param array|null $content
     * @param array|null $marks
     * @return array
     */
    public static function createNode(
        string $type,
        array $attrs = [],
        ?array $content = null,
        ?array $marks = null
    ): array {
        $node = [
            'type' => $type,
            'attrs' => $attrs,
        ];

        if ($content !== null) {
            $node['content'] = $content;
        }

        if ($marks !== null) {
            $node['marks'] = $marks;
        }

        return $node;
    }

    /**
     * Create a text node with marks.
     * 
     * @param string $text
     * @param array $marks
     * @return array
     */
    public static function createTextNode(string $text, array $marks = []): array
    {
        $node = [
            'type' => 'text',
            'text' => $text,
        ];

        if (!empty($marks)) {
            $node['marks'] = $marks;
        }

        return $node;
    }

    /**
     * Create a mark.
     * 
     * @param string $type
     * @param array $attrs
     * @return array
     */
    public static function createMark(string $type, array $attrs = []): array
    {
        return [
            'type' => $type,
            'attrs' => $attrs,
        ];
    }

    /**
     * Extract plain text from canonical document.
     * 
     * @param array $document
     * @return string
     */
    public static function extractPlainText(array $document): string
    {
        $text = '';
        
        if (isset($document['content'])) {
            $text = self::extractTextFromNodes($document['content']);
        }

        return trim($text);
    }

    protected static function extractTextFromNodes(array $nodes): string
    {
        $text = '';
        
        foreach ($nodes as $node) {
            if ($node['type'] === 'text' && isset($node['text'])) {
                $text .= $node['text'];
            } elseif ($node['type'] === 'hard_break') {
                $text .= "\n";
            } elseif (isset($node['content'])) {
                $text .= self::extractTextFromNodes($node['content']);
            }
        }

        return $text;
    }

    /**
     * Calculate approximate word count.
     */
    public static function calculateWordCount(array $document): int
    {
        $text = self::extractPlainText($document);
        return str_word_count($text);
    }

    /**
     * Calculate character count.
     */
    public static function calculateCharacterCount(array $document): int
    {
        return strlen(self::extractPlainText($document));
    }

    /**
     * Estimate reading time in minutes (average 200 wpm).
     */
    public static function estimateReadingTime(array $document): int
    {
        $words = self::calculateWordCount($document);
        return max(1, (int)ceil($words / 200));
    }
}