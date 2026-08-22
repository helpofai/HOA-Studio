<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Rank Math Pro SEO Analyzer
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Features 4 Pillar Rank Math Evaluation Engine:
| 1. Basic SEO (Title, Meta, URL, Intro, Body, Word Count)
| 2. Additional SEO (Subheadings, Image Alt, Density, URL Length, Outbound Links, Internal Links)
| 3. Title Readability (Front-loaded keyword, Numbers, Power words, Sentiment)
| 4. Content Readability (TOC/Headings, Short paragraphs, Sentence length, Media)
|
*/

namespace App\Features\SEO\Services;

use Illuminate\Support\Str;

class SeoAnalyzer
{
    /**
     * Power words dictionary for Title Readability
     */
    protected array $powerWords = [
        'ultimate', 'proven', 'essential', 'master', 'breakthrough', 'complete',
        'guide', 'review', 'best', 'top', 'fast', 'easy', 'secret', 'definitive',
        'expert', 'advanced', 'strategy', 'blueprint', 'guaranteed', 'epic',
        'unleashed', 'framework', 'formula', 'insider', 'supercharged', 'step-by-step'
    ];

    /**
     * Analyze document content and calculate comprehensive Rank Math SEO & Readability metrics
     */
    public function analyze(string $htmlContent, string $title = '', ?string $targetKeyword = null, array $secondaryKeywords = [], string $metaDescription = ''): array
    {
        // Spaced HTML for accurate word and sentence segmentation
        $spacedHtml = preg_replace('/<\/(h[1-6]|p|div|li|blockquote)>/i', "$0. ", $htmlContent);
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($spacedHtml)));
        $words = !empty($plainText) ? preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
        $totalWords = count($words);

        // Sentences
        $sentences = !empty($plainText) ? preg_split('/(?<=[.?!])\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
        $totalSentences = max(1, count($sentences));

        // Paragraphs extraction
        preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $htmlContent, $pMatches);
        $rawParagraphs = array_map('strip_tags', $pMatches[1] ?? []);
        $totalParagraphs = max(1, count($rawParagraphs));

        // Syllables estimation for Flesch Readability
        $totalSyllables = 0;
        foreach ($words as $w) {
            $totalSyllables += $this->countSyllables($w);
        }

        // Flesch Reading Ease Score
        $fleschScore = 0;
        $readingGrade = 'N/A';
        if ($totalWords > 0) {
            $wordsPerSentence = $totalWords / $totalSentences;
            $syllablesPerWord = $totalSyllables / $totalWords;
            $fleschScore = 206.835 - (1.015 * $wordsPerSentence) - (84.6 * $syllablesPerWord);
            $fleschScore = (int) round(max(0, min(100, $fleschScore)));

            if ($fleschScore >= 90) $readingGrade = 'Very Easy (5th grade)';
            elseif ($fleschScore >= 80) $readingGrade = 'Easy (6th grade)';
            elseif ($fleschScore >= 70) $readingGrade = 'Fairly Easy (7th grade)';
            elseif ($fleschScore >= 60) $readingGrade = 'Standard (8th-9th grade)';
            elseif ($fleschScore >= 50) $readingGrade = 'Fairly Difficult (High school)';
            elseif ($fleschScore >= 30) $readingGrade = 'Difficult (College level)';
            else $readingGrade = 'Very Difficult (Academic/Graduate)';
        }

        // Extract Headings
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $htmlContent, $h1Matches);
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $htmlContent, $h2Matches);
        preg_match_all('/<h3[^>]*>(.*?)<\/h3>/si', $htmlContent, $h3Matches);

        $h1List = array_map('strip_tags', $h1Matches[1] ?? []);
        $h2List = array_map('strip_tags', $h2Matches[1] ?? []);
        $h3List = array_map('strip_tags', $h3Matches[1] ?? []);

        // Extract Links & Images
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']*)["\']/si', $htmlContent, $linkMatches);
        preg_match_all('/<img\s+[^>]*alt=["\']([^"\']*)["\']/si', $htmlContent, $imgMatches);
        
        $links = $linkMatches[1] ?? [];
        $imgAlts = $imgMatches[1] ?? [];
        $totalLinks = count($links);
        $totalImages = count($imgAlts);

        // Internal vs External Links analysis
        $internalLinksCount = 0;
        $externalLinksCount = 0;
        foreach ($links as $link) {
            if (Str::startsWith($link, ['/', '#', 'http://localhost', 'http://127.0.0.1', 'https://helpofai.com'])) {
                $internalLinksCount++;
            } else {
                $externalLinksCount++;
            }
        }

        // Long sentences (> 20 words)
        $longSentencesCount = 0;
        foreach ($sentences as $s) {
            $sWords = preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY);
            if (count($sWords) > 20) {
                $longSentencesCount++;
            }
        }
        $longSentencesPct = $totalSentences > 0 ? round(($longSentencesCount / $totalSentences) * 100) : 0;

        // Long Paragraphs (> 120 words)
        $longParagraphsCount = 0;
        foreach ($rawParagraphs as $p) {
            $pWords = count(preg_split('/\s+/u', trim($p), -1, PREG_SPLIT_NO_EMPTY));
            if ($pWords > 120) {
                $longParagraphsCount++;
            }
        }

        // Target Keyword Data
        $kw = $targetKeyword ? trim(mb_strtolower($targetKeyword)) : null;
        $kwWordsCount = $kw ? count(preg_split('/\s+/u', $kw, -1, PREG_SPLIT_NO_EMPTY)) : 1;
        $slug = Str::slug($title ?: 'untitled');

        $kwData = [
            'target_keyword' => $targetKeyword,
            'count' => 0,
            'density' => 0.0,
            'in_title' => false,
            'in_first_10_pct' => false,
            'in_meta' => false,
            'in_url' => false,
            'in_h1' => false,
            'in_subheadings' => false,
            'in_img_alt' => false,
        ];

        if ($kw && $totalWords > 0) {
            $lowerText = mb_strtolower($plainText);
            $lowerTitle = mb_strtolower($title);
            $lowerMeta = mb_strtolower($metaDescription);
            $lowerSlug = mb_strtolower(str_replace('-', ' ', $slug));
            $lowerH1 = mb_strtolower(implode(' ', $h1List));
            $lowerSubheadings = mb_strtolower(implode(' ', array_merge($h2List, $h3List)));
            $lowerImgAlts = mb_strtolower(implode(' ', $imgAlts));

            // First 10% of content
            $tenPercentWordsCount = max(10, (int) round($totalWords * 0.1));
            $first10PctWords = mb_strtolower(implode(' ', array_slice($words, 0, $tenPercentWordsCount)));

            $kwOccurrences = mb_substr_count($lowerText, $kw);
            $kwData['count'] = $kwOccurrences;
            // Rank Math Keyword Density Formula: (Keyword Count * Words in Keyword / Total Words) * 100
            $kwData['density'] = round((($kwOccurrences * $kwWordsCount) / max(1, $totalWords)) * 100, 2);
            
            $kwData['in_title'] = mb_strpos($lowerTitle, $kw) !== false;
            $kwData['in_first_10_pct'] = mb_strpos($first10PctWords, $kw) !== false;
            $kwData['in_meta'] = !empty($metaDescription) && mb_strpos($lowerMeta, $kw) !== false;
            $kwData['in_url'] = mb_strpos($lowerSlug, str_replace(' ', ' ', $kw)) !== false || mb_strpos(Str::slug($title), Str::slug($kw)) !== false;
            $kwData['in_h1'] = mb_strpos($lowerH1, $kw) !== false;
            $kwData['in_subheadings'] = mb_strpos($lowerSubheadings, $kw) !== false;
            $kwData['in_img_alt'] = mb_strpos($lowerImgAlts, $kw) !== false;
        }

        // Title Readability Extra Analysis
        $titleStartsWithKw = false;
        $titleHasNumber = preg_match('/\d+/', $title) === 1;
        $titleHasPowerWord = false;
        $matchedPowerWord = '';

        if (!empty($title)) {
            $lowerTitle = mb_strtolower($title);
            if ($kw && (mb_strpos($lowerTitle, $kw) === 0 || mb_strpos($lowerTitle, $kw) < 15)) {
                $titleStartsWithKw = true;
            }
            foreach ($this->powerWords as $pw) {
                if (mb_strpos($lowerTitle, $pw) !== false) {
                    $titleHasPowerWord = true;
                    $matchedPowerWord = $pw;
                    break;
                }
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // RANK MATH 4-PILLAR CHECKLIST COMPUTATION
        // ══════════════════════════════════════════════════════════════════════

        // PILLAR 1: Basic SEO (40 Points Max)
        $basicChecks = [
            [
                'id' => 'kw_in_title',
                'title' => 'Focus Keyword in SEO Title',
                'desc' => 'Primary keyword appears in the document title tag.',
                'pass' => $kw ? $kwData['in_title'] : false,
                'weight' => 10,
            ],
            [
                'id' => 'kw_in_meta',
                'title' => 'Focus Keyword in Meta Description',
                'desc' => 'Primary keyword appears in the meta description.',
                'pass' => $kw ? $kwData['in_meta'] : (!empty($metaDescription)),
                'weight' => 8,
            ],
            [
                'id' => 'kw_in_url',
                'title' => 'Focus Keyword in the URL',
                'desc' => 'Primary keyword is present in the permalink slug.',
                'pass' => $kw ? $kwData['in_url'] : true,
                'weight' => 6,
            ],
            [
                'id' => 'kw_in_intro',
                'title' => 'Focus Keyword in First 10% of Content',
                'desc' => 'Primary keyword appears in the introduction hook.',
                'pass' => $kw ? $kwData['in_first_10_pct'] : false,
                'weight' => 6,
            ],
            [
                'id' => 'kw_in_body',
                'title' => 'Focus Keyword found in Content Body',
                'desc' => 'Primary keyword is used naturally across paragraphs.',
                'pass' => $kw ? ($kwData['count'] >= 2) : false,
                'weight' => 5,
            ],
            [
                'id' => 'content_length',
                'title' => 'Content Length Check',
                'desc' => "Current length is {$totalWords} words (600+ words recommended).",
                'pass' => $totalWords >= 600,
                'weight' => 5,
            ],
        ];

        // PILLAR 2: Additional SEO (30 Points Max)
        $densityValid = $kw ? ($kwData['density'] >= 0.8 && $kwData['density'] <= 2.5) : true;
        $urlLengthValid = strlen($slug) <= 75;

        $additionalChecks = [
            [
                'id' => 'kw_in_subheadings',
                'title' => 'Focus Keyword in Subheadings (H2, H3)',
                'desc' => 'Primary keyword found in H2 or H3 heading tags.',
                'pass' => $kw ? $kwData['in_subheadings'] : (count($h2List) >= 2),
                'weight' => 6,
            ],
            [
                'id' => 'kw_in_img_alt',
                'title' => 'Focus Keyword in Image Alt Attributes',
                'desc' => 'Images contain alt text containing the focus keyword.',
                'pass' => $kw ? $kwData['in_img_alt'] : ($totalImages > 0),
                'weight' => 5,
            ],
            [
                'id' => 'keyword_density',
                'title' => "Keyword Density ({$kwData['density']}%)",
                'desc' => 'Keyword density is within the ideal 0.8% - 2.5% range.',
                'pass' => $densityValid && ($kw ? $kwData['count'] > 0 : true),
                'weight' => 6,
            ],
            [
                'id' => 'url_length',
                'title' => 'URL Permalinks Length',
                'desc' => 'URL slug length is concise (under 75 characters).',
                'pass' => $urlLengthValid,
                'weight' => 4,
            ],
            [
                'id' => 'external_links',
                'title' => "External Outbound Citations ({$externalLinksCount})",
                'desc' => 'Authoritative external citations found in content.',
                'pass' => $externalLinksCount >= 1,
                'weight' => 5,
            ],
            [
                'id' => 'internal_links',
                'title' => "Internal Cluster Links ({$internalLinksCount})",
                'desc' => 'Internal links linking to related topics and resources.',
                'pass' => $internalLinksCount >= 1,
                'weight' => 4,
            ],
        ];

        // PILLAR 3: Title Readability (15 Points Max)
        $titleChecks = [
            [
                'id' => 'kw_at_beginning_of_title',
                'title' => 'Focus Keyword at Start of Title',
                'desc' => 'Primary keyword is front-loaded in the first half of the title.',
                'pass' => $kw ? $titleStartsWithKw : (!empty($title)),
                'weight' => 6,
            ],
            [
                'id' => 'title_has_number',
                'title' => 'Title Contains a Number',
                'desc' => 'Numbers in titles increase click-through rates (CTR).',
                'pass' => $titleHasNumber,
                'weight' => 5,
            ],
            [
                'id' => 'title_has_power_word',
                'title' => 'Title Contains a Power Word',
                'desc' => $titleHasPowerWord ? "Power word detected ('{$matchedPowerWord}')." : 'Add a persuasive power word to boost CTR.',
                'pass' => $titleHasPowerWord,
                'weight' => 4,
            ],
        ];

        // PILLAR 4: Content Readability (15 Points Max)
        $contentReadabilityChecks = [
            [
                'id' => 'headings_toc',
                'title' => 'Use Table of Contents / Headings Structure',
                'desc' => 'Content utilizes H2 and H3 tags for scannability.',
                'pass' => count($h2List) >= 2,
                'weight' => 4,
            ],
            [
                'id' => 'short_paragraphs',
                'title' => 'Short & Scannable Paragraphs',
                'desc' => 'Paragraphs are bite-sized (under 120 words each).',
                'pass' => $longParagraphsCount === 0 && $totalParagraphs >= 2,
                'weight' => 4,
            ],
            [
                'id' => 'sentence_length',
                'title' => 'Sentence Length Check',
                'desc' => "Only {$longSentencesPct}% of sentences exceed 20 words (target: < 25%).",
                'pass' => $longSentencesPct <= 25,
                'weight' => 4,
            ],
            [
                'id' => 'rich_media',
                'title' => 'Rich Media Usage',
                'desc' => 'Content contains images, callouts, or tables.',
                'pass' => $totalImages >= 1 || (strpos($htmlContent, '<table') !== false),
                'weight' => 3,
            ],
        ];

        // Calculate Aggregate Rank Math Score (0 - 100)
        $totalEarned = 0;
        $totalPossible = 100;

        foreach (array_merge($basicChecks, $additionalChecks, $titleChecks, $contentReadabilityChecks) as $check) {
            if ($check['pass']) {
                $totalEarned += $check['weight'];
            }
        }

        if (!$kw) {
            $totalEarned = max(20, min(80, (int) round($totalEarned * 0.85)));
        }

        $rankMathScore = (int) max(0, min(100, $totalEarned));

        // Category pass counters
        $countPassed = fn($arr) => count(array_filter($arr, fn($c) => $c['pass']));

        return [
            'score' => $rankMathScore,
            'readability_score' => $fleschScore,
            'reading_grade' => $readingGrade,
            'rank_math' => [
                'basic_seo' => [
                    'title' => 'Basic SEO',
                    'score_label' => $countPassed($basicChecks) . '/' . count($basicChecks) . ' Passed',
                    'checks' => $basicChecks,
                ],
                'additional_seo' => [
                    'title' => 'Additional SEO',
                    'score_label' => $countPassed($additionalChecks) . '/' . count($additionalChecks) . ' Passed',
                    'checks' => $additionalChecks,
                ],
                'title_readability' => [
                    'title' => 'Title Readability',
                    'score_label' => $countPassed($titleChecks) . '/' . count($titleChecks) . ' Passed',
                    'checks' => $titleChecks,
                ],
                'content_readability' => [
                    'title' => 'Content Readability',
                    'score_label' => $countPassed($contentReadabilityChecks) . '/' . count($contentReadabilityChecks) . ' Passed',
                    'checks' => $contentReadabilityChecks,
                ],
            ],
            'metrics' => [
                'words' => $totalWords,
                'sentences' => $totalSentences,
                'paragraphs' => $totalParagraphs,
                'reading_time_minutes' => max(1, (int) ceil($totalWords / 200)),
                'headings' => [
                    'h1' => count($h1List),
                    'h2' => count($h2List),
                    'h3' => count($h3List),
                ],
                'links' => [
                    'total' => $totalLinks,
                    'internal' => $internalLinksCount,
                    'external' => $externalLinksCount,
                ],
                'images' => $totalImages,
                'long_sentences_pct' => $longSentencesPct,
                'keyword' => $kwData,
            ],
        ];
    }

    /**
     * Estimate syllable count for an English word
     */
    protected function countSyllables(string $word): int
    {
        $word = mb_strtolower(preg_replace('/[^a-zA-Z]/', '', $word));
        if (strlen($word) <= 3) {
            return 1;
        }

        $word = preg_replace('/(?:[^laeiouy]|ed|es|e)$/', '', $word);
        $word = preg_replace('/^y/', '', $word);
        preg_match_all('/[aeiouy]{1,2}/', $word, $matches);

        return max(1, count($matches[0] ?? []));
    }
}