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

namespace App\Features\KnowledgeBase\Services;

class SemanticChunker
{
    /**
     * Chunk document text semantically by headers, paragraphs, and token windows
     *
     * @param string $text
     * @param int $targetChunkTokens
     * @param int $overlapTokens
     * @return array<int, array{chunk_index: int, content: string, token_count: int}>
     */
    public function chunk(string $text, int $targetChunkTokens = 350, int $overlapTokens = 50): array
    {
        $text = trim($text);
        if (empty($text)) {
            return [];
        }

        // Split initially by markdown headers (#, ##, ###) or double newlines
        $sections = preg_split('/(?:\r\n|\r|\n)\s*(?=#{1,4}\s+)|(?:\r\n|\r|\n){2,}/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentChunk = '';
        $currentTokens = 0;
        $chunkIndex = 0;

        foreach ($sections as $section) {
            $secText = trim($section);
            if (empty($secText)) continue;

            $secTokens = $this->estimateTokens($secText);

            // If a single section is larger than the target chunk, split it by sentences
            if ($secTokens > $targetChunkTokens) {
                if (!empty($currentChunk)) {
                    $chunks[] = [
                        'chunk_index' => $chunkIndex++,
                        'content' => trim($currentChunk),
                        'token_count' => $currentTokens,
                    ];
                    $currentChunk = '';
                    $currentTokens = 0;
                }

                $sentences = preg_split('/(?<=[.?!])\s+/u', $secText, -1, PREG_SPLIT_NO_EMPTY);
                $subChunk = '';
                $subTokens = 0;

                foreach ($sentences as $s) {
                    $sTokens = $this->estimateTokens($s);
                    if ($subTokens + $sTokens > $targetChunkTokens && !empty($subChunk)) {
                        $chunks[] = [
                            'chunk_index' => $chunkIndex++,
                            'content' => trim($subChunk),
                            'token_count' => $subTokens,
                        ];

                        // Keep overlap
                        $overlapText = $this->extractOverlap($subChunk, $overlapTokens);
                        $subChunk = $overlapText . ' ' . $s;
                        $subTokens = $this->estimateTokens($subChunk);
                    } else {
                        $subChunk .= (empty($subChunk) ? '' : ' ') . $s;
                        $subTokens += $sTokens;
                    }
                }

                if (!empty(trim($subChunk))) {
                    $chunks[] = [
                        'chunk_index' => $chunkIndex++,
                        'content' => trim($subChunk),
                        'token_count' => $subTokens,
                    ];
                }
            } else {
                if ($currentTokens + $secTokens > $targetChunkTokens && !empty($currentChunk)) {
                    $chunks[] = [
                        'chunk_index' => $chunkIndex++,
                        'content' => trim($currentChunk),
                        'token_count' => $currentTokens,
                    ];

                    $overlapText = $this->extractOverlap($currentChunk, $overlapTokens);
                    $currentChunk = $overlapText . "\n\n" . $secText;
                    $currentTokens = $this->estimateTokens($currentChunk);
                } else {
                    $currentChunk .= (empty($currentChunk) ? '' : "\n\n") . $secText;
                    $currentTokens += $secTokens;
                }
            }
        }

        if (!empty(trim($currentChunk))) {
            $chunks[] = [
                'chunk_index' => $chunkIndex++,
                'content' => trim($currentChunk),
                'token_count' => $currentTokens,
            ];
        }

        return $chunks;
    }

    /**
     * Estimate token count (1 token ~= 4 characters or ~0.75 words for English)
     */
    public function estimateTokens(string $text): int
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
        $charCount = mb_strlen($text);

        return max(1, (int) round(($wordCount * 1.3) + ($charCount / 20)));
    }

    /**
     * Extract the last N tokens from a text chunk for overlapping window
     */
    protected function extractOverlap(string $text, int $overlapTokens): string
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) <= $overlapTokens) {
            return '';
        }

        $slice = array_slice($words, -$overlapTokens);
        return implode(' ', $slice);
    }
}