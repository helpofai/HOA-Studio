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

namespace App\Features\SEO\Actions;

use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\AI\Services\OmniRouteClient;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateSeoMetadata
{
    public function __construct(
        protected OmniRouteClient $client,
        protected RecordGenerationUsage $recordUsage
    ) {}

    /**
     * Generate 3 click-worthy Meta Descriptions (150-160 chars) matching focus keyword.
     * Dual-Mode: Uses AI if available, otherwise executes local algorithmic generation.
     */
    public function generateMetaDescriptions(User $user, string $documentText, ?string $keyword = null): array
    {
        if ($user->hasQuota(1)) {
            try {
                $systemPrompt = "You are an elite SEO copywriter. Generate exactly 3 compelling, high-CTR meta descriptions (strictly between 145 and 160 characters each). Each meta description must include the target keyword naturally and end with a clear call to action. Return ONLY valid JSON formatted as: [\"desc1\", \"desc2\", \"desc3\"].";
                $prompt = "Target Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

                $response = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ], ['model' => 'auto', 'temperature' => 0.7]);

                $content = trim($response['content']);
                $wordsUsed = max(1, str_word_count(strip_tags($content)));

                $this->recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $response['total_tokens'] ?? 0,
                    'model_slug' => $response['model'] ?? 'omniroute',
                ]);

                $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return array_slice($decoded, 0, 3);
                }
            } catch (Exception $e) {
                Log::warning('GenerateSeoMetadata: AI meta generation failed, falling back to local algorithm: ' . $e->getMessage());
            }
        }

        return $this->generateMetaDescriptionsAlgorithmic($documentText, $keyword);
    }

    /**
     * Generate 3 high-converting SEO Title Tags.
     * Dual-Mode: Uses AI if available, otherwise executes local algorithmic generation.
     */
    public function generateTitles(User $user, string $documentText, ?string $keyword = null): array
    {
        if ($user->hasQuota(1)) {
            try {
                $systemPrompt = "You are a master SEO title copywriter. Generate 3 click-magnet, search-optimized title tags (between 50 and 60 characters). Front-load the primary keyword. Return ONLY valid JSON formatted as: [\"title1\", \"title2\", \"title3\"].";
                $prompt = "Target Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

                $response = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ], ['model' => 'auto', 'temperature' => 0.7]);

                $content = trim($response['content']);
                $wordsUsed = max(1, str_word_count(strip_tags($content)));

                $this->recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $response['total_tokens'] ?? 0,
                    'model_slug' => $response['model'] ?? 'omniroute',
                ]);

                $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return array_slice($decoded, 0, 3);
                }
            } catch (Exception $e) {
                Log::warning('GenerateSeoMetadata: AI title generation failed, falling back to local algorithm: ' . $e->getMessage());
            }
        }

        return $this->generateTitlesAlgorithmic($documentText, $keyword);
    }

    /**
     * Suggest Semantic (LSI) Keywords.
     * Dual-Mode: Uses AI if available, otherwise executes local algorithmic generation.
     */
    public function suggestKeywords(User $user, string $documentText, ?string $primaryKeyword = null): array
    {
        if ($user->hasQuota(1)) {
            try {
                $systemPrompt = "You are an SEO semantic search expert. Analyze the topic and primary keyword, and return a list of 8 high-relevance semantic entities, synonyms, and secondary keywords (LSI keywords) to improve topical authority. Return ONLY valid JSON formatted as: [\"keyword1\", \"keyword2\", ...].";
                $prompt = "Primary Keyword: " . ($primaryKeyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

                $response = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ], ['model' => 'auto', 'temperature' => 0.6]);

                $content = trim($response['content']);
                $wordsUsed = max(1, str_word_count(strip_tags($content)));

                $this->recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $response['total_tokens'] ?? 0,
                    'model_slug' => $response['model'] ?? 'omniroute',
                ]);

                $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return array_slice($decoded, 0, 8);
                }
            } catch (Exception $e) {
                Log::warning('GenerateSeoMetadata: AI keyword suggestion failed, falling back to local algorithm: ' . $e->getMessage());
            }
        }

        return $this->suggestKeywordsAlgorithmic($documentText, $primaryKeyword);
    }

    /**
     * Generate structured FAQ pairs (Questions + Answers).
     * Dual-Mode: Uses AI if available, otherwise executes local algorithmic generation.
     */
    public function generateFaqs(User $user, string $documentText, ?string $keyword = null): array
    {
        if ($user->hasQuota(1)) {
            try {
                $systemPrompt = "You are an SEO structured data expert. Analyze the content and generate 4 high-value, highly searched FAQ pairs. Return ONLY valid JSON formatted as: [{\"question\": \"...\", \"answer\": \"...\"}, ...].";
                $prompt = "Target Topic / Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

                $response = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ], ['model' => 'auto', 'temperature' => 0.6]);

                $content = trim($response['content']);
                $wordsUsed = max(1, str_word_count(strip_tags($content)));

                $this->recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $response['total_tokens'] ?? 0,
                    'model_slug' => $response['model'] ?? 'omniroute',
                ]);

                $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return array_slice($decoded, 0, 4);
                }
            } catch (Exception $e) {
                Log::warning('GenerateSeoMetadata: AI FAQ generation failed, falling back to local algorithm: ' . $e->getMessage());
            }
        }

        return $this->generateFaqsAlgorithmic($documentText, $keyword);
    }

    /**
     * Generate a Quick Answer / TL;DR Box.
     * Dual-Mode: Uses AI if available, otherwise executes local algorithmic generation.
     */
    public function generateQuickAnswer(User $user, string $documentText, ?string $keyword = null): string
    {
        if ($user->hasQuota(1)) {
            try {
                $systemPrompt = "You are an SEO intent optimization expert. Generate a concise, 2-3 sentence 'Quick Answer / Summary' box addressing the primary search intent. Output clean HTML with <strong> and concise bullet points if needed. Do not wrap in markdown code blocks.";
                $prompt = "Target Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

                $response = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ], ['model' => 'auto', 'temperature' => 0.6]);

                $content = trim($response['content']);
                $wordsUsed = max(1, str_word_count(strip_tags($content)));

                $this->recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $response['total_tokens'] ?? 0,
                    'model_slug' => $response['model'] ?? 'omniroute',
                ]);

                if (!empty($content)) {
                    return $content;
                }
            } catch (Exception $e) {
                Log::warning('GenerateSeoMetadata: AI Quick Answer generation failed, falling back to local algorithm: ' . $e->getMessage());
            }
        }

        return $this->generateQuickAnswerAlgorithmic($documentText, $keyword);
    }

    /**
     * Identify Content Gaps & Missing Topics.
     * Dual-Mode: Uses AI if available, otherwise executes local algorithmic generation.
     */
    public function generateContentGaps(User $user, string $documentText, ?string $keyword = null): array
    {
        if ($user->hasQuota(1)) {
            try {
                $systemPrompt = "You are a senior SEO content strategist. Analyze the content and return 4 critical missing subtopics or questions required to outperform top-ranking competitors. Return ONLY valid JSON formatted as: [{\"topic\": \"...\", \"reason\": \"...\", \"suggested_h2\": \"...\"}, ...].";
                $prompt = "Primary Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

                $response = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ], ['model' => 'auto', 'temperature' => 0.6]);

                $content = trim($response['content']);
                $wordsUsed = max(1, str_word_count(strip_tags($content)));

                $this->recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $response['total_tokens'] ?? 0,
                    'model_slug' => $response['model'] ?? 'omniroute',
                ]);

                $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return array_slice($decoded, 0, 4);
                }
            } catch (Exception $e) {
                Log::warning('GenerateSeoMetadata: AI Content Gaps failed, falling back to local algorithm: ' . $e->getMessage());
            }
        }

        return $this->generateContentGapsAlgorithmic($documentText, $keyword);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LOCAL DETERMINISTIC ALGORITHMS & LINGUISTIC NLP (ZERO TOKENS / OFFLINE)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Fallback Local Algorithmic Title Generator (Zero Tokens / Offline)
     */
    public function generateTitlesAlgorithmic(string $documentText, ?string $keyword = null): array
    {
        $kw = !empty($keyword) ? trim($keyword) : $this->extractPrimaryTopic($documentText);
        $capKw = Str::headline($kw);
        $year = date('Y');

        $templates = [
            "The Complete Guide to {$capKw} ({$year})",
            "How to Master {$capKw}: Step-by-Step Blueprint",
            "7 Proven {$capKw} Strategies for Top Results",
            "{$capKw} Explained: Essential Best Practices",
            "Why {$capKw} Matters & How to Implement It",
        ];

        $results = [];
        foreach ($templates as $t) {
            $results[] = mb_strlen($t) > 65 ? Str::limit($t, 60, '') : $t;
            if (count($results) >= 3) break;
        }

        return $results;
    }

    /**
     * Fallback Local Algorithmic Meta Description Generator (Zero Tokens / Offline)
     */
    public function generateMetaDescriptionsAlgorithmic(string $documentText, ?string $keyword = null): array
    {
        $kw = !empty($keyword) ? trim($keyword) : $this->extractPrimaryTopic($documentText);
        $capKw = Str::headline($kw);
        $year = date('Y');

        $cleanText = trim(strip_tags($documentText));
        $sentences = preg_split('/(?<=[.?!])\s+/u', $cleanText, -1, PREG_SPLIT_NO_EMPTY);
        $introExcerpt = !empty($sentences[0]) ? Str::limit(trim($sentences[0]), 80, '') : "Discover key insights and proven methodologies for {$kw}.";

        $desc1 = "Discover everything you need to know about {$capKw} in {$year}. Explore expert strategies, actionable advice, and key takeaways in this complete guide. Read now.";
        $desc2 = "Looking to optimize {$kw}? {$introExcerpt} Learn practical steps and best practices to achieve top results quickly. Get the full breakdown now.";
        $desc3 = "Master {$capKw} with our in-depth analysis. We break down core concepts, practical tips, and data-backed recommendations for top performance.";

        $format = function(string $text) {
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if (mb_strlen($text) > 158) {
                $text = Str::limit($text, 155, '...');
            }
            return $text;
        };

        return [
            $format($desc1),
            $format($desc2),
            $format($desc3),
        ];
    }

    /**
     * Fallback Local Algorithmic LSI & Semantic Keyword Extractor (Zero Tokens / Offline)
     */
    public function suggestKeywordsAlgorithmic(string $documentText, ?string $primaryKeyword = null): array
    {
        $kw = !empty($primaryKeyword) ? trim(mb_strtolower($primaryKeyword)) : '';
        $clean = strtolower(strip_tags($documentText));
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean);
        $words = array_values(array_filter(preg_split('/\s+/u', $clean), fn($w) => mb_strlen($w) > 3));

        $stopWords = [
            'this', 'that', 'with', 'from', 'have', 'more', 'about', 'your', 'will', 'what',
            'which', 'their', 'there', 'they', 'when', 'where', 'been', 'would', 'could',
            'should', 'these', 'those', 'also', 'into', 'just', 'some', 'than', 'them'
        ];

        $phraseCounts = [];
        $totalWords = count($words);
        for ($i = 0; $i < $totalWords - 1; $i++) {
            $w1 = $words[$i];
            $w2 = $words[$i + 1];
            if (!in_array($w1, $stopWords) && !in_array($w2, $stopWords)) {
                $bi = "{$w1} {$w2}";
                if ($bi !== $kw) {
                    $phraseCounts[$bi] = ($phraseCounts[$bi] ?? 0) + 1;
                }
            }
            if ($i < $totalWords - 2) {
                $w3 = $words[$i + 2];
                if (!in_array($w1, $stopWords) && !in_array($w3, $stopWords)) {
                    $tri = "{$w1} {$w2} {$w3}";
                    if ($tri !== $kw) {
                        $phraseCounts[$tri] = ($phraseCounts[$tri] ?? 0) + 2;
                    }
                }
            }
        }

        arsort($phraseCounts);
        $topPhrases = array_keys(array_slice($phraseCounts, 0, 8));

        $semanticModifiers = ['best practices', 'strategy', 'optimization', 'guide', 'framework', 'tools', 'examples', 'metrics'];
        foreach ($semanticModifiers as $mod) {
            if (count($topPhrases) >= 8) break;
            $candidate = $kw ? "{$kw} {$mod}" : $mod;
            if (!in_array($candidate, $topPhrases)) {
                $topPhrases[] = $candidate;
            }
        }

        return array_slice($topPhrases, 0, 8);
    }

    /**
     * Fallback Local Algorithmic FAQ Generator (Zero Tokens / Offline)
     */
    public function generateFaqsAlgorithmic(string $documentText, ?string $keyword = null): array
    {
        $kw = !empty($keyword) ? trim($keyword) : $this->extractPrimaryTopic($documentText);
        $capKw = Str::headline($kw);

        return [
            [
                'question' => "What is {$capKw} and why is it important?",
                'answer' => "{$capKw} provides a structured framework and actionable strategies to maximize efficiency, quality, and measurable search outcomes."
            ],
            [
                'question' => "How can you implement {$capKw} effectively?",
                'answer' => "Follow a structured approach: establish foundational baselines, align core structural parameters, and iterate continuously based on empirical data."
            ],
            [
                'question' => "What are the primary benefits of {$capKw}?",
                'answer' => "Key benefits include improved audience engagement, higher organic authority, streamlined scannability, and scalable publishing consistency."
            ],
            [
                'question' => "What common mistakes should be avoided?",
                'answer' => "Avoid overlooking direct intent answers, neglecting comprehensive formatting, creating bulky paragraphs without subheadings, and lacking citation evidence."
            ]
        ];
    }

    /**
     * Fallback Local Algorithmic Content Gap Detector (Zero Tokens / Offline)
     */
    public function generateContentGapsAlgorithmic(string $documentText, ?string $keyword = null): array
    {
        $kw = !empty($keyword) ? trim($keyword) : $this->extractPrimaryTopic($documentText);
        $capKw = Str::headline($kw);
        $lower = mb_strtolower($documentText);

        $gaps = [];

        if (!str_contains($lower, 'step') && !str_contains($lower, 'guide') && !str_contains($lower, 'how to')) {
            $gaps[] = [
                'topic' => 'Step-by-Step Implementation Framework',
                'reason' => 'High-ranking search results provide structured execution instructions for readers.',
                'suggested_h2' => "How to Implement {$capKw}: Step-by-Step"
            ];
        }

        if (!str_contains($lower, 'vs') && !str_contains($lower, 'comparison') && !str_contains($lower, 'table') && !str_contains($lower, 'benchmark')) {
            $gaps[] = [
                'topic' => 'Comparative Analysis & Benchmarks',
                'reason' => 'Searchers frequently compare solutions and evaluate metrics before making decisions.',
                'suggested_h2' => "{$capKw} Comparison & Performance Benchmarks"
            ];
        }

        if (!str_contains($lower, 'mistake') && !str_contains($lower, 'pitfall') && !str_contains($lower, 'avoid') && !str_contains($lower, 'troubleshoot')) {
            $gaps[] = [
                'topic' => 'Common Mistakes & Troubleshooting',
                'reason' => 'Highlighting pitfalls builds trust and captures high-intent troubleshooting queries.',
                'suggested_h2' => "Critical Mistakes to Avoid with {$capKw}"
            ];
        }

        if (!str_contains($lower, 'trend') && !str_contains($lower, 'future') && !str_contains($lower, 'evolution')) {
            $gaps[] = [
                'topic' => 'Future Trends & Key Developments',
                'reason' => 'Forward-looking analysis establishes topical authority and keeps content current.',
                'suggested_h2' => "The Future of {$capKw}: Trends to Watch"
            ];
        }

        return $gaps;
    }

    /**
     * Fallback Local Algorithmic Quick Answer Generator (Zero Tokens / Offline)
     */
    public function generateQuickAnswerAlgorithmic(string $documentText, ?string $keyword = null): string
    {
        $kw = !empty($keyword) ? trim($keyword) : $this->extractPrimaryTopic($documentText);
        $capKw = Str::headline($kw);

        return "<div class=\"geo-direct-answer my-3 p-3.5 rounded-xl bg-purple-950/20 border border-purple-500/30 text-slate-200 text-xs leading-relaxed\">"
            . "<strong>Quick Answer: </strong>{$capKw} is an essential discipline designed to achieve optimal performance through systematic implementation, empirical validation, and user-centric best practices. Focus on clear execution and continuous metric tracking."
            . "</div>";
    }

    /**
     * Helper to extract primary topic if keyword is missing
     */
    protected function extractPrimaryTopic(string $text): string
    {
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $text, $m)) {
            $topic = trim(strip_tags($m[1]));
            if (!empty($topic)) return Str::limit($topic, 30, '');
        }
        $words = preg_split('/\s+/u', trim(strip_tags($text)), -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($words)) {
            return implode(' ', array_slice($words, 0, min(3, count($words))));
        }
        return 'Content Strategy';
    }
}