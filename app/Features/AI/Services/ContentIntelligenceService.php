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

namespace App\Features\AI\Services;

use App\Features\Documents\Data\CanonicalDocumentSchema;
use App\Features\SEO\Services\SeoAnalyzer;

/**
 * Content Intelligence Service
 * 
 * Provides intelligent analysis and transformations for documents
 * based on the Universal Canonical AST.
 */
class ContentIntelligenceService
{
    public function __construct(
        protected SeoAnalyzer $seoAnalyzer
    ) {}

    /**
     * Analyze a document AST for SEO and readability.
     * 
     * @param array $canonicalAst
     * @param array $options
     * @return array
     */
    public function analyze(array $canonicalAst, array $options = []): array
    {
        $plainText = CanonicalDocumentSchema::extractPlainText($canonicalAst);
        $wordCount = CanonicalDocumentSchema::calculateWordCount($canonicalAst);
        
        // Structure analysis
        $structure = $this->analyzeStructure($canonicalAst['content'] ?? []);

        return [
            'telemetry' => [
                'word_count' => $wordCount,
                'char_count' => strlen($plainText),
                'reading_time' => CanonicalDocumentSchema::estimateReadingTime($canonicalAst),
            ],
            'structure' => $structure,
            'seo' => $this->seoAnalyzer->analyze($plainText, $options['focus_keyword'] ?? null, $structure),
        ];
    }

    /**
     * Analyze document structure (headings, images, lists).
     */
    protected function analyzeStructure(array $nodes): array
    {
        $stats = [
            'headings' => ['h1' => 0, 'h2' => 0, 'h3' => 0, 'h4' => 0, 'h5' => 0, 'h6' => 0],
            'images' => 0,
            'links' => 0,
            'paragraphs' => 0,
            'lists' => 0,
        ];

        foreach ($nodes as $node) {
            if ($node['type'] === 'heading') {
                $level = $node['attrs']['level'] ?? 1;
                $stats['headings']["h{$level}"]++;
            } elseif ($node['type'] === 'paragraph') {
                $stats['paragraphs']++;
            } elseif ($node['type'] === 'image') {
                $stats['images']++;
            } elseif ($node['type'] === 'bullet_list' || $node['type'] === 'ordered_list') {
                $stats['lists']++;
            }

            if (isset($node['content'])) {
                $subStats = $this->analyzeStructure($node['content']);
                // Merge sub-stats
                foreach ($subStats['headings'] as $lvl => $count) $stats['headings'][$lvl] += $count;
                $stats['images'] += $subStats['images'];
                $stats['paragraphs'] += $subStats['paragraphs'];
                $stats['lists'] += $subStats['lists'];
            }
        }

        return $stats;
    }
}
