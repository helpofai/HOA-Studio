<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Document Text Analyzer
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Documents\Services;

class DocumentTextAnalyzer
{
    /**
     * Common English stop words to filter out during keyword extraction
     */
    protected const STOP_WORDS = [
        'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'aren\'t', 'as',
        'at', 'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can\'t', 'cannot',
        'could', 'couldn\'t', 'did', 'didn\'t', 'do', 'does', 'doesn\'t', 'doing', 'don\'t', 'down', 'during', 'each',
        'few', 'for', 'from', 'further', 'had', 'hadn\'t', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d',
        'he\'ll', 'he\'s', 'her', 'here', 'here\'s', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'how\'s', 'i',
        'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 'if', 'in', 'into', 'is', 'isn\'t', 'it', 'it\'s', 'its', 'itself', 'let\'s',
        'me', 'more', 'most', 'mustn\'t', 'my', 'myself', 'no', 'nor', 'not', 'of', 'off', 'on', 'once', 'only', 'or',
        'other', 'ought', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same', 'shan\'t', 'she', 'she\'d', 'she\'ll',
        'she\'s', 'should', 'shouldn\'t', 'so', 'some', 'such', 'than', 'that', 'that\'s', 'the', 'their', 'theirs',
        'them', 'themselves', 'then', 'there', 'there\'s', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve',
        'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'wasn\'t', 'we', 'we\'d', 'we\'ll',
        'we\'re', 'we\'ve', 'were', 'weren\'t', 'what', 'what\'s', 'when', 'when\'s', 'where', 'where\'s', 'which', 'while',
        'who', 'who\'s', 'whom', 'why', 'why\'s', 'with', 'won\'t', 'would', 'wouldn\'t', 'you', 'you\'d', 'you\'ll',
        'you\'re', 'you\'ve', 'your', 'yours', 'yourself', 'yourselves', 'will', 'also', 'can', 'just', 'like', 'one',
    ];

    /**
     * Analyze extracted text and HTML to produce a comprehensive intelligence report
     */
    public function analyze(string $plainText, string $htmlContent = ''): array
    {
        $plainText = trim($plainText);
        $words = preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        // Character Counts
        $charCount = mb_strlen($plainText);
        $charCountNoSpaces = mb_strlen(preg_replace('/\s+/u', '', $plainText));

        // Sentences & Paragraphs
        $sentences = preg_split('/(?<=[.?!])\s+(?=[A-Z0-9])/u', $plainText, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = max(1, count($sentences));

        $paragraphs = array_filter(array_map('trim', explode("\n\n", $plainText)));
        $paragraphCount = max(1, count($paragraphs));

        // Structural Counts in HTML
        $headingCount = 0;
        $tableCount = 0;
        $listCount = 0;
        if (! empty($htmlContent)) {
            $headingCount = preg_match_all('/<h[1-6]\b[^>]*>/i', $htmlContent);
            $tableCount = preg_match_all('/<table\b[^>]*>/i', $htmlContent);
            $listCount = preg_match_all('/<(ul|ol)\b[^>]*>/i', $htmlContent);
        }

        // Averages
        $avgWordsPerSentence = $wordCount > 0 ? round($wordCount / $sentenceCount, 1) : 0;
        $totalSyllables = 0;
        $totalWordChars = 0;

        foreach ($words as $word) {
            $cleanWord = preg_replace('/[^\p{L}]/u', '', mb_strtolower($word));
            if (empty($cleanWord)) {
                continue;
            }
            $totalWordChars += mb_strlen($cleanWord);
            $totalSyllables += $this->countSyllables($cleanWord);
        }

        $avgWordLength = $wordCount > 0 ? round($totalWordChars / $wordCount, 1) : 0;
        $avgSyllablesPerWord = $wordCount > 0 ? round($totalSyllables / $wordCount, 2) : 1;

        // Flesch Reading Ease
        $fleschScore = 0;
        if ($wordCount > 0 && $sentenceCount > 0) {
            $fleschScore = round(206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord), 1);
            $fleschScore = max(0, min(100, $fleschScore));
        }

        $readabilityData = $this->interpretFleschScore($fleschScore);

        // Tone & Style
        $toneData = $this->assessTone($plainText, $avgWordsPerSentence, $avgWordLength);

        // Keywords & Top Entities
        $keywords = $this->extractKeywords($words, 10);

        // Executive Extractive Summary
        $summary = $this->generateSummary($sentences, $keywords, 2);

        return [
            'metrics' => [
                'word_count' => $wordCount,
                'character_count' => $charCount,
                'character_count_no_spaces' => $charCountNoSpaces,
                'sentence_count' => $sentenceCount,
                'paragraph_count' => $paragraphCount,
                'heading_count' => $headingCount,
                'table_count' => $tableCount,
                'list_count' => $listCount,
                'reading_time_minutes' => max(1, (int) ceil($wordCount / 200)),
                'speaking_time_minutes' => max(1, (int) ceil($wordCount / 130)),
                'avg_words_per_sentence' => $avgWordsPerSentence,
                'avg_word_length' => $avgWordLength,
            ],
            'readability' => array_merge([
                'score' => $fleschScore,
                'flesch_reading_ease' => $fleschScore,
                'flesch_kincaid_grade' => $readabilityData['grade'] ?? 'Standard',
                'reading_level_label' => $readabilityData['level'] ?? 'Standard',
            ], $readabilityData),
            'tone' => $toneData,
            'keywords' => $keywords,
            'summary' => $summary,
        ];
    }

