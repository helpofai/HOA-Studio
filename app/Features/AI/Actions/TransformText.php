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

use App\Features\AI\Services\ContentWriterBrain;
use App\Features\AI\Services\OmniRouteClient;
use App\Models\User;
use Exception;

class TransformText
{
    public function __construct(
        protected OmniRouteClient $client,
        protected RecordGenerationUsage $recordUsage,
        protected ContentWriterBrain $brain
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
            // Rewrite & Polish (Paragraph-safe, no full-article headers)
            'polish' => "You are an elite prose editor. Task: Polish and enhance the provided text for flow, grammar, vocabulary, and elegance while strictly preserving the author's original meaning. Output ONLY the polished text without H1 titles, outlines, or conversational filler.",
            'rewrite' => "You are an elite prose editor. Task: Rephrase and rewrite the provided text with alternative phrasing, improved flow, and high readability while preserving the exact core meaning. Output ONLY the single rewritten paragraph/section without H1 titles, outlines, or conversational filler.",
            'fix_grammar' => 'You are a meticulous proofreader. Fix all spelling, punctuation, capitalization, and grammatical errors in the provided text. Do not alter the author\'s voice or style. Output ONLY the corrected text.',

            // Length & Flow
            'shorten' => 'You are a concise editor. Make the provided text significantly more direct, compact, and punchy. Eliminate all fluff, filler, and redundancy. Output ONLY the shortened text without conversational filler.',
            'expand' => "You are an elite copy editor. Task: Expand the provided text with relevant details, clear explanations, logical depth, examples, and compelling context without fluff. Output ONLY the expanded content without H1 titles or conversational filler.",
            'continue' => $tiptapStyleGuide . "\n\nTask: Seamlessly continue writing the text from where it stops, producing the next 2-3 logical, high-quality sections in the exact same voice and style. Output ONLY the continuation.",
            'tldr' => 'Generate 2 to 3 concise, high-impact bullet points summarizing the core essence of the provided text. Output ONLY the bullet points.',

            // Tones
            'tone:professional', 'professional' => "You are an elite copyeditor. Task: Rewrite the following text in an authoritative, professional, and corporate-ready executive tone. Output ONLY the rewritten text without H1 titles or conversational filler.",
            'tone:casual', 'casual' => "You are an elite copyeditor. Task: Rewrite the following text in a warm, conversational, and relatable tone. Output ONLY the rewritten text without H1 titles or conversational filler.",
            'tone:persuasive', 'persuasive' => "You are an elite copyeditor. Task: Rewrite the following text with compelling copywriting principles, strong action verbs, and persuasive framing designed to inspire action. Output ONLY the rewritten text without H1 titles or conversational filler.",
            'tone:friendly', 'friendly' => "You are an elite copyeditor. Task: Rewrite the following text in a kind, welcoming, empathetic, and friendly tone. Output ONLY the rewritten text without H1 titles or conversational filler.",
            'tone:academic', 'academic' => "You are an elite copyeditor. Task: Rewrite the following text in a scholarly, formal, and rigorously analytical academic tone. Output ONLY the rewritten text without H1 titles or conversational filler.",
            'tone:direct', 'direct' => "You are an elite copyeditor. Task: Rewrite the following text in a direct, active-voice, no-nonsense manner. Output ONLY the rewritten text without H1 titles or conversational filler.",

            // Summarize & Synthesis
            'summarize' => "You are an elite analyst. Task: Provide a clear, comprehensive, and well-structured summary of the provided text, capturing all primary conclusions and nuances. Output ONLY the summary.",
            'action_items' => "Extract a structured checklist of concrete action items and next steps from the text. Format as a clean task checklist with - [ ] task items.",
            'simplify' => 'Simplify the text so it is effortlessly readable at an 8th-grade level, replacing complex jargon with plain words. Output ONLY the simplified text.',
            'seo_optimize' => "Task: Optimize the following text for search engines, improving keyword clarity, bold terms, and scannable readability without rewriting the whole document.",

            // God-Tier SEO & Blog Creation Pipeline
            'generate_outline' => "You are an elite SEO content architect. Create a logical, comprehensive, and search-intent optimized article outline featuring one primary H1, multiple descriptive H2 sections with nested H3 subsections, an FAQ section, and a decision-oriented conclusion. Output clean markdown outline with #, ##, ###, and - bullet items.",
            'generate_faq' => "Generate 4-5 high-value, genuine search questions and clear, authoritative answers. Format cleanly as:\n### Question text here\nAnswer paragraph with bold key terms. Output ONLY the FAQ section.",
            'quick_answer' => "Generate a concise, high-value search-intent quick answer callout box.\nFormat cleanly as: > **Quick Answer:** [Clear 2-3 sentence answer satisfying user intent]. Output ONLY the callout box.",
            'content_gaps' => 'Identify 3-5 critical missing subtopics, unanswered questions, or competitive angles required to make this the definitive resource on the web. Output as clear bullet points with explanations.',
            'key_takeaways' => "Extract 4-5 actionable, high-impact key takeaways. Format as a clean bulleted list with bold leading concepts. Output ONLY the list.",
            'eeat_trust' => "Generate a professional 'Experience, Expertise & Testing Methodology' trust block including author credentials, testing metrics, and device matrix. Format with blockquote, table, and bulleted criteria. Output ONLY the trust block.",
            'search_intent' => 'Identify the primary search intent (Informational, Commercial, Transactional, Navigational), target audience persona, and the optimal content angle to satisfy user expectations.',
            'comparison_table' => "Generate a structured comparison table comparing key features, pros, cons, and performance metrics across top options. Output ONLY a clean HTML table (<table>...</table>).",

            // Content Intelligence Surgical Section-Specific Actions
            'seo_fix_intro' => "You are an elite SEO copy editor. Your task is to rewrite ONLY the introductory 1-2 paragraphs of the document to naturally front-load the target keyword within the opening 2 sentences. DO NOT write the rest of the document. Output ONLY the 1-2 opening paragraphs in clean HTML.",
            'seo_fix_subheadings' => "You are an SEO content architect. Add keyword-optimized H2 and H3 subheadings with concise transition paragraphs. Output ONLY the subheadings and short sections to be inserted, not the full document.",
            'seo_fix_citations' => "Generate an authoritative 'References & External Citations' block with 2-3 credible citations, links, and study references. Output ONLY the citation list/callout in clean HTML.",
            'seo_fix_density' => "Surgically adjust the provided text snippet so the target focus keyword is naturally integrated without keyword stuffing. Preserve the existing text and output ONLY the updated snippet.",
            'seo_fix_title' => "Generate an optimized, high-CTR headline incorporating the target keyword. Output ONLY the single headline title as plain text.",
            'seo_fix_meta' => "Generate a punchy, click-optimized 150-160 character meta description featuring the focus keyword. Output ONLY the meta description as plain text.",

            // Custom Instruction & Full Publication Directives
            'custom' => !empty($customInstruction) 
                ? $tiptapStyleGuide . "\n\nUser Instruction: {$customInstruction}\n\nDeliver an exhaustive, publication-grade document following all formatting permissions above without extraneous conversation."
                : $tiptapStyleGuide . "\n\nImprove and refine the following text with professional headings, bold markers, and clean structure.",

            default => $tiptapStyleGuide . "\n\nImprove and refine the following text with professional structure.",
        };
    }

    /**
     * Specialized prompt algorithms for localized paragraph & selection transforms
     */
    public function getSelectionPrompt(string $transformationType, ?string $customInstruction = null, ?string $targetKeyword = null): string
    {
        $kwSnippet = $targetKeyword ? " (Incorporate focus keyword: '{$targetKeyword}')" : "";

        return match ($transformationType) {
            'recreate' => <<<EOT
You are an elite editorial ghostwriter and narrative stylist.
The user has provided a raw paragraph enclosed in <target_paragraph> tags.

YOUR TASK:
Completely RECREATE and RE-ARCHITECT this paragraph from scratch with authoritative clarity, elevated vocabulary, and captivating prose rhythm{$kwSnippet}.

EDITORIAL GUIDELINES:
1. Do NOT repeat or echo the existing sentence structure. Restructure the thoughts with fresh, powerful phrasing.
2. Upgrade weak verbs, eliminate clichés, and inject authoritative domain presence.
3. Output ONLY the single recreated paragraph. Do NOT add markdown headings, bullet points, or meta-chatter.
4. Output immediately with zero conversational introduction.
EOT,

            'rewrite', 'polish', 'rewrite_polish' => <<<EOT
You are a master developmental editor and stylist.
The user has provided a paragraph enclosed in <target_paragraph> tags.

YOUR TASK:
REWRITE and POLISH this text into a significantly improved, punchy, and highly articulate paragraph{$kwSnippet}.

EDITORIAL GUIDELINES:
1. Eliminate all passive voice, redundant qualifiers, weak transitions, and awkward phrasing.
2. Reconstruct sentences with active voice, dynamic rhythm, and compelling cadence.
3. You MUST deliver a noticeable, superior revision — do NOT return the original text unchanged.
4. Output ONLY the single polished paragraph. No markdown titles, no conversational intro/outro.
EOT,

            'expand' => <<<EOT
You are an expert investigative copywriter and analytical essayist.
The user has provided a text snippet in <target_paragraph> tags.

YOUR TASK:
EXPAND this text with rich analytical depth, illustrative nuance, practical implications, and clear supporting context{$kwSnippet}.

EDITORIAL GUIDELINES:
1. Deepen the core insights by answering the unspoken 'why' and 'how'.
2. Expand the length by approximately 1.5x to 2x without adding empty fluff or repetition.
3. Output ONLY the expanded prose (1 to 2 rich paragraphs). No H1 titles or meta-commentary.
EOT,

            'shorten' => <<<EOT
You are an executive communications editor specializing in brevity and high-impact clarity.
The user has provided text in <target_paragraph> tags.

YOUR TASK:
CONDENSE and SHORTEN this text into its absolute, crystal-clear essence.

EDITORIAL GUIDELINES:
1. Cut unnecessary words, throat-clearing preambles, and redundancies ruthlessly.
2. Preserve 100% of the core factual meaning in half the word count.
3. Output ONLY the condensed, punchy result. No conversational filler.
EOT,

            'simplify' => <<<EOT
You are an expert plain-language communicator.
The user has provided text in <target_paragraph> tags.

YOUR TASK:
SIMPLIFY this text so that it is instantly comprehensible at an 8th-grade reading level (Hemingway style).

EDITORIAL GUIDELINES:
1. Replace dense jargon and multi-syllable abstractions with crisp, everyday words and clear analogies.
2. Keep sentences short, active, and effortless to scan.
3. Output ONLY the simplified prose. No conversational filler.
EOT,

            'generate_faq' => <<<EOT
You are an SEO search-intent architect.
The user has provided text in <target_paragraph> tags.

YOUR TASK:
Generate 2 to 3 high-value FAQ questions directly addressing user queries arising from this content{$kwSnippet}.

FORMAT RULES:
### [Question Text]?
[Direct, authoritative answer in 2-3 sentences with **bold** key terms.]

Output ONLY the FAQ block (using ### for questions).
EOT,

            'key_takeaways' => <<<EOT
You are an executive intelligence analyst.
The user has provided text in <target_paragraph> tags.

YOUR TASK:
Extract 3 to 4 high-leverage key takeaways from this content.

FORMAT RULES:
- **[Core Insight Name]:** [1-2 sentence actionable explanation.]

Output ONLY the bulleted takeaways list with bold leading anchors.
EOT,

            'seo_optimize' => <<<EOT
You are a top-tier SEO strategist and semantic search engineer.
The user has provided text in <target_paragraph> tags.

YOUR TASK:
SEO-OPTIMIZE this text for maximum topical authority, search crawler relevance, and AI answer engine extraction{$kwSnippet}.

EDITORIAL GUIDELINES:
1. Naturally weave high-value semantic entities and bold key concepts (**bold terms**).
2. Front-load primary subject matter and ensure high scannability.
3. Output ONLY the optimized paragraph/content.
EOT,

            // Tone Shifting
            'tone:professional', 'professional' => <<<EOT
You are an executive corporate communications director.
Rewrite the text in <target_paragraph> into an authoritative, polished, C-suite executive tone with impeccable professionalism.
Output ONLY the rewritten text.
EOT,

            'tone:casual', 'casual' => <<<EOT
You are a warm, engaging, and conversational writer.
Rewrite the text in <target_paragraph> in an approachable, friendly, and relatable conversational tone.
Output ONLY the rewritten text.
EOT,

            'tone:persuasive', 'persuasive' => <<<EOT
You are a world-class conversion copywriter.
Rewrite the text in <target_paragraph> using high-converting direct-response copywriting principles, strong active verbs, and compelling emotional hooks.
Output ONLY the rewritten text.
EOT,

            'tone:academic', 'academic' => <<<EOT
You are a senior academic researcher and peer-review editor.
Rewrite the text in <target_paragraph> in a scholarly, rigorous, analytical, and objective academic tone.
Output ONLY the rewritten text.
EOT,

            'tone:friendly', 'friendly' => <<<EOT
You are an empathetic, warm community writer.
Rewrite the text in <target_paragraph> in an encouraging, approachable, and helpful tone.
Output ONLY the rewritten text.
EOT,

            'tone:direct', 'direct' => <<<EOT
You are a no-nonsense, high-velocity writer.
Rewrite the text in <target_paragraph> in a punchy, active-voice, zero-fluff, direct style.
Output ONLY the rewritten text.
EOT,

            'custom' => !empty($customInstruction)
                ? "You are a precise editorial assistant.\nExecute this directive on the text in <target_paragraph>: {$customInstruction}\nOutput ONLY the transformed text without conversational filler."
                : "You are a master editor. Enhance and improve the text in <target_paragraph> with superior clarity and flow. Output ONLY the revised text.",

            default => !empty($customInstruction)
                ? "You are an expert copyeditor.\nTask: {$customInstruction}\nApply this to the text in <target_paragraph> and output ONLY the revised content."
                : "You are an elite copyeditor. Rewrite and elevate the text in <target_paragraph> with superior flow, active voice, and precision. Output ONLY the revised text."
        };
    }

    /**
     * Build system and user prompt for streaming or transformation
     */
    public function buildPrompt(string $text, string $transformationType, ?string $customInstruction = null, array $context = []): array
    {
        if (!isset($context['selected_text']) && !isset($context['target_text'])) {
            $context['target_text'] = $text;
        }

        if ($this->brain->isFullArticleType($transformationType, $customInstruction, $context)) {
            $pipelineStages = $context['pipeline_stages'] ?? [];
            return $this->brain->buildPipelineArticlePrompt($text, $context, $pipelineStages, $customInstruction);
        }

        return $this->brain->buildSurgicalPrompt($transformationType, $context, $customInstruction);
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
        $context = $options['context'] ?? [];
        if (!isset($context['selected_text']) && !isset($context['target_text'])) {
            $context['target_text'] = $text;
        }
        $pipelineStages = $options['pipeline_stages'] ?? [];
        $isFullArticle = $this->brain->isFullArticleType($transformationType, $customInstruction, $context);

        if ($isFullArticle) {
            $brainPrompt = $this->brain->buildPipelineArticlePrompt($text, $context, $pipelineStages, $customInstruction);
            $systemPrompt = $brainPrompt['system'];
            $userContent = $brainPrompt['user'];
        } else {
            $brainPrompt = $this->brain->buildSurgicalPrompt($transformationType, $context, $customInstruction);
            $systemPrompt = $brainPrompt['system'];
            $userContent = $brainPrompt['user'];
        }

        // Ground with Knowledge Base RAG context if available for full generation
        if ($isFullArticle) {
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
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent],
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