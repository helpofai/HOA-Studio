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
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\Table\Node\Table;
use League\CommonMark\Extension\Table\Node\TableRow;
use League\CommonMark\Extension\Table\Node\TableCell;
use League\CommonMark\Node\Node;

/**
 * Markdown Adapter - Translates between Markdown and Canonical AST
 *
 * Uses league/commonmark for parsing and rendering.
 * Note: Markdown is a flat format - conversion to Canonical AST
 * involves some structural inference.
 */
class MarkdownAdapter implements EditorAdapterInterface
{
    protected GithubFlavoredMarkdownConverter $converter;
    protected Environment $environment;

    // Markdown supports a subset of Canonical nodes
    protected array $supportedNodeTypes = [
        'doc', 'paragraph', 'heading', 'blockquote', 'code_block',
        'horizontal_rule', 'bullet_list', 'ordered_list', 'list_item',
        'image', 'link', 'table', 'table_row', 'table_cell', 'table_header',
        'text', 'hard_break', 'task_list', 'task_item'
    ];

    protected array $supportedMarkTypes = [
        'bold', 'italic', 'strike', 'code', 'link', 'highlight'
    ];

    public function __construct()
    {
        $this->environment = Environment::createCommonMarkEnvironment();
        // Add table extension
        $this->environment->addExtension(new \League\CommonMark\Extension\Table\TableExtension());
        // Add task list extension
        $this->environment->addExtension(new \League\CommonMark\Extension\TaskList\TaskListExtension());

        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ], $this->environment);
    }

    public function toCanonical(string|array $editorContent): array
    {
        $markdown = is_string($editorContent) ? $editorContent : '';

        if (trim($markdown) === '') {
            return CanonicalDocumentSchema::getEmptyDocument();
        }

        // Parse Markdown to AST using league/commonmark
        $document = $this->converter->convert($markdown)->getDocument();

        // Convert league/commonmark AST to our Canonical AST
        $canonicalContent = $this->convertMarkdownAst($document);

        $canonical = [
            'type' => 'doc',
            'attrs' => [
                'schema_version' => CanonicalDocumentSchema::SCHEMA_VERSION,
                'source_editor' => 'markdown',
                'converted_at' => now()->toISOString(),
            ],
            'content' => $canonicalContent,
        ];

        if (!CanonicalDocumentSchema::validate($canonical)) {
            return CanonicalDocumentSchema::getEmptyDocument();
        }

        return $canonical;
    }

    public function fromCanonical(array $canonicalAst): string|array
    {
        if (!CanonicalDocumentSchema::validate($canonicalAst)) {
            return '';
        }

        $markdownParts = [];
        $content = $canonicalAst['content'] ?? [];

        foreach ($content as $node) {
            $markdownParts[] = $this->nodeToMarkdown($node);
        }

        return implode("\n\n", $markdownParts);
    }

    public function extractPlainText(string|array $editorContent): string
    {
        $markdown = is_string($editorContent) ? $editorContent : '';

        if (trim($markdown) === '') {
            return '';
        }

        $document = $this->converter->convert($markdown)->getDocument();
        return $this->extractTextFromDocument($document);
    }

    public function getEditorKey(): string
    {
        return 'markdown';
    }

    public function getDisplayName(): string
    {
        return 'Markdown Editor';
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
        $riskLevel = ConversionRiskAssessment::RISK_LOW;
        $warnings = [];
        $unsupportedNodes = [];

        $allNodes = $this->collectAllNodes($canonicalAst);

        foreach ($allNodes as $node) {
            $type = $node['type'] ?? '';
            if ($type && !in_array($type, $this->supportedNodeTypes)) {
                $unsupportedNodes[] = $type;
                $riskLevel = ConversionRiskAssessment::RISK_MEDIUM;
                $warnings[] = "Unsupported node type: $type";
            }
        }

        return new ConversionRiskAssessment(
            riskLevel: $riskLevel,
            warnings: $warnings,
            unsupportedNodes: array_unique($unsupportedNodes)
        );
    }

    public function sanitize(string|array $editorContent): string|array
    {
        // For markdown, we just return the content as-is (it's already safe text)
        return is_string($editorContent) ? $editorContent : '';
    }

    public function getSupportedSchemaVersion(): int
    {
        return CanonicalDocumentSchema::SCHEMA_VERSION;
    }

    /**
     * Convert league/commonmark AST to Canonical AST.
     */
    protected function convertMarkdownAst(Node $document): array
    {
        $nodes = [];

        foreach ($document->children() as $node) {
            $canonicalNode = $this->convertMarkdownNode($node);
            if ($canonicalNode) {
                $nodes[] = $canonicalNode;
            }
        }

        return $nodes;
    }

    /**
     * Convert a single markdown node.
     */
    protected function convertMarkdownNode(Node $node): ?array
    {
        $type = $this->getNodeType($node);

        if (!$type) {
            // If it's a text node that wasn't mapped, it might be a raw text node
            if ($node instanceof Text) {
                return ['type' => 'text', 'text' => $node->getContent()];
            }
            return null;
        }

        $canonical = [
            'type' => $type,
            'attrs' => $this->extractAttributes($node),
        ];

        // Collect children first (iterator can only be consumed once)
        $children = iterator_to_array($node->children());
        $childNodes = [];
        foreach ($children as $child) {
            $childNode = $this->convertMarkdownNode($child);
            if ($childNode) {
                $childNodes[] = $childNode;
            }
        }

        if (!empty($childNodes)) {
            $canonical['content'] = $childNodes;
        }

        // Handle inline content for text nodes
        if ($node instanceof Text) {
            $canonical['text'] = $node->getLiteral();
        }

        // For heading nodes, extract text from inline children if no structured children were found
        if ($node instanceof Heading) {
            // Heading may have inline text content in its children
            $textContents = [];
            foreach ($children as $child) {
                if ($child instanceof Text) {
                    $textContents[] = ['type' => 'text', 'text' => $child->getLiteral()];
                }
            }
            if (!empty($textContents)) {
                $canonical['content'] = $textContents;
            }
        }

        return $canonical;
    }

    /**
     * Map markdown node to canonical type.
     */
    protected function getNodeType(Node $node): ?string
    {
        $class = get_class($node);

        $map = [
            Heading::class => 'heading',
            Paragraph::class => 'paragraph',
            \League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote::class => 'blockquote',
            \League\CommonMark\Extension\CommonMark\Node\Block\FencedCode::class => 'code_block',
            \League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode::class => 'code_block',
            \League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak::class => 'horizontal_rule',
            ListBlock::class => 'bullet_list',
            ListItem::class => 'list_item',
            Image::class => 'image',
            Link::class => 'link',
            Table::class => 'table',
            TableRow::class => 'table_row',
            TableCell::class => 'table_cell',
            Text::class => 'text',
            \League\CommonMark\Extension\CommonMark\Node\Inline\SoftBreak::class => 'hard_break',
            \League\CommonMark\Extension\CommonMark\Node\Inline\HardBreak::class => 'hard_break',
        ];

        // Check for ordered list
        if ($node instanceof ListBlock && $node->getListData()->type === ListBlock::TYPE_ORDERED) {
            return 'ordered_list';
        }

        return $map[$class] ?? null;
    }

    /**
     * Extract attributes from markdown node.
     */
    protected function extractAttributes(Node $node): array
    {
        $attrs = [];

        if ($node instanceof Heading) {
            $attrs['level'] = $node->getLevel();
        }

        if ($node instanceof ListBlock) {
            $attrs['ordered'] = $node->getListData()->type === ListBlock::TYPE_ORDERED;
            $attrs['start'] = $node->getListData()->start ?? 1;
        }

        if ($node instanceof Image) {
            $attrs['src'] = $node->getUrl();
            $attrs['alt'] = $node->getAlt() ?? '';
            $attrs['title'] = $node->getTitle() ?? '';
        }

        if ($node instanceof Link) {
            $attrs['href'] = $node->getUrl();
            $attrs['title'] = $node->getTitle() ?? '';
        }

        if ($node instanceof TableCell) {
            $attrs['align'] = $node->getAlignment()?->value ?? 'left';
            $attrs['header'] = $node->isHeader();
        }

        return $attrs;
    }

    /**
     * Convert Canonical node to Markdown string.
     */
    protected function nodeToMarkdown(array $node, int $depth = 0): string
    {
        $type = $node['type'] ?? 'paragraph';
        $attrs = $node['attrs'] ?? [];
        $content = $node['content'] ?? [];
        $marks = $node['marks'] ?? [];
        $text = $node['text'] ?? '';

        $indent = str_repeat('  ', $depth);

        return match ($type) {
            'heading' => $indent . str_repeat('#', $attrs['level'] ?? 2) . ' ' . $this->renderInlineContent($content, $marks, $text),
            'paragraph' => $indent . $this->renderInlineContent($content, $marks, $text),
            'blockquote' => $indent . '> ' . $this->renderInlineContent($content, $marks, $text),
            'code_block' => $indent . "```\n" . $indent . $text . "\n" . $indent . "```",
            'horizontal_rule' => $indent . '---',
            'bullet_list' => $this->renderList($content, false, $depth),
            'ordered_list' => $this->renderList($content, true, $depth),
            'list_item' => $indent . '- ' . $this->renderInlineContent($content, $marks, $text),
            'image' => $indent . '![' . ($attrs['alt'] ?? '') . '](' . ($attrs['src'] ?? '') . ')',
            'link' => $indent . '[' . $this->renderInlineContent($content, $marks, $text) . '](' . ($attrs['href'] ?? '') . ')',
            'table' => $this->renderTable($content),
            'table_row' => $this->renderTableRow($content),
            'table_cell' => $this->renderInlineContent($content, $marks, $text),
            'table_header' => '**' . $this->renderInlineContent($content, $marks, $text) . '**',
            'text' => $this->applyMarks($text, $marks),
            'hard_break' => "\n",
            'task_list' => $this->renderList($content, false, $depth),
            'task_item' => $indent . '- [ ] ' . $this->renderInlineContent($content, $marks, $text),
            default => $indent . $text,
        };
    }

    /**
     * Render inline content with marks.
     */
    protected function renderInlineContent(array $content, array $marks, string $text): string
    {
        if (!empty($content)) {
            return implode('', array_map(function($child) {
                return $this->nodeToMarkdown($child);
            }, $content));
        }
        return $this->applyMarks($text, $marks);
    }

    /**
     * Apply marks to text.
     */
    protected function applyMarks(string $text, array $marks): string
    {
        foreach ($marks as $mark) {
            switch ($mark['type'] ?? '') {
                case 'bold':
                    $text = '**' . $text . '**';
                    break;
                case 'italic':
                    $text = '*' . $text . '*';
                    break;
                case 'strike':
                    $text = '~~' . $text . '~~';
                    break;
                case 'code':
                    $text = '`' . $text . '`';
                    break;
                case 'link':
                    $text = '[' . $text . '](' . ($mark['attrs']['href'] ?? '') . ')';
                    break;
            }
        }
        return $text;
    }

    /**
     * Render list.
     */
    protected function renderList(array $items, bool $ordered, int $depth): string
    {
        $lines = [];
        $counter = $ordered ? 1 : 0;

        foreach ($items as $item) {
            $prefix = $ordered ? $counter++ . '. ' : '- ';
            $lines[] = str_repeat('  ', $depth) . $prefix . $this->renderInlineContent($item['content'] ?? [], $item['marks'] ?? [], $item['text'] ?? '');
        }

        return implode("\n", $lines);
    }

    /**
     * Render table.
     */
    protected function renderTable(array $rows): string
    {
        $lines = [];

        foreach ($rows as $i => $row) {
            $cells = [];
            foreach ($row['content'] ?? [] as $cell) {
                $cells[] = $this->nodeToMarkdown($cell);
            }
            $lines[] = '| ' . implode(' | ', $cells) . ' |';

            // Add header separator after first row
            if ($i === 0) {
                $lines[] = '| ' . implode(' | ', array_fill(0, count($cells), '---')) . ' |';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Render table row.
     */
    protected function renderTableRow(array $cells): string
    {
        return '| ' . implode(' | ', array_map(function($cell) {
            return $this->nodeToMarkdown($cell);
        }, $cells)) . ' |';
    }

    /**
     * Extract text from document recursively.
     */
    protected function extractTextFromDocument(Node $document): string
    {
        $texts = [];

        $walker = $document->walker();
        while ($event = $walker->next()) {
            if ($event->isEntering() && $event->getNode() instanceof Text) {
                $texts[] = $event->getNode()->getContent();
            }
        }

        return implode(' ', $texts);
    }

    /**
     * Collect all nodes from canonical AST for risk assessment.
     */
    protected function collectAllNodes(array $node, array &$collected = []): array
    {
        $collected[] = $node;

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $this->collectAllNodes($child, $collected);
            }
        }

        return $collected;
    }
}