    /**
     * Approximate syllable count for a word
     */
    protected function countSyllables(string $word): int
    {
        $word = mb_strtolower(trim($word));
        if (mb_strlen($word) <= 3) {
            return 1;
        }

        $word = preg_replace('/(?:[^laeiouy]|ed|es|e)$/i', '', $word);
        $word = preg_replace('/^y/i', '', $word);
        preg_match_all('/[aeiouy]{1,2}/i', $word, $matches);

        return max(1, count($matches[0]));
    }

    /**
     * Interpret Flesch Reading Ease score into human-friendly metrics
     */
    protected function interpretFleschScore(float $score): array
    {
        if ($score >= 90) {
            return [
                'level' => 'Very Easy',
                'grade' => '5th Grade (Elementary)',
                'description' => 'Extremely simple to read. Conversational and widely accessible.',
                'badge_color' => 'emerald',
            ];
        } elseif ($score >= 80) {
            return [
                'level' => 'Easy',
                'grade' => '6th Grade',
                'description' => 'Conversational English. High readability for broad audiences.',
                'badge_color' => 'emerald',
            ];
        } elseif ($score >= 70) {
            return [
                'level' => 'Fairly Easy',
                'grade' => '7th Grade',
                'description' => 'Clear, straightforward prose. Ideal for web articles and blogs.',
                'badge_color' => 'cyan',
            ];
        } elseif ($score >= 60) {
            return [
                'level' => 'Standard / Plain English',
                'grade' => '8th - 9th Grade',
                'description' => 'Average readability. Accessible to typical high school readers.',
                'badge_color' => 'indigo',
            ];
        } elseif ($score >= 50) {
            return [
                'level' => 'Fairly Difficult',
                'grade' => '10th - 12th Grade',
                'description' => 'Somewhat complex. Well-suited for business and technical readers.',
                'badge_color' => 'amber',
            ];
        } elseif ($score >= 30) {
            return [
                'level' => 'Difficult',
                'grade' => 'College Level',
                'description' => 'Dense vocabulary and complex sentence structures.',
                'badge_color' => 'rose',
            ];
        } else {
            return [
                'level' => 'Academic / Advanced',
                'grade' => 'Postgraduate Level',
                'description' => 'Specialized technical or legal prose requiring domain expertise.',
                'badge_color' => 'purple',
            ];
        }
    }

    /**
     * Assess stylistic tone based on vocabulary, complexity, and structural cues
     */
    protected function assessTone(string $text, float $avgSentenceLen, float $avgWordLen): array
    {
        $lower = mb_strtolower($text);

        // Cue detectors
        $techKeywords = ['architecture', 'api', 'model', 'server', 'database', 'system', 'code', 'function', 'class', 'method', 'config', 'schema'];
        $bizKeywords = ['revenue', 'growth', 'market', 'strategy', 'customer', 'enterprise', 'compliance', 'workflow', 'team', 'management'];
        $academicKeywords = ['methodology', 'hypothesis', 'empirical', 'furthermore', 'consequently', 'paradigm', 'analysis', 'synthesis'];
        $convKeywords = ['you', 'we', 'imagine', 'let\'s', 'here\'s', 'really', 'great', 'awesome', 'simple'];

        $techScore = 0;
        foreach ($techKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $techScore++;
            }
        }

        $bizScore = 0;
        foreach ($bizKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $bizScore++;
            }
        }

        $academicScore = 0;
        foreach ($academicKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $academicScore++;
            }
        }

        $convScore = 0;
        foreach ($convKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $convScore++;
            }
        }

        $totalScores = max(1, $techScore + $academicScore + $bizScore + $convScore);
        $tonesMap = [
            'Technical' => min(98, max(10, (int) round(($techScore / $totalScores) * 100))),
            'Academic' => min(98, max(10, (int) round(($academicScore / $totalScores) * 100))),
            'Professional' => min(98, max(10, (int) round(($bizScore / $totalScores) * 100))),
            'Conversational' => min(98, max(10, (int) round(($convScore / $totalScores) * 100))),
        ];

        if ($techScore >= 4 || ($techScore >= 2 && $avgWordLen > 5.2)) {
            return [
                'primary' => 'Technical & Architectural',
                'primary_tone' => 'Technical & Architectural',
                'badge' => '⚡ Technical',
                'description' => 'Contains structured technical terminology, systems concepts, and precise domain vocabulary.',
                'confidence' => 92,
                'primary_confidence' => 92,
                'tones' => $tonesMap,
            ];
        } elseif ($academicScore >= 3 || ($avgSentenceLen > 22 && $avgWordLen > 5.5)) {
            return [
                'primary' => 'Academic & Analytical',
                'primary_tone' => 'Academic & Analytical',
                'badge' => '🎓 Academic',
                'description' => 'Characterized by complex sentence constructs, formal reasoning, and empirical terminology.',
                'confidence' => 88,
                'primary_confidence' => 88,
                'tones' => $tonesMap,
            ];
        } elseif ($bizScore >= 3) {
            return [
                'primary' => 'Professional & Corporate',
                'primary_tone' => 'Professional & Corporate',
                'badge' => '💼 Professional',
                'description' => 'Clear, polished executive communication suited for business stakeholders and operations.',
                'confidence' => 90,
                'primary_confidence' => 90,
                'tones' => $tonesMap,
            ];
        } elseif ($convScore >= 4 || $avgSentenceLen < 14) {
            return [
                'primary' => 'Conversational & Engaging',
                'primary_tone' => 'Conversational & Engaging',
                'badge' => '💬 Conversational',
                'description' => 'Direct, approachable, and engaging dialogue with reader-centric phrasing.',
                'confidence' => 85,
                'primary_confidence' => 85,
                'tones' => $tonesMap,
            ];
        }

        return [
            'primary' => 'Informative & Balanced',
            'primary_tone' => 'Informative & Balanced',
            'badge' => '📰 Informative',
            'description' => 'Neutral, balanced narrative delivering clear explanatory insights without excessive jargon.',
            'confidence' => 86,
            'primary_confidence' => 86,
            'tones' => $tonesMap,
        ];
    }

    /**
     * Extract top keywords and frequencies
     */
    protected function extractKeywords(array $words, int $limit = 10): array
    {
        $frequencies = [];
        $stopWords = array_flip(self::STOP_WORDS);

        foreach ($words as $rawWord) {
            $word = mb_strtolower(preg_replace('/[^\p{L}0-9_-]/u', '', $rawWord));
            if (mb_strlen($word) < 3) {
                continue;
            }
            if (isset($stopWords[$word])) {
                continue;
            }
            if (is_numeric($word)) {
                continue;
            }

            $frequencies[$word] = ($frequencies[$word] ?? 0) + 1;
        }

        arsort($frequencies);

        $totalFiltered = array_sum($frequencies);
        $top = [];
        $count = 0;

        foreach ($frequencies as $term => $freq) {
            if ($count++ >= $limit) {
                break;
            }
            $percentage = $totalFiltered > 0 ? round(($freq / $totalFiltered) * 100, 1) : 0;
            $top[] = [
                'term' => $term,
                'word' => $term,
                'count' => $freq,
                'percentage' => $percentage,
            ];
        }

        return $top;
    }

    /**
     * Generate an extractive summary from the most informative sentences
     */
    protected function generateSummary(array $sentences, array $keywords, int $maxSentences = 2): string
    {
        if (empty($sentences)) {
            return 'No content available for summary.';
        }

        if (count($sentences) <= $maxSentences) {
            return implode(' ', $sentences);
        }

        $keywordList = array_column($keywords, 'term');
        $scores = [];

        foreach ($sentences as $idx => $sentence) {
            $trimmed = trim($sentence);
            $words = preg_split('/\s+/u', mb_strtolower($trimmed), -1, PREG_SPLIT_NO_EMPTY);
            $wordCount = count($words);

            if ($wordCount < 6 || $wordCount > 45) {
                $scores[$idx] = 0;

                continue;
            }

            // Keyword overlap score
            $matchCount = 0;
            foreach ($words as $w) {
                if (in_array($w, $keywordList)) {
                    $matchCount++;
                }
            }

            // Position score: intro and conclusion sentences carry more weight
            $positionWeight = ($idx === 0) ? 1.5 : (($idx === count($sentences) - 1) ? 1.2 : 1.0);
            $scores[$idx] = ($matchCount / max(1, $wordCount)) * $positionWeight;
        }

        arsort($scores);
        $topIndices = array_slice(array_keys($scores), 0, $maxSentences);
        sort($topIndices); // Maintain original narrative order

        $chosen = [];
        foreach ($topIndices as $i) {
            $chosen[] = trim($sentences[$i]);
        }

        return implode(' ', $chosen);
    }
}
