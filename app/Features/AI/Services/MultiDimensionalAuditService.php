<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - MultiDimensionalAuditService
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

namespace App\Features\AI\Services;

use App\Features\AI\Data\ContentState;
use App\Features\SEO\Services\SeoAnalyzer;
use Illuminate\Support\Str;

/**
 * Multi-Dimensional Knowledge & Quality Audit Service.
 * Evaluates document content across 6 core quality dimensions:
 * 1. Truth & Factual Grounding
 * 2. Audience & Simple-Language Readability
 * 3. Search Intent & Contract Fulfilling
 * 4. Purpose-Driven Keyword & Entity Placement
 * 5. E-E-A-T & Rich Formatting
 * 6. Overall Multi-Dimensional Quality Gate Score & Blockers
 */
class MultiDimensionalAuditService
{
    public function __construct(
        protected SeoAnalyzer $seoAnalyzer
    ) {}

    /**
     * Audit HTML document content against multi-dimensional quality criteria.
     */
    public function audit(string $htmlContent, string $title = '', ?string $targetKeyword = null, ?ContentState $state = null): array
    {
        // Base algorithmic SEO analysis
        $baseSeo = $this->seoAnalyzer->analyze(
            htmlContent: $htmlContent,
            title: $title,
            targetKeyword: $targetKeyword
        );

        $plainText = strip_tags($htmlContent);
        $wordCount = str_word_count($plainText);

        // 1. Truth & Factual Grounding Audit
        $truthAudit = $this->auditTruthAndGrounding($htmlContent, $state);

        // 2. Audience & Simple-Language Readability Audit
        $simplicityAudit = $this->auditSimpleLanguage($htmlContent, $baseSeo);

        // 3. Search Intent & Contract Audit
        $intentAudit = $this->auditSearchIntent($htmlContent, $targetKeyword, $state);

        // 4. Purpose-Driven Keyword & Entity Placement Audit
        $keywordAudit = $this->auditKeywordPlacement($htmlContent, $title, $targetKeyword);

        // 5. E-E-A-T & Rich Formatting Audit
        $eeatAudit = $this->auditEeatAndFormatting($htmlContent, $baseSeo);

        // 6. Multi-Dimensional Quality Gate Score & Critical Failure Blockers
        $qualityGate = $this->computeQualityGateScore(
            truthScore: $truthAudit['score'],
            simplicityScore: $simplicityAudit['score'],
            intentScore: $intentAudit['score'],
            keywordScore: $keywordAudit['score'],
            eeatScore: $eeatAudit['score'],
            wordCount: $wordCount,
            truthAudit: $truthAudit
        );

        return [
            'overall_score' => $qualityGate['overall_score'],
            'grade' => $qualityGate['grade'],
            'status' => $qualityGate['status'], // PASS or FAIL
            'critical_blockers' => $qualityGate['critical_blockers'],
            'dimensions' => [
                'truth_grounding' => $truthAudit,
                'simple_language' => $simplicityAudit,
                'search_intent' => $intentAudit,
                'keyword_placement' => $keywordAudit,
                'eeat_formatting' => $eeatAudit,
            ],
            'seo_base_metrics' => [
                'word_count' => $wordCount,
                'flesch_score' => $baseSeo['readability']['flesch_reading_ease'] ?? 60,
                'reading_grade' => $baseSeo['readability']['reading_grade'] ?? 'Standard (8th-9th grade)',
                'keyword_density' => $baseSeo['keyword_analysis']['density_pct'] ?? 0,
            ],
            'recommendations' => $qualityGate['recommendations'],
        ];
    }

    /**
     * Audit 1: Truth & Factual Grounding
     */
    protected function auditTruthAndGrounding(string $htmlContent, ?ContentState $state = null): array
    {
        $score = 90;
        $riskRating = 'low';
        $issues = [];

        if ($state !== null && ! empty($state->knowledgeGraph->claims)) {
            $totalClaims = count($state->knowledgeGraph->claims);
            $approvedCount = 0;

            foreach ($state->knowledgeGraph->claims as $claim) {
                if (($claim['verification_status'] ?? '') === 'approved') {
                    $approvedCount++;
                }
            }

            $claimRatio = $totalClaims > 0 ? ($approvedCount / $totalClaims) : 1.0;
            $score = (int) round($claimRatio * 100);

            if ($score < 60) {
                $riskRating = 'high';
                $issues[] = 'Low evidence grounding: less than 60% of factual claims are verified in KnowledgeGraph.';
            }
        }

        return [
            'score' => max(0, min(100, $score)),
            'risk_rating' => $riskRating,
            'grounding_status' => $score >= 80 ? 'Authoritatively Grounded' : 'Requires Evidence Verification',
            'issues' => $issues,
        ];
    }

    /**
     * Audit 2: Audience & Simple-Language Readability
     */
    protected function auditSimpleLanguage(string $htmlContent, array $baseSeo): array
    {
        $flesch = $baseSeo['readability']['flesch_reading_ease'] ?? 60;
        $score = max(0, min(100, (int) round($flesch)));
        $issues = [];

        // Check for overly long paragraphs (> 120 words)
        preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $htmlContent, $matches);
        $longParagraphs = 0;
        foreach ($matches[1] as $pText) {
            if (str_word_count(strip_tags($pText)) > 120) {
                $longParagraphs++;
            }
        }

        if ($longParagraphs > 0) {
            $score = max(0, $score - ($longParagraphs * 5));
            $issues[] = "{$longParagraphs} paragraphs exceed 120 words. Break them into smaller sections for simple reading.";
        }

        return [
            'score' => $score,
            'flesch_reading_ease' => $flesch,
            'reading_grade' => $baseSeo['readability']['reading_grade'] ?? 'Standard (8th-9th grade)',
            'long_paragraphs_count' => $longParagraphs,
            'issues' => $issues,
        ];
    }

    /**
     * Audit 3: Search Intent & Contract Compliance
     */
    protected function auditSearchIntent(string $htmlContent, ?string $targetKeyword, ?ContentState $state = null): array
    {
        $score = 80;
        $issues = [];

        // Check for Quick Summary / Answer Callout Box
        $hasQuickAnswer = (bool) preg_match('/class=["\'][^"\']*quick-answer|bg-violet|callout/i', $htmlContent) || Str::contains($htmlContent, '💡');
        if ($hasQuickAnswer) {
            $score += 10;
        } else {
            $issues[] = 'Missing Quick Answer Callout snippet box for instant search intent satisfaction.';
        }

        // Check for FAQ section
        $hasFaq = (bool) preg_match('/<h[2-3][^>]*>[^<]*FAQ|Frequently Asked Questions/i', $htmlContent);
        if ($hasFaq) {
            $score += 10;
        }

        return [
            'score' => min(100, $score),
            'has_quick_answer_callout' => $hasQuickAnswer,
            'has_faq_section' => $hasFaq,
            'issues' => $issues,
        ];
    }

    /**
     * Audit 4: Purpose-Driven Keyword Placement
     */
    protected function auditKeywordPlacement(string $htmlContent, string $title, ?string $targetKeyword): array
    {
        $score = 75;
        $issues = [];

        if (! empty($targetKeyword)) {
            $kw = strtolower($targetKeyword);
            $lowerHtml = strtolower($htmlContent);
            $lowerTitle = strtolower($title);

            // In Title/H1
            if (Str::contains($lowerTitle, $kw)) {
                $score += 10;
            } else {
                $issues[] = "Target keyword '{$targetKeyword}' is missing from the main Title/H1.";
            }

            // In H2 headings
            preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $htmlContent, $h2Matches);
            $h2Text = strtolower(implode(' ', $h2Matches[1] ?? []));
            if (Str::contains($h2Text, $kw)) {
                $score += 10;
            }

            // Keyword Density Guard (Should not exceed 3%)
            $totalWords = max(1, str_word_count(strip_tags($htmlContent)));
            $kwCount = substr_count($lowerHtml, $kw);
            $density = round(($kwCount / $totalWords) * 100, 2);

            if ($density > 3.5) {
                $score -= 20;
                $issues[] = "Unnatural keyword stuffing detected ({$density}% density). Maintain natural usage under 3%.";
            }
        }

        return [
            'score' => max(0, min(100, $score)),
            'issues' => $issues,
        ];
    }

    /**
     * Audit 5: E-E-A-T & Rich Formatting
     */
    protected function auditEeatAndFormatting(string $htmlContent, array $baseSeo): array
    {
        $score = 70;
        $issues = [];

        // Tables / Comparison matrices
        $hasTables = Str::contains($htmlContent, '<table');
        if ($hasTables) {
            $score += 15;
        }

        // Code / Pre blocks
        $hasCode = Str::contains($htmlContent, '<pre') || Str::contains($htmlContent, '<code');
        if ($hasCode) {
            $score += 10;
        }

        // Links
        $linksCount = count($baseSeo['links']['all'] ?? []);
        if ($linksCount >= 2) {
            $score += 5;
        }

        return [
            'score' => min(100, $score),
            'has_tables' => $hasTables,
            'has_code_blocks' => $hasCode,
            'links_count' => $linksCount,
            'issues' => $issues,
        ];
    }

    /**
     * Audit 6: Compute Quality Gate Score & Blockers
     */
    protected function computeQualityGateScore(
        int $truthScore,
        int $simplicityScore,
        int $intentScore,
        int $keywordScore,
        int $eeatScore,
        int $wordCount,
        array $truthAudit
    ): array {
        // Weighted Quality Score
        $overallScore = (int) round(
            ($truthScore * 0.25) +
            ($simplicityScore * 0.20) +
            ($intentScore * 0.20) +
            ($keywordScore * 0.15) +
            ($eeatScore * 0.20)
        );

        $grade = match (true) {
            $overallScore >= 95 => 'A+',
            $overallScore >= 88 => 'A',
            $overallScore >= 80 => 'B',
            $overallScore >= 70 => 'C',
            $overallScore >= 60 => 'D',
            default => 'F',
        };

        $blockers = [];
        if ($wordCount < 100) {
            $blockers[] = 'Document is too short (under 100 words).';
        }
        if ($truthAudit['risk_rating'] === 'critical') {
            $blockers[] = 'Critical factual contradiction detected in KnowledgeGraph.';
        }

        $status = empty($blockers) && $overallScore >= 70 ? 'PASS' : 'FAIL';

        $recommendations = [];
        if ($truthScore < 80) {
            $recommendations[] = 'Verify ungrounded claims against authoritative sources or RAG vector memory.';
        }
        if ($simplicityScore < 70) {
            $recommendations[] = 'Simplify long sentences and break heavy paragraphs into concise bullet points.';
        }
        if ($intentScore < 80) {
            $recommendations[] = 'Add a Quick Answer summary callout box at the top of the article.';
        }

        return [
            'overall_score' => $overallScore,
            'grade' => $grade,
            'status' => $status,
            'critical_blockers' => $blockers,
            'recommendations' => $recommendations,
        ];
    }
}
