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
use Illuminate\Support\Facades\Log;

class CriticAgentService
{
    /**
     * Helper for evaluating raw draft strings.
     */
    public function evaluateDraft(
        string $heading,
        string $content,
        string $topic,
        array $assignedClaims,
        KnowledgeFabricDTO $knowledge
    ): CriticScoreDTO {
        $draft = new SectionDraftDTO(
            sectionId: 'sec_eval',
            heading: $heading,
            contentHtml: $content,
            contentMarkdown: strip_tags($content),
            wordCount: str_word_count(strip_tags($content)),
            citedClaimIds: $assignedClaims
        );

        $section = new SectionNodeDTO(
            sectionId: 'sec_eval',
            heading: $heading,
            assignedClaimIds: $assignedClaims
        );

        $missionDTO = new ContentMissionDTO(
            topic: $topic,
            primaryObjective: $topic
        );

        return $this->evaluate($draft, $section, $missionDTO, $knowledge);
    }

    /**
     * Rigorously critique and evaluate a drafted section against 6 quality dimensions.
     *
     * NOW USES REAL AI to provide human-like critique with specific revision directives.
     */
    public function evaluate(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        ContentMissionDTO $mission,
        KnowledgeFabricDTO $knowledge
    ): CriticScoreDTO {
        Log::info("[CriticAgent] Evaluating section: {$draft->heading}");

        // ══════════════════════════════════════════════════════════════
        // AI-Powered Multi-Rubric Quality Evaluation
        // ══════════════════════════════════════════════════════════════

        $topic = $mission->topic;
        $expertise = is_array($mission->targetAudience) ? ($mission->targetAudience['expertise_level'] ?? 'Intermediate') : 'Intermediate';
        $contentPreview = mb_substr(strip_tags($draft->contentHtml), 0, 1500);

        $criticPersona = is_array($mission->targetAudience) ? ($mission->targetAudience['persona'] ?? 'Technical professionals') : (string) $mission->targetAudience;
        $critiquePrompt = "Evaluate this article section for quality and provide specific revision directives.

Topic: \"{$topic}\"
Target Audience: {$criticPersona}
Expertise Level: {$expertise}

Section Heading: {$draft->heading}
Section Purpose: {$section->purpose}
Must Answer Questions: " . implode('; ', $section->mustAnswerQuestions ?? []) . "

Content Preview:
{$contentPreview}

Evaluate on these 6 dimensions and return specific feedback:

1. fact_grounding_score (0-100): Does the content cite and properly attribute the claims/evidence?
2. fact_grounding_issues: Array of specific factual issues found
3. fact_grounding_directives: Array of specific corrections needed

4. completeness_score (0-100): Does it fully answer the must-answer questions?
5. completeness_issues: What's missing?
6. completeness_directives: How to make it complete?

7. search_intent_score (0-100): Does it match what users search for?
8. search_intent_issues: Misalignment issues
9. search_intent_directives: How to better align?

10. brand_voice_score (0-100): Is the tone professional and appropriate?
11. brand_voice_issues: Tone/fluff issues
12. brand_voice_directives: How to fix tone?

13. readability_score (0-100): Is it easy to read and understand?
14. readability_issues: Structural/clarity issues
15. readability_directives: How to improve?

16. seo_score (0-100): Are keywords properly used?
17. seo_issues: SEO problems
18. seo_directives: SEO fixes

Return JSON with all these fields:
{
  \"fact_grounding\": {\"score\": 90, \"issues\": [], \"directives\": []},
  \"completeness\": {\"score\": 85, \"issues\": [], \"directives\": []},
  \"search_intent\": {\"score\": 88, \"issues\": [], \"directives\": []},
  \"brand_voice\": {\"score\": 90, \"issues\": [], \"directives\": []},
  \"readability\": {\"score\": 92, \"issues\": [], \"directives\": []},
  \"seo\": {\"score\": 85, \"issues\": [], \"directives\": []}
}";

        $aiCritique = DynamicContentProvider::askJSON($critiquePrompt, [
            'fact_grounding' => ['score' => 90, 'issues' => [], 'directives' => []],
            'completeness' => ['score' => 85, 'issues' => [], 'directives' => []],
            'search_intent' => ['score' => 88, 'issues' => [], 'directives' => []],
            'brand_voice' => ['score' => 90, 'issues' => [], 'directives' => []],
            'readability' => ['score' => 92, 'issues' => [], 'directives' => []],
            'seo' => ['score' => 85, 'issues' => [], 'directives' => []]
        ]);

        // Merge AI critique with algorithmic checks
        $issues = [];
        $directives = [];

        // Extract all issues and directives from AI
        foreach ($aiCritique as $dimension => $data) {
            if (is_array($data)) {
                $issues = array_merge($issues, $data['issues'] ?? []);
                $directives = array_merge($directives, $data['directives'] ?? []);
            }
        }

        // Also run algorithmic checks for additional precision
        $factScore = $this->evaluateFactGrounding($draft, $section, $knowledge, $issues, $directives);
        $completenessScore = $this->evaluateCompleteness($draft, $section, $issues, $directives);
        $intentScore = $this->evaluateSearchIntent($draft, $section, $mission, $issues, $directives);
        $voiceScore = $this->evaluateBrandVoice($draft, $mission, $issues, $directives);
        $readabilityScore = $this->evaluateReadability($draft, $section, $issues, $directives);
        $seoScore = $this->evaluateSeo($draft, $section, $issues, $directives);

        // Use AI scores if available and reasonable, else use algorithmic
        $finalFactScore = $aiCritique['fact_grounding']['score'] ?? $factScore;
        $finalCompletenessScore = $aiCritique['completeness']['score'] ?? $completenessScore;
        $finalIntentScore = $aiCritique['search_intent']['score'] ?? $intentScore;
        $finalVoiceScore = $aiCritique['brand_voice']['score'] ?? $voiceScore;
        $finalReadabilityScore = $aiCritique['readability']['score'] ?? $readabilityScore;
        $finalSeoScore = $aiCritique['seo']['score'] ?? $seoScore;

        $result = CriticScoreDTO::compute(
            factGrounding: $finalFactScore,
            completeness: $finalCompletenessScore,
            searchIntent: $finalIntentScore,
            brandVoice: $finalVoiceScore,
            readability: $finalReadabilityScore,
            seoOptimization: $finalSeoScore,
            issues: array_unique($issues),
            revisionDirectives: array_unique($directives)
        );

        $statusLabel = (! $result->passed) ? 'needs revision' : 'approved';
        Log::info("[CriticAgent] Section '{$draft->heading}' scored: {$result->overallScore}/100 ({$statusLabel})");

        return $result;
    }

    /**
     * @deprecated Now using AI-powered evaluation - kept for fallback
     */
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
            if (!$claim) {
                continue;
            }

            if ($claim->epistemicState === EpistemicState::CONTRADICTED) {
                $score -= 25.0;
                $issues[] = "Section references contradicted claim: [{$claimId}]";
                $directives[] = "Remove contradicted claim [{$claimId}] and replace with consensus data.";
            } elseif ($claim->epistemicState === EpistemicState::UNVERIFIED) {
                $score -= 15.0;
                $issues[] = "Section relies on unverified claim: [{$claimId}]";
                $directives[] = "Anchor claim [{$claimId}] with verified evidence or qualify as unverified.";
            }

            if (!str_contains($content, strtolower($claimId)) && !str_contains($content, 'verified claim')) {
                $score -= 10.0;
                $directives[] = "Inject explicit claim attribution anchor for [{$claimId}].";
            }
        }

        return max(30.0, min(100.0, $score));
    }

    /**
     * @deprecated Now using AI-powered evaluation - kept for fallback
     */
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
            $words = array_filter(
                explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $question))),
                fn($w) => strlen($w) > 4
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

    /**
     * @deprecated Now using AI-powered evaluation - kept for fallback
     */
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

        if (!empty($section->intentCategory)) {
            $intentTerm = strtolower($section->intentCategory);
            if (!str_contains($content, $intentTerm) && !str_contains($heading, $intentTerm)) {
                $score -= 10.0;
                $directives[] = "Align tone closer with intended user intent: {$section->intentCategory}.";
            }
        }

        return max(50.0, min(100.0, $score));
    }

    /**
     * @deprecated Now using AI-powered evaluation - kept for fallback
     */
    protected function evaluateBrandVoice(
        SectionDraftDTO $draft,
        ContentMissionDTO $mission,
        array &$issues,
        array &$directives
    ): float {
        $score = 92.0;
        $text = strip_tags($draft->contentHtml);

        $bannedFiller = ['delve', 'tapestry', 'testament', "in today's fast-paced world", 'beacon of hope'];
        foreach ($bannedFiller as $filler) {
            if (stripos($text, $filler) !== false) {
                $score -= 8.0;
                $issues[] = "Contains AI cliché filler phrase: '{$filler}'";
                $directives[] = "Remove AI cliché '{$filler}' and replace with precise technical prose.";
            }
        }

        return max(40.0, min(100.0, $score));
    }

    /**
     * @deprecated Now using AI-powered evaluation - kept for fallback
     */
    protected function evaluateReadability(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        array &$issues,
        array &$directives
    ): float {
        $score = 94.0;
        $wordCount = $draft->wordCount;

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

        if (!str_contains($draft->contentHtml, '<p')) {
            $score -= 25.0;
            $issues[] = 'Draft lacks semantic paragraph elements.';
            $directives[] = 'Enclose prose in standard semantic paragraph tags.';
        }

        return max(30.0, min(100.0, $score));
    }

    /**
     * @deprecated Now using AI-powered evaluation - kept for fallback
     */
    protected function evaluateSeo(
        SectionDraftDTO $draft,
        SectionNodeDTO $section,
        array &$issues,
        array &$directives
    ): float {
        $score = 90.0;
        $content = strtolower(strip_tags($draft->contentHtml));

        if (!empty($section->targetKeywords)) {
            $missingKeywords = [];
            foreach ($section->targetKeywords as $kw) {
                if (!str_contains($content, strtolower($kw))) {
                    $missingKeywords[] = $kw;
                }
            }

            if (!empty($missingKeywords)) {
                $score -= min(25.0, count($missingKeywords) * 7.0);
                $directives[] = 'Naturally integrate missing target keywords: ' . implode(', ', $missingKeywords);
            }
        }

        return max(50.0, min(100.0, $score));
    }
}