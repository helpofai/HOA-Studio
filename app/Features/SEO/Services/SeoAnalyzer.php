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

namespace App\Features\SEO\Services;

class SeoAnalyzer
{
    /**
     * Analyze document content and calculate comprehensive SEO & readability metrics
     */
    public function analyze(string $htmlContent, string $title = '', ?string $targetKeyword = null, array $secondaryKeywords = []): array
    {
        // Add sentence boundary after block elements so headings and paragraphs are clean sentences
        $spacedHtml = preg_replace('/<\/(h[1-6]|p|div|li|blockquote)>/i', "$0. ", $htmlContent);
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($spacedHtml)));
        $words = !empty($plainText) ? preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
        $totalWords = count($words);

        // Sentences
        $sentences = !empty($plainText) ? preg_split('/(?<=[.?!])\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
        $totalSentences = max(1, count($sentences));

        // Paragraphs
        $paragraphs = array_filter(array_map('trim', explode("\n", $plainText)));
        $totalParagraphs = max(1, count($paragraphs));

        // Syllable count estimation for Flesch Readability
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

        // Heading tags extracted from HTML
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $htmlContent, $h1Matches);
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $htmlContent, $h2Matches);
        preg_match_all('/<h3[^>]*>(.*?)<\/h3>/si', $htmlContent, $h3Matches);

        $h1List = array_map('strip_tags', $h1Matches[1] ?? []);
        $h2List = array_map('strip_tags', $h2Matches[1] ?? []);
        $h3List = array_map('strip_tags', $h3Matches[1] ?? []);

        // Links & Images
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']*)["\']/si', $htmlContent, $linkMatches);
        preg_match_all('/<img\s+[^>]*alt=["\']([^"\']*)["\']/si', $htmlContent, $imgMatches);
        $totalLinks = count($linkMatches[1] ?? []);
        $totalImages = count($imgMatches[1] ?? []);

        // Long sentences (> 20 words)
        $longSentencesCount = 0;
        foreach ($sentences as $s) {
            $sWords = preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY);
            if (count($sWords) > 20) {
                $longSentencesCount++;
            }
        }
        $longSentencesPct = $totalSentences > 0 ? round(($longSentencesCount / $totalSentences) * 100) : 0;

        // Keyword analysis
        $kw = $targetKeyword ? trim(mb_strtolower($targetKeyword)) : null;
        $kwData = [
            'target_keyword' => $targetKeyword,
            'count' => 0,
            'density' => 0.0,
            'in_title' => false,
            'in_first_100_words' => false,
            'in_h1' => false,
            'in_h2' => false,
            'in_h3' => false,
        ];

        if ($kw && $totalWords > 0) {
            $lowerText = mb_strtolower($plainText);
            $lowerTitle = mb_strtolower($title);
            $lowerH1 = mb_strtolower(implode(' ', $h1List));
            $lowerH2 = mb_strtolower(implode(' ', $h2List));
            $lowerH3 = mb_strtolower(implode(' ', $h3List));

            $first100Words = mb_strtolower(implode(' ', array_slice($words, 0, 100)));

            $kwOccurrences = mb_substr_count($lowerText, $kw);
            $kwData['count'] = $kwOccurrences;
            $kwData['density'] = round(($kwOccurrences / max(1, $totalWords)) * 100, 2);
            $kwData['in_title'] = mb_strpos($lowerTitle, $kw) !== false;
            $kwData['in_first_100_words'] = mb_strpos($first100Words, $kw) !== false;
            $kwData['in_h1'] = mb_strpos($lowerH1, $kw) !== false;
            $kwData['in_h2'] = mb_strpos($lowerH2, $kw) !== false;
            $kwData['in_h3'] = mb_strpos($lowerH3, $kw) !== false;
        }

        // Secondary keywords analysis
        $secKeywordsData = [];
        foreach ($secondaryKeywords as $sk) {
            $skClean = trim($sk);
            if (empty($skClean)) continue;
            $skLower = mb_strtolower($skClean);
            $count = mb_substr_count(mb_strtolower($plainText), $skLower);
            $secKeywordsData[] = [
                'keyword' => $skClean,
                'count' => $count,
                'found' => $count > 0,
            ];
        }

        // Calculate SEO Checklist & Recommendations
        $recommendations = [];
        $seoScore = 0;

        // 1. Content Length Check (Max 25 pts)
        if ($totalWords >= 1200) {
            $seoScore += 25;
            $recommendations[] = ['type' => 'good', 'category' => 'Content Length', 'text' => "Great content length ({$totalWords} words). Comprehensive articles rank higher on search engines."];
        } elseif ($totalWords >= 600) {
            $seoScore += 15;
            $recommendations[] = ['type' => 'warning', 'category' => 'Content Length', 'text' => "Good start ({$totalWords} words), but aiming for 1,200+ words can improve ranking potential."];
        } elseif ($totalWords > 0) {
            $seoScore += 5;
            $recommendations[] = ['type' => 'critical', 'category' => 'Content Length', 'text' => "Content is too thin ({$totalWords} words). Recommended minimum is 600-1,200 words."];
        } else {
            $recommendations[] = ['type' => 'critical', 'category' => 'Content Length', 'text' => "Document is empty. Begin writing or use an AI template to generate content."];
        }

        // 2. Target Keyword Checks (Max 35 pts)
        if ($kw) {
            if ($kwData['in_title']) {
                $seoScore += 10;
                $recommendations[] = ['type' => 'good', 'category' => 'Keywords', 'text' => "Primary keyword '{$targetKeyword}' is present in the document title."];
            } else {
                $recommendations[] = ['type' => 'critical', 'category' => 'Keywords', 'text' => "Include primary keyword '{$targetKeyword}' in the document title."];
            }

            if ($kwData['in_first_100_words']) {
                $seoScore += 10;
                $recommendations[] = ['type' => 'good', 'category' => 'Keywords', 'text' => "Primary keyword appears in the first 100 words (introduction hook)."];
            } else {
                $recommendations[] = ['type' => 'warning', 'category' => 'Keywords', 'text' => "Include primary keyword '{$targetKeyword}' in the opening paragraph for faster topical recognition."];
            }

            if ($kwData['in_h2'] || $kwData['in_h3']) {
                $seoScore += 5;
                $recommendations[] = ['type' => 'good', 'category' => 'Keywords', 'text' => "Primary keyword is included in subheadings (H2/H3)."];
            } else {
                $recommendations[] = ['type' => 'warning', 'category' => 'Keywords', 'text' => "Include primary keyword '{$targetKeyword}' in at least one H2 or H3 subheading."];
            }

            if ($kwData['density'] >= 0.8 && $kwData['density'] <= 2.5) {
                $seoScore += 10;
                $recommendations[] = ['type' => 'good', 'category' => 'Keywords', 'text' => "Optimal keyword density ({$kwData['density']}%). Perfectly balanced without stuffing."];
            } elseif ($kwData['density'] > 2.5) {
                $seoScore += 3;
                $recommendations[] = ['type' => 'warning', 'category' => 'Keywords', 'text' => "Keyword density is high ({$kwData['density']}%). Consider replacing some mentions with synonyms to prevent stuffing."];
            } elseif ($kwData['count'] > 0) {
                $seoScore += 5;
                $recommendations[] = ['type' => 'warning', 'category' => 'Keywords', 'text' => "Keyword density is low ({$kwData['density']}%). Try referencing the primary keyword more naturally."];
            } else {
                $recommendations[] = ['type' => 'critical', 'category' => 'Keywords', 'text' => "Primary keyword '{$targetKeyword}' does not appear in the body text."];
            }
        } else {
            $recommendations[] = ['type' => 'warning', 'category' => 'Keywords', 'text' => "No focus keyword set. Add a primary keyword to evaluate SEO ranking factors."];
            $seoScore += 15; // Neutral baseline when no keyword set
        }

        // 3. Headings & Structure Checks (Max 20 pts)
        $totalHeadings = count($h1List) + count($h2List) + count($h3List);
        if (count($h2List) >= 2) {
            $seoScore += 15;
            $recommendations[] = ['type' => 'good', 'category' => 'Headings', 'text' => "Well-structured outline with " . count($h2List) . " H2 subheadings."];
        } elseif (count($h2List) == 1) {
            $seoScore += 8;
            $recommendations[] = ['type' => 'warning', 'category' => 'Headings', 'text' => "Add more H2 subheadings to break up content into scannable sections."];
        } else {
            $recommendations[] = ['type' => 'critical', 'category' => 'Headings', 'text' => "No H2 subheadings found. Break your content into structured sections."];
        }

        if (count($h1List) <= 1) {
            $seoScore += 5;
        } else {
            $recommendations[] = ['type' => 'warning', 'category' => 'Headings', 'text' => "Multiple H1 tags detected (" . count($h1List) . "). Use exactly one H1 tag per page."];
        }

        // 4. Readability & Sentences Checks (Max 20 pts)
        if ($fleschScore >= 60) {
            $seoScore += 15;
            $recommendations[] = ['type' => 'good', 'category' => 'Readability', 'text' => "Excellent readability ({$fleschScore}/100 — {$readingGrade}). Clear and engaging for readers."];
        } elseif ($fleschScore >= 45) {
            $seoScore += 10;
            $recommendations[] = ['type' => 'warning', 'category' => 'Readability', 'text' => "Moderate readability ({$fleschScore}/100 — {$readingGrade}). Consider shortening complex sentences."];
        } elseif ($totalWords > 0) {
            $seoScore += 5;
            $recommendations[] = ['type' => 'critical', 'category' => 'Readability', 'text' => "Difficult reading ease ({$fleschScore}/100 — {$readingGrade}). Simplify dense vocabulary and sentence lengths."];
        }

        if ($longSentencesPct <= 20) {
            $seoScore += 5;
            $recommendations[] = ['type' => 'good', 'category' => 'Readability', 'text' => "Only {$longSentencesPct}% of sentences exceed 20 words."];
        } else {
            $recommendations[] = ['type' => 'warning', 'category' => 'Readability', 'text' => "{$longSentencesPct}% of sentences contain more than 20 words. Break long sentences for punchiness."];
        }

        $seoScore = (int) max(0, min(100, $seoScore));

        return [
            'score' => $seoScore,
            'readability_score' => $fleschScore,
            'reading_grade' => $readingGrade,
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
                'links' => $totalLinks,
                'images' => $totalImages,
                'long_sentences_pct' => $longSentencesPct,
                'keyword' => $kwData,
                'secondary_keywords' => $secKeywordsData,
            ],
            'recommendations' => $recommendations,
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