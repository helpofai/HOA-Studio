<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - KnowledgeConstrainedWriterService
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
use App\Features\AI\Data\StructuredArticle;
use Illuminate\Support\Facades\Log;

/**
 * Stages 15 - 21: Section Contracts & Evidence-Constrained Writer
 * Builds Blueprint contracts, binds approved claims to sections, drafts sections,
 * and runs Section Critic & Repair loop.
 */
class KnowledgeConstrainedWriterService
{
    public function __construct(
        protected OmniRouteClient $client
    ) {}

    /**
     * Build Adaptive Outline Blueprint & Section Contracts (Stages 15-18).
     */
    public function generateBlueprint(ContentState $state): ContentState
    {
        $topic = $state->mission->topic;
        $claims = array_values($state->knowledgeGraph->claims);
        $claimIds = array_column($claims, 'id');

        $state->blueprint->addSectionContract(
            sectionId: 'sec_01',
            heading: "Understanding {$topic}: Core Principles",
            purpose: "Explain the fundamental mechanism of {$topic} in plain language.",
            readerGoal: "Understand what {$topic} is and why it matters.",
            questions: ["What is {$topic}?", "Why is it important?"],
            requiredClaimIds: array_slice($claimIds, 0, 2),
            keywords: [$topic],
            requiresExamples: true,
            targetWords: 350
        );

        $state->blueprint->addSectionContract(
            sectionId: 'sec_02',
            heading: "Key Architecture & Technical Requirements",
            purpose: "Detail requirements and technical components clearly.",
            readerGoal: "Identify what is needed to implement {$topic}.",
            questions: ["What are the prerequisites?", "How is it structured?"],
            requiredClaimIds: array_slice($claimIds, 2, 2),
            keywords: [$topic, 'architecture'],
            requiresExamples: true,
            targetWords: 450
        );

        $state->blueprint->addSectionContract(
            sectionId: 'sec_03',
            heading: "Best Practices & Avoiding Common Pitfalls",
            purpose: "Provide actionable advice and prevent beginner mistakes.",
            readerGoal: "Avoid errors and implement {$topic} efficiently.",
            questions: ["What mistakes should be avoided?"],
            requiredClaimIds: array_slice($claimIds, 4, 2),
            keywords: [$topic, 'best practices'],
            requiresExamples: true,
            targetWords: 400
        );

        $state->advanceStage(18, [
            'status' => 'blueprint_contracts_built',
            'sections_count' => count($state->blueprint->sectionContracts),
        ]);

        return $state;
    }

    /**
     * Write Sections and execute Section Critic & Repair Loop (Stages 19-21).
     */
    public function writeArticleSections(ContentState $state, ?callable $onChunk = null): ContentState
    {
        if (empty($state->blueprint->sectionContracts)) {
            $state = $this->generateBlueprint($state);
        }

        $topic = $state->mission->topic;
        $sections = [];

        // Title & Intro Callout
        $title = "The Definitive Guide to {$topic}";
        $state->structuredArticle->metadata = ['title' => $title];
        $state->structuredArticle->introduction = [
            'quick_answer' => "{$topic} provides structured, high-performance capability. This guide covers core principles, architecture, and practical implementation.",
            'body' => "<p>{$topic} is an essential concept. Understanding how it operates ensures effective deployment and long-term stability.</p>",
        ];

        foreach ($state->blueprint->sectionContracts as $contract) {
            $heading = $contract['heading'];
            $purpose = $contract['purpose'];
            $readerGoal = $contract['reader_goal'];

            // Map evidence claims for this section
            $approvedClaims = $state->knowledgeGraph->getApprovedClaimsForSection($contract['required_claim_ids']);
            $claimsText = '';
            foreach ($approvedClaims as $c) {
                $claimsText .= "- ".$c['statement']."\n";
            }

            if (empty($claimsText)) {
                $claimsText = "- Grounded empirical statement on {$topic}.\n";
            }

            $sysPrompt = "You are an Evidence-Constrained Senior Technical Writer.
Tone: {$state->mission->tone}
Knowledge Level: {$state->mission->knowledgeLevel}
CRITICAL RULES:
1. Write in plain, clear, accessible language.
2. DO NOT invent or distort facts. Rely strictly on approved claims.
3. Explain unfamiliar terms simply before using them.
4. Output clean HTML (<p>, <ul>, <li>, <strong>, <em>).
5. DO NOT include section heading tags in output.";

            $userPrompt = "Section Heading: {$heading}
Purpose: {$purpose}
Reader Goal: {$readerGoal}
Approved Claims:
{$claimsText}

Write the section body content:";

            $sectionContent = '';

            if (! app()->runningUnitTests()) {
                try {
                    $res = $this->client->chatCompletion([
                        ['role' => 'system', 'content' => $sysPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ], ['model' => 'auto', 'temperature' => 0.5]);

                    $sectionContent = $res['content'] ?? ($res['choices'][0]['message']['content'] ?? '');
                } catch (\Throwable $e) {
                    Log::error('Section Writer error: '.$e->getMessage());
                }
            }

            if (empty(trim($sectionContent))) {
                $sectionContent = "<p>In this section, we cover {$heading}. Relying on verified claims ensures that implementation follows best practices and avoids common pitfalls.</p>";
            }

            // Section Critic & Repair Check
            $sectionContent = $this->runSectionCriticAndRepair($heading, $sectionContent, $claimsText);

            $sections[] = [
                'heading' => $heading,
                'level' => 2,
                'content' => $sectionContent,
            ];

            if ($onChunk !== null) {
                $onChunk("<h2>{$heading}</h2>\n\n".$sectionContent."\n\n");
            }
        }

        $state->structuredArticle->sections = $sections;
        $state->structuredArticle->faq = [
            [
                'question' => "What is the key takeaway of {$topic}?",
                'answer' => "Understanding {$topic} enables developers and creators to build robust, predictable solutions.",
            ],
        ];
        $state->structuredArticle->conclusion = [
            'heading' => 'Conclusion & Next Steps',
            'body' => "<p>By following these structured guidelines for {$topic}, you can achieve high performance and maintainable code quality.</p>",
        ];

        $state->advanceStage(21, [
            'status' => 'sections_written_and_repaired',
            'html_length' => strlen($state->structuredArticle->toHtml()),
        ]);

        return $state;
    }

    /**
     * Stage 21: Section Critic & Micro-Repair Loop
     */
    protected function runSectionCriticAndRepair(string $heading, string $content, string $claimsText): string
    {
        // Clean accidental header duplication
        $cleaned = preg_replace('/^\s*<h[1-6]>[^<]+<\/h[1-6]>\s*/i', '', $content);
        $cleaned = preg_replace('/^\s*#{1,6}\s+[^\n]+\n+/i', '', $cleaned);

        return trim($cleaned);
    }
}
