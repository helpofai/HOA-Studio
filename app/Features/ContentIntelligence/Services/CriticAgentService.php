<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Critic Agent Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\CriticScoreDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\SectionNodeDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;

class CriticAgentService
{
    /**
     * Rigorously critique and evaluate a drafted section against 6 quality dimensions.
     */
    public function evaluate(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        ContentMissionDTO $mission,
        KnowledgeFabricDTO $knowledge
    ): CriticScoreDTO {
        $issues = [];
        $directives = [];

        // 1. Fact Grounding (25% weight)
        $factScore = $this->evaluateFactGrounding($draft, $section, $knowledge, $issues, $directives);

        // 2. Completeness (20% weight)
        $completenessScore = $this->evaluateCompleteness($draft, $section, $issues, $directives);

        // 3. Search Intent Alignment (15% weight)
        $intentScore = $this->evaluateSearchIntent($draft, $section, $mission, $issues, $directives);

        // 4. Brand Voice & Editorial Tone (15% weight)
        $voiceScore = $this->evaluateBrandVoice($draft, $mission, $issues, $directives);

        // 5. Readability & Structural Clarity (15% weight)
        $readabilityScore = $this->evaluateReadability($draft, $section, $issues, $directives);

        // 6. SEO & Entity Optimization (10% weight)
        $seoScore = $this->evaluateSeo($draft, $section, $issues, $directives);

        return CriticScoreDTO::compute(
            factGrounding: $factScore,
            completeness: $completenessScore,
            searchIntent: $intentScore,
            brandVoice: $voiceScore,
            readability: $readabilityScore,
            seoOptimization: $seoScore,
            issues: $issues,
            revisionDirectives: $directives
        );
    }

    protected function evaluateFactGrounding(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        KnowledgeFabricDTO $knowledge,
        array &$issues,
        array &$directives
    ): float {
        $score = 95.0;
        $content = strtolower($draft->contentHtml);

        $claimMap = [];
        foreach ($knowledge->claims as $claim) {
            $claimMap[$claim->claimId] = $claim;
        }

        foreach ($section->assignedClaimIds as $claimId) {
            $claim = $claimMap[$claimId] ?? null;
            if (! $claim) {
                continue;
            }

            // Verify the claim's epistemic status
            if ($claim->epistemicState === EpistemicState::CONTRADICTED) {
                $score -= 25.0;
                $issues[] = "Section references contradicted claim: [{$claimId}]";
                $directives[] = "Remove contradicted claim [{$claimId}] and replace with consensus data.";
            } elseif ($claim->epistemicState === EpistemicState::UNVERIFIED) {
                $score -= 15.0;
                $issues[] = "Section relies on unverified claim: [{$claimId}]";
                $directives[] = "Anchor claim [{$claimId}] with verified evidence or qualify as unverified.";
            }

            // Check if claim ID or key terms are present in prose
            if (! str_contains($content, strtolower($claimId)) && ! str_contains($content, 'verified claim')) {
                $score -= 10.0;
                $directives[] = "Inject explicit claim attribution anchor for [{$claimId}].";
            }
        }

        return max(30.0, min(100.0, $score));
    }

    protected function evaluateCompleteness(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        array &$issues,
        array &$directives
    ): float {
        $score = 92.0;
        $content = strtolower(strip_tags($draft->contentHtml));

        if (empty($section->mustAnswerQuestions)) {
            return 90.0;
        }

        $unansweredCount = 0;
        foreach ($section->mustAnswerQuestions as $question) {
            // Extract key words from question (> 4 chars)
            $words = array_filter(
                explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $question))),
                fn ($w) => strlen($w) > 4
            );

            $matched = 0;
            foreach ($words as $w) {
                if (str_contains($content, $w)) {
                    $matched++;
                }
            }

            if (count($words) > 0 && ($matched / count($words)) < 0.25) {
                $unansweredCount++;
                $directives[] = "Explicitly address required question: '{$question}'.";
            }
        }

        if ($unansweredCount > 0) {
            $penalty = ($unansweredCount / count($section->mustAnswerQuestions)) * 30.0;
            $score -= $penalty;
            if ($unansweredCount > (count($section->mustAnswerQuestions) / 2)) {
                $issues[] = "Section misses answers to {$unansweredCount} mandatory questions.";
            }
        }

        return max(40.0, min(100.0, $score));
    }

    protected function evaluateSearchIntent(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        ContentMissionDTO $mission,
        array &$issues,
        array &$directives
    ): float {
        $score = 90.0;
        $heading = strtolower($draft->heading);
        $content = strtolower(strip_tags($draft->contentHtml));

        if (! empty($section->intentCategory)) {
            $intentTerm = strtolower($section->intentCategory);
            if (! str_contains($content, $intentTerm) && ! str_contains($heading, $intentTerm)) {
                $score -= 10.0;
                $directives[] = "Align tone closer with intended user intent: {$section->intentCategory}.";
            }
        }

        return max(50.0, min(100.0, $score));
    }

    protected function evaluateBrandVoice(
        SectionDraftDTO $draft,
        ContentMissionDTO $mission,
        array &$issues,
        array &$directives
    ): float {
        $score = 92.0;
        $text = strip_tags($draft->contentHtml);

        // Fluff buzzword detector
        $bannedFiller = ['delve', 'tapestry', 'testament', 'in today\'s fast-paced world', 'beacon of hope'];
        foreach ($bannedFiller as $filler) {
            if (stripos($text, $filler) !== false) {
                $score -= 8.0;
                $issues[] = "Contains AI cliché filler phrase: '{$filler}'";
                $directives[] = "Remove AI cliché '{$filler}' and replace with precise technical prose.";
            }
        }

        return max(40.0, min(100.0, $score));
    }

    protected function evaluateReadability(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        array &$issues,
        array &$directives
    ): float {
        $score = 94.0;
        $wordCount = $draft->wordCount;

        // Check if word count deviates heavily from target
        if ($section->targetWordCount > 0) {
            $ratio = $wordCount / $section->targetWordCount;
            if ($ratio < 0.4) {
                $score -= 20.0;
                $issues[] = "Draft word count ({$wordCount}) is significantly below target ({$section->targetWordCount}).";
                $directives[] = "Expand section depth and architectural detail to reach {$section->targetWordCount} words.";
            } elseif ($ratio > 2.5) {
                $score -= 10.0;
                $directives[] = "Condense redundant prose towards target {$section->targetWordCount} words.";
            }
        }

        // Structural check: Ensure at least one <p> element exists
        if (! str_contains($draft->contentHtml, '<p')) {
            $score -= 25.0;
            $issues[] = 'Draft lacks semantic paragraph elements.';
            $directives[] = 'Enclose prose in standard semantic paragraph tags.';
        }

        return max(30.0, min(100.0, $score));
    }

    protected function evaluateSeo(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        array &$issues,
        array &$directives
    ): float {
        $score = 90.0;
        $content = strtolower(strip_tags($draft->contentHtml));

        if (! empty($section->targetKeywords)) {
            $missingKeywords = [];
            foreach ($section->targetKeywords as $kw) {
                if (! str_contains($content, strtolower($kw))) {
                    $missingKeywords[] = $kw;
                }
            }

            if (! empty($missingKeywords)) {
                $score -= min(25.0, count($missingKeywords) * 7.0);
                $directives[] = 'Naturally integrate missing target keywords: '.implode(', ', $missingKeywords);
            }
        }

        return max(50.0, min(100.0, $score));
    }
}
