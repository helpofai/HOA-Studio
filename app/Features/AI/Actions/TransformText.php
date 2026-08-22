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

namespace App\Features\AI\Actions;

use App\Features\AI\Services\OmniRouteClient;
use App\Models\User;
use Exception;

class TransformText
{
    public function __construct(
        protected OmniRouteClient $client,
        protected RecordGenerationUsage $recordUsage
    ) {}

    /**
     * Contextual prompt mapping for all text transformations with full TipTap permissions
     */
    public function getSystemPrompt(string $transformationType, ?string $customInstruction = null): string
    {
        $tiptapStyleGuide = <<<EOT
You are an elite editorial writer and enterprise publication architect. You have FULL PERMISSION and authorization to craft rich, beautifully formatted blog posts and documents using the complete TipTap editor architecture:

FORMATTING & ELEMENT PERMISSIONS:
- # H1: Exactly one primary title at the very top.
- ## H2: Main thematic sections and core chapters.
- ### H3: Tactical sub-points, deep-dives, and FAQ questions.
- **Bold**: Prominently emphasize key entities, metrics, critical concepts, and core conclusions.
- *Italics*: For nuanced terms, references, and Latin phrases.
- ~~Strikethrough~~: For debunked myths, before-and-after contrasts, and deprecated practices.
- ==Highlight==: For standout insights and crucial takeaways.
- ● Bullet Lists (- item): For features, benefits, and scannable points.
- 1. Numbered Lists (1. item): For step-by-step procedures, ranking, and sequential recipes.
- ✓ Task Checklists (- [ ] task or - [x] task): For actionable implementation checklists and reader action plans.
- ▦ Comparison Tables (| Header 1 | Header 2 |): Include structured data tables with feature comparisons, pros/cons, and benchmark metrics.
- " Blockquotes (> Quick Answer: ...): For search-intent answers, executive TL;DR callouts, and expert citations.
- </> Code Blocks (```language): For configuration code, formulas, syntax, and CLI commands.
- — Horizontal Rules (---): Between major thematic transitions.
- 🖼 Images (![Descriptive Alt Text](url "Caption")): Conceptual image figure blocks with descriptive alt tags for visual pacing.

WHITESPACE & EDITORIAL DISCIPLINE:
- Strictly NO extra empty whitespace or redundant blank lines.
- Strictly NO conversational meta-commentary (e.g. "Here is your article", "Sure, I can help with that").
- Output pure, high-density, authoritative content immediately ready for publication.
EOT;

        return match ($transformationType) {
            // Rewrite & Polish
            'polish' => $tiptapStyleGuide . "\n\nTask: Polish and enhance the provided text for flow, grammar, vocabulary, and elegance while strictly preserving the author's original meaning. Output ONLY the polished text.",
            'rewrite' => $tiptapStyleGuide . "\n\nTask: Rephrase and rewrite the provided text with alternative phrasing and improved structure while keeping the exact core message. Output ONLY the rewritten text.",
            'fix_grammar' => 'You are a meticulous proofreader. Fix all spelling, punctuation, capitalization, and grammatical errors in the provided text. Do not alter the author\'s voice or style. Output ONLY the corrected text.',

            // Length & Flow
            'shorten' => 'You are a concise editor. Make the provided text significantly more direct, compact, and punchy. Eliminate all fluff, filler, and redundancy. Output ONLY the shortened text.',
            'expand' => $tiptapStyleGuide . "\n\nTask: Expand the provided text with relevant details, clear explanations, logical depth, examples, and compelling context without fluff. Output ONLY the expanded content.",
            'continue' => $tiptapStyleGuide . "\n\nTask: Seamlessly continue writing the text from where it stops, producing the next 2-3 logical, high-quality sections in the exact same voice and style. Output ONLY the continuation.",
            'tldr' => 'Generate 2 to 3 concise, high-impact bullet points summarizing the core essence of the provided text. Output ONLY the bullet points.',

            // Tones
            'tone:professional', 'professional' => $tiptapStyleGuide . "\n\nTask: Rewrite the following text in an authoritative, professional, and corporate-ready executive tone. Output ONLY the rewritten text.",
            'tone:casual', 'casual' => $tiptapStyleGuide . "\n\nTask: Rewrite the following text in a warm, conversational, and relatable tone. Output ONLY the rewritten text.",
            'tone:persuasive', 'persuasive' => $tiptapStyleGuide . "\n\nTask: Rewrite the following text with compelling copywriting principles, strong action verbs, and persuasive framing designed to inspire action. Output ONLY the rewritten text.",
            'tone:friendly', 'friendly' => $tiptapStyleGuide . "\n\nTask: Rewrite the following text in a kind, welcoming, empathetic, and friendly tone. Output ONLY the rewritten text.",
            'tone:academic', 'academic' => $tiptapStyleGuide . "\n\nTask: Rewrite the following text in a scholarly, formal, and rigorously analytical academic tone. Output ONLY the rewritten text.",
            'tone:direct', 'direct' => $tiptapStyleGuide . "\n\nTask: Rewrite the following text in a direct, active-voice, no-nonsense manner. Output ONLY the rewritten text.",

            // Summarize & Synthesis
            'summarize' => $tiptapStyleGuide . "\n\nTask: Provide a clear, comprehensive, and well-structured summary of the provided text, capturing all primary conclusions and nuances.",
            'action_items' => "Extract a structured checklist of concrete action items and next steps from the text. Format as a clean task checklist with - [ ] task items.",
            'simplify' => 'Simplify the text so it is effortlessly readable at an 8th-grade level, replacing complex jargon with plain words. Output ONLY the simplified text.',
            'seo_optimize' => $tiptapStyleGuide . "\n\nTask: Optimize the following text for search engines, improving keyword clarity, header hierarchy (H2/H3), tables, bold terms, and readability.",

            // God-Tier SEO & Blog Creation Pipeline
            'generate_outline' => "You are an elite SEO content architect. Create a logical, comprehensive, and search-intent optimized article outline featuring one primary H1, multiple descriptive H2 sections with nested H3 subsections, an FAQ section, and a decision-oriented conclusion. Output clean markdown outline with #, ##, ###, and - bullet items.",
            'generate_faq' => "Generate 4-5 high-value, genuine search questions and clear, authoritative answers. Format cleanly as:\n### Question text here\nAnswer paragraph with bold key terms.",
            'quick_answer' => "Generate a concise, high-value search-intent quick answer callout box.\nFormat cleanly as: > **Quick Answer:** [Clear 2-3 sentence answer satisfying user intent].",
            'content_gaps' => 'Identify 3-5 critical missing subtopics, unanswered questions, or competitive angles required to make this the definitive resource on the web. Output as clear bullet points with explanations.',
            'key_takeaways' => "Extract 4-5 actionable, high-impact key takeaways. Format as a clean bulleted list with bold leading concepts.",
            'eeat_trust' => "Generate a professional 'Experience, Expertise & Testing Methodology' trust block including author credentials, testing metrics, and device matrix. Format with blockquote, table, and bulleted criteria.",
            'search_intent' => 'Identify the primary search intent (Informational, Commercial, Transactional, Navigational), target audience persona, and the optimal content angle to satisfy user expectations.',
            'comparison_table' => "Generate a structured comparison table comparing key features, pros, cons, and performance metrics across top options. Output as a clean markdown table with | Header 1 | Header 2 | Header 3 |.",

            // Custom Instruction & Full Publication Directives
            'custom' => !empty($customInstruction) 
                ? $tiptapStyleGuide . "\n\nUser Instruction: {$customInstruction}\n\nDeliver an exhaustive, publication-grade document following all formatting permissions above without extraneous conversation."
                : $tiptapStyleGuide . "\n\nImprove and refine the following text with professional headings, bold markers, and clean structure.",

            default => $tiptapStyleGuide . "\n\nImprove and refine the following text with professional structure.",
        };
    }

    /**
     * Execute synchronous text transformation
     */
    public function execute(User $user, string $text, string $transformationType, array $options = []): string
    {
        if (!$user->hasQuota(1)) {
            throw new Exception("Monthly word quota exceeded. Please upgrade your plan or wait for the next billing cycle.");
        }

        $customInstruction = $options['custom_instruction'] ?? null;
        $systemPrompt = $this->getSystemPrompt($transformationType, $customInstruction);

        // Ground with Knowledge Base RAG context if available
        try {
            $ragAction = app(\App\Features\KnowledgeBase\Actions\RetrieveRagContext::class);
            $queryText = !empty($customInstruction) ? $customInstruction : $text;
            $ragResult = $ragAction->execute($user, $queryText, limit: 3);
            if (!empty($ragResult['has_context']) && !empty($ragResult['prompt_snippet'])) {
                $systemPrompt .= "\n\n" . $ragResult['prompt_snippet'];
            }
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $text],
        ];

        $response = $this->client->chatCompletion($messages, $options);
        $resultText = trim($response['content']);

        $wordCount = str_word_count(strip_tags($resultText));
        $this->recordUsage->execute($user, [
            'words_used' => max(1, $wordCount),
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model_slug' => $response['model'] ?? config('omniroute.default_model', 'auto'),
        ]);

        return $resultText;
    }
}