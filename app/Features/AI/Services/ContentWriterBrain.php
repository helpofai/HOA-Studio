<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Writer Brain & Memory Engine
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

class ContentWriterBrain
{
    /**
     * Build the specialized Brain & Memory Prompt Matrix for surgical action tools
     */
    public function buildSurgicalPrompt(string $actionTool, array $context, ?string $customInstruction = null): array
    {
        $selectedText = trim($context['selected_text'] ?? ($context['target_text'] ?? ($context['text'] ?? '')));
        if ($selectedText === '' && !empty($customInstruction)) {
            $selectedText = $customInstruction;
        }
        $fullDocText = trim($context['full_document_text'] ?? '');
        $precedingText = trim($context['preceding_text'] ?? '');
        $followingText = trim($context['following_text'] ?? '');
        $docTitle = trim($context['document_title'] ?? '');
        $targetKeyword = trim($context['target_keyword'] ?? '');

        // 1. Core Brain Memory Bank
        $memoryBank = "=== 🧠 CONTENT WRITER BRAIN & MEMORY ===\n";
        if ($docTitle !== '') {
            $memoryBank .= "Document Title: {$docTitle}\n";
        }
        if ($targetKeyword !== '') {
            $memoryBank .= "Target SEO Keyword: {$targetKeyword}\n";
        }
        if ($fullDocText !== '') {
            $memoryBank .= "Full Article Knowledge & Thematic Trajectory:\n\"\"\"\n" . mb_substr($fullDocText, 0, 10000) . "\n\"\"\"\n";
        }
        if ($precedingText !== '') {
            $memoryBank .= "Immediate Preceding Context (Text Before Selection):\n\"\"\"\n{$precedingText}\n\"\"\"\n";
        }
        if ($followingText !== '') {
            $memoryBank .= "Immediate Following Context (Text After Selection):\n\"\"\"\n{$followingText}\n\"\"\"\n";
        }
        $memoryBank .= "=== END OF BRAIN MEMORY ===\n\n";

        // 2. Action-Specific Algorithmic Directive
        $kwSnippet = $targetKeyword !== '' ? " (Naturally align with focus keyword: '{$targetKeyword}')" : "";

        $toolDirective = match ($actionTool) {
            'recreate' => <<<EOT
ACTION TOOL: RECREATE PARAGRAPH
OBJECTIVE:
Completely re-architect and rewrite the marked text from scratch with elevated vocabulary, captivating cadence, and authoritative domain clarity{$kwSnippet}.
RULES:
1. Absorb the entire article's thesis and surrounding narrative from your Brain Memory so this paragraph fits seamlessly into the document.
2. Discard the old sentence structure and write fresh, powerful, high-impact prose.
3. OUTPUT ONLY the single recreated paragraph. Do NOT output headings, bullet lists, or conversational filler.
EOT,

            'rewrite', 'polish', 'rewrite_polish' => <<<EOT
ACTION TOOL: REWRITE & POLISH
OBJECTIVE:
Significantly elevate the quality, flow, active voice, and articulateness of the marked text{$kwSnippet}.
RULES:
1. Eliminate passive voice, weak verbs, filler words, and awkward phrasing.
2. Maintain perfect narrative flow with the preceding and following paragraphs.
3. You MUST provide a noticeable, high-quality revision — do NOT return the original text unchanged.
4. OUTPUT ONLY the single rewritten paragraph as pure prose.
EOT,

            'expand' => <<<EOT
ACTION TOOL: EXPAND WITH DEPTH
OBJECTIVE:
Deepen and expand the marked text with rich analytical substance, practical implications, and illustrative depth{$kwSnippet}.
RULES:
1. Answer the unspoken 'why' and 'how' while ensuring no redundancy with the following text.
2. Expand the content to approximately 1.5x - 2x depth without empty fluff.
3. OUTPUT ONLY the expanded content (1 to 2 rich paragraphs).
EOT,

            'shorten' => <<<EOT
ACTION TOOL: SHORTEN & CONDENSE
OBJECTIVE:
Distill the marked text into its absolute, crystal-clear, high-impact essence.
RULES:
1. Cut unnecessary words, qualifiers, and redundancies ruthlessly.
2. Preserve 100% of the core factual meaning in fewer, punchier sentences.
3. OUTPUT ONLY the condensed paragraph.
EOT,

            'simplify' => <<<EOT
ACTION TOOL: SIMPLIFY (8TH-GRADE)
OBJECTIVE:
Translate dense or academic phrasing into effortless, crisp plain English (Hemingway style).
RULES:
1. Replace jargon with clear, accessible words and crisp sentence structure.
2. Maintain the intelligent core insight while maximizing scannability and ease of reading.
3. OUTPUT ONLY the simplified paragraph.
EOT,

            'generate_faq' => <<<EOT
ACTION TOOL: GENERATE FAQ BLOCK
OBJECTIVE:
Generate 2 to 3 high-value, genuine FAQ questions and concise answers based on the marked content{$kwSnippet}.
FORMAT:
### [Question Text]?
[Direct 2-3 sentence answer with **bold key terms**.]
RULES:
Output ONLY the FAQ block with ### questions.
EOT,

            'key_takeaways' => <<<EOT
ACTION TOOL: EXTRACT KEY TAKEAWAYS
OBJECTIVE:
Extract 3 to 4 high-leverage key takeaways from the marked content.
FORMAT:
- **[Core Concept]:** [1-2 sentence actionable takeaway.]
RULES:
Output ONLY the bulleted list with bold leading concepts.
EOT,

            'seo_optimize' => <<<EOT
ACTION TOOL: SEO OPTIMIZE TEXT
OBJECTIVE:
Optimize the marked text for search authority, semantic entity density, and AI search extraction{$kwSnippet}.
RULES:
1. Naturally weave relevant semantic terms and bold key entities (**bold terms**).
2. Ensure high scannability and topical relevance while matching surrounding tone.
3. OUTPUT ONLY the optimized paragraph.
EOT,

            // Tone Shifting
            'tone:professional', 'professional' => <<<EOT
ACTION TOOL: TONE SHIFTER (EXECUTIVE & PROFESSIONAL)
Rewrite the marked text into an authoritative, C-suite executive tone with impeccable corporate clarity.
OUTPUT ONLY the rewritten text.
EOT,

            'tone:casual', 'casual' => <<<EOT
ACTION TOOL: TONE SHIFTER (WARM & CONVERSATIONAL)
Rewrite the marked text in an approachable, warm, friendly, and relatable conversational style.
OUTPUT ONLY the rewritten text.
EOT,

            'tone:persuasive', 'persuasive' => <<<EOT
ACTION TOOL: TONE SHIFTER (HIGH-IMPACT PERSUASIVE)
Rewrite the marked text with compelling copywriting principles, strong action verbs, and persuasive framing.
OUTPUT ONLY the rewritten text.
EOT,

            'tone:academic', 'academic' => <<<EOT
ACTION TOOL: TONE SHIFTER (ACADEMIC & ANALYTICAL)
Rewrite the marked text in a rigorous, scholarly, peer-reviewed, analytical academic tone.
OUTPUT ONLY the rewritten text.
EOT,

            'tone:friendly', 'friendly' => <<<EOT
ACTION TOOL: TONE SHIFTER (FRIENDLY & EMPATHETIC)
Rewrite the marked text in an encouraging, warm, and helpful tone.
OUTPUT ONLY the rewritten text.
EOT,

            'tone:direct', 'direct' => <<<EOT
ACTION TOOL: TONE SHIFTER (DIRECT & ACTIVE)
Rewrite the marked text in a punchy, active-voice, zero-fluff, high-velocity style.
OUTPUT ONLY the rewritten text.
EOT,

            // Structured Component & Media Blocks
            'comparison_table' => <<<EOT
ACTION TOOL: COMPARISON TABLE
OBJECTIVE:
Generate a structured, responsive comparison table comparing key features, specifications, pros, cons, and metrics{$kwSnippet}.
RULES:
1. Output clean markdown table syntax (| Col 1 | Col 2 | Col 3 |) or clean HTML (<table>...</table>).
2. Include at least 3-5 structured rows comparing top options/facets clearly.
3. Output ONLY the table itself without conversational preambles, titles, or surrounding paragraphs.
EOT,

            'quick_answer' => <<<EOT
ACTION TOOL: QUICK ANSWER CALLOUT
OBJECTIVE:
Generate a high-impact, front-loaded executive quick-answer summary callout box{$kwSnippet}.
FORMAT:
> **Quick Answer:** [Clear, authoritative 2-3 sentence answer satisfying core reader intent with **bold key terms**.]
RULES:
Output ONLY the blockquote callout.
EOT,

            'eeat_trust' => <<<EOT
ACTION TOOL: E-E-A-T TRUST & TESTING BLOCK
OBJECTIVE:
Generate an authoritative trust block documenting testing methodology, reviewer expertise, and evaluation metrics{$kwSnippet}.
RULES:
Output ONLY the trust callout box with blockquote, structured criteria, and author credentials.
EOT,

            'content_gaps' => <<<EOT
ACTION TOOL: CONTENT GAP ANALYSIS
OBJECTIVE:
Identify 3 to 5 critical missing angles, nuanced FAQs, and tactical points competitors omit{$kwSnippet}.
RULES:
Output ONLY a clean bulleted list with bold leading topics.
EOT,

            'generate_outline', 'outline' => <<<EOT
ACTION TOOL: OUTLINE ARCHITECT
OBJECTIVE:
Generate a clean, hierarchical content outline with H1, H2, and H3 headings and bullet items{$kwSnippet}.
RULES:
Output ONLY the markdown outline structure.
EOT,

            'seo_fix_title' => <<<EOT
ACTION TOOL: SEO HEADLINE GENERATOR
OBJECTIVE:
Generate a high-CTR, click-worthy, SEO-optimized title frontloading the target keyword{$kwSnippet}.
RULES:
Output STRICTLY the single headline title on a single line. Do NOT output quotes, asterisks, hashtags, or commentary.
EOT,

            'seo_fix_meta' => <<<EOT
ACTION TOOL: SEO META DESCRIPTION
OBJECTIVE:
Generate a punchy, click-optimized 150-160 character meta description featuring the target keyword{$kwSnippet}.
RULES:
Output STRICTLY the single meta description without quotes, commentary, or multiple paragraphs.
EOT,

            'seo_fix_intro' => <<<EOT
ACTION TOOL: SEO INTRO REWRITE
OBJECTIVE:
Rewrite the opening 1-2 paragraphs of the document to naturally front-load the target keyword{$kwSnippet} within the opening 2 sentences.
RULES:
Output ONLY the 1-2 opening paragraphs. Do NOT write the rest of the document.
EOT,

            'seo_fix_subheadings' => <<<EOT
ACTION TOOL: SEO SUBHEADINGS INTEGRATOR
OBJECTIVE:
Add keyword-optimized H2 and H3 subheadings with concise transition paragraphs{$kwSnippet}.
RULES:
Output ONLY the subheadings and short transition text, not the full document.
EOT,

            'seo_fix_citations' => <<<EOT
ACTION TOOL: REFERENCES & CITATIONS BLOCK
OBJECTIVE:
Generate an authoritative 'References & External Citations' block with 2-3 credible citations, links, and study references.
RULES:
Output ONLY the citation block in clean markdown/HTML.
EOT,

            'seo_fix_density' => <<<EOT
ACTION TOOL: KEYWORD DENSITY SURGEON
OBJECTIVE:
Surgically integrate the target keyword{$kwSnippet} naturally into the provided text without keyword stuffing.
RULES:
Preserve existing structure and output ONLY the updated text.
EOT,

            'fix_grammar' => <<<EOT
ACTION TOOL: GRAMMAR & PROOFREADING
OBJECTIVE:
Correct all spelling, punctuation, capitalization, and grammatical errors while preserving voice.
RULES:
Output ONLY the corrected text.
EOT,

            'summarize' => <<<EOT
ACTION TOOL: EXECUTIVE SUMMARY
OBJECTIVE:
Provide a clear, high-density summary capturing all core insights.
RULES:
Output ONLY the summary.
EOT,

            'tldr' => <<<EOT
ACTION TOOL: TL;DR BULLETS
OBJECTIVE:
Generate 2 to 3 concise, high-impact bullet points summarizing the core essence.
RULES:
Output ONLY the bullet points.
EOT,

            'action_items' => <<<EOT
ACTION TOOL: ACTION ITEMS CHECKLIST
OBJECTIVE:
Extract a concrete implementation checklist with actionable steps.
RULES:
Output as a task checklist (- [ ] Item).
EOT,

            'continue' => <<<EOT
ACTION TOOL: SEAMLESS CONTINUATION
OBJECTIVE:
Seamlessly continue writing from where the text stops in the exact same voice and style.
RULES:
Output ONLY the continuation text (2-3 paragraphs).
EOT,

            'custom' => !empty($customInstruction)
                ? "ACTION TOOL: INLINE AI DIRECTIVE\nExecute this directive on the marked text: {$customInstruction}\nOUTPUT ONLY the transformed text without conversational filler."
                : "ACTION TOOL: ENHANCE\nElevate the marked text with superior clarity and flow. OUTPUT ONLY the revised text.",

            default => !empty($customInstruction)
                ? "ACTION TOOL: CUSTOM\nTask: {$customInstruction}\nApply to the marked text and OUTPUT ONLY the revised content."
                : "ACTION TOOL: EDITORIAL ELEVATION\nElevate the marked text with superior flow, active voice, and precision. OUTPUT ONLY the revised text."
        };

        // 3. Strict Execution & Typographical Guardrails
        $guardrail = <<<EOT
CRITICAL SURGICAL & TYPOGRAPHICAL DIRECTIVES:
1. TARGET ISOLATION:
   - Transform ONLY the exact text enclosed within <target_marked_content>.
   - NEVER repeat surrounding paragraphs, full document outlines, or unrelated sections.
2. HEADING TYPE INTEGRITY:
   - If the marked text is a standard paragraph or sentence, output STRICTLY a standard body paragraph without ANY markdown headings (#, ##, ###), title tags, label prefixes (e.g. "**Revised:**", "**Rewritten:**", "Introduction:"), or bullet lists.
   - If the marked text is an H2 heading, output ONLY a single updated H2 heading (## ...).
   - If the marked text is an H3 heading, output ONLY a single updated H3 heading (### ...).
   - NEVER convert a standard paragraph into a heading.
3. ZERO SPACING & FORMATTING BLOAT:
   - Output clean, tight prose without leading/trailing empty lines, excessive line breaks, or redundant blank paragraphs.
4. ZERO CONVERSATIONAL CHATTER:
   - Output pure finished text immediately with zero conversational preambles (no "Here is...", no "Sure!", no "Certainly!").
EOT;

        $systemPrompt = $memoryBank . "\n" . $toolDirective . "\n\n" . $guardrail;

        $userContent = "<target_marked_content>\n{$selectedText}\n</target_marked_content>";
        if (!empty($customInstruction) && !in_array($customInstruction, ['rewrite', 'recreate', 'polish', 'custom', $actionTool])) {
            $userContent .= "\n\nSpecific Editorial Instruction: {$customInstruction}";
        }

        return [
            'system' => $systemPrompt,
            'user' => $userContent,
        ];
    }

    /**
     * Build the 15-Stage Production Pipeline Master Prompt integrated with Brain & Memory
     *
     * @param string $userPrompt
     * @param array $context
     * @param array $pipelineStages
     * @param string|null $customInstruction
     * @return array ['system' => string, 'user' => string]
     */
    public function buildPipelineArticlePrompt(string $userPrompt, array $context, array $pipelineStages = [], ?string $customInstruction = null): array
    {
        $fullDocText = trim($context['full_document_text'] ?? '');
        $docTitle = trim($context['document_title'] ?? '');
        $targetKeyword = trim($context['target_keyword'] ?? '');

        // 1. Brain & Memory Grounding Matrix
        $memoryBank = "=== 🧠 CONTENT WRITER BRAIN & GLOBAL MEMORY ===\n";
        if ($docTitle !== '') {
            $memoryBank .= "Article Working Title: {$docTitle}\n";
        }
        if ($targetKeyword !== '') {
            $memoryBank .= "Primary Target SEO Keyword: {$targetKeyword}\n";
        }
        if ($fullDocText !== '') {
            $memoryBank .= "Existing Document Canvas Knowledge & Outline:\n\"\"\"\n" . mb_substr($fullDocText, 0, 10000) . "\n\"\"\"\n";
        }
        $memoryBank .= "=== END OF BRAIN MEMORY ===\n\n";

        // 2. Active 15-Stage Production Pipeline Directives
        $stageRules = [];
        $activeCount = 0;

        $kwSnippet = $targetKeyword !== '' ? " ('{$targetKeyword}')" : "";

        $allStageDefinitions = [
            'search_intent' => '1. [Search Intent Analysis]: Craft an authoritative, front-loaded executive quick-answer/summary box (> blockquote) immediately beneath the H1 title addressing core reader intent.',
            'keyword_research' => "2. [Keyword & Entity Integration]: Naturally weave the primary focus keyword{$kwSnippet} and relevant high-value semantic LSI entities throughout H2/H3 headings and early paragraphs.",
            'serp_competitor' => '3. [SERP Competitor Superiority]: Deliver deeper tactical substance, more complete breakdowns, and superior clarity compared to top search competitors.',
            'content_gaps' => '4. [Content Gap Closure]: Actively address overlooked sub-topics, edge cases, practical constraints, and nuanced FAQs that competing guides miss.',
            'article_outline' => '5. [Outline & Structural Architecture]: Structure the article with a clear, logical heading hierarchy (H1 -> H2 -> H3) with zero fluff and maximum readability.',
            'section_generation' => '6. [Section-by-Section Deep Synthesis]: Flesh out each major section with thorough, engaging, comprehensive prose and vivid real-world examples.',
            'fact_verification' => '7. [Fact & Source Grounding]: Anchor claims with credible domain logic, metrics, and authoritative perspectives with zero hallucinations.',
            'originality_check' => '8. [Originality & Novelty]: Use fresh analogies, engaging phrasing, and authoritative thought leadership rather than generic filler.',
            'seo_optimization' => '9. [Rank Math & SEO Optimization]: Optimize for 100/100 search visibility with bold key concepts (**bold terms**), strong transition words, and scannable visual anchors.',
            'readability_opt' => '10. [Readability & Cadence Flow]: Keep sentences crisp, dynamic, and written in active voice at an effortless reading level.',
            'internal_links' => '11. [Contextual Linking Hooks]: Highlight high-intent anchor phrases ([Anchor Text](#)) for internal linking opportunities.',
            'media_suggestions' => '12. [Rich Assets & Data Formatting]: Format key comparisons into structured markdown tables (| Header 1 | Header 2 |) with clear metrics.',
            'schema_generation' => '13. [Schema FAQ Block]: Append a rich 3-4 question FAQ block using ### [Question]? format with direct 2-sentence answers.',
            'quality_audit' => '14. [10-Point Quality Audit]: Ensure 100% compliance with high-retention enterprise editorial publishing standards.',
            'publish_assembly' => '15. [Publish-Ready Formatting]: Output the complete, beautifully structured TipTap markdown document ready for instant 1-click publishing.',
        ];

        if (empty($pipelineStages)) {
            $stageRules = array_values($allStageDefinitions);
            $activeCount = 15;
        } else {
            foreach ($pipelineStages as $stageKey) {
                if (isset($allStageDefinitions[$stageKey])) {
                    $stageRules[] = $allStageDefinitions[$stageKey];
                    $activeCount++;
                }
            }
            if ($activeCount === 0) {
                $stageRules = array_values($allStageDefinitions);
                $activeCount = 15;
            }
        }

        $pipelineDirectives = "=== ⚡ ACTIVE ENTERPRISE PRODUCTION PIPELINE ({$activeCount}/15 STAGES) ===\n" .
            implode("\n", $stageRules) . "\n" .
            "=== END OF PIPELINE DIRECTIVES ===\n\n";

        // 3. TipTap Formatting Permissions
        $tiptapRules = <<<EOT
FORMATTING & TIPTAP PUBLICATION PERMISSIONS:
- # H1: Exactly one primary title at the very top.
- ## H2: Main thematic sections and core chapters.
- ### H3: Tactical sub-points and FAQ questions.
- **Bold**: Prominently emphasize key entities, metrics, and core concepts.
- *Italics*: For nuanced terms and references.
- > Blockquotes (> Quick Answer: ...): For executive summary and quick-answer callouts.
- ▦ Tables (| Col 1 | Col 2 |): Include structured comparison and feature tables.
- ● Bullet / Numbered Lists: For scannable tips, checklists, and procedures.
- Output pure markdown immediately with zero conversational preambles (never say "Here is your article:").
EOT;

        $systemPrompt = $memoryBank . $pipelineDirectives . $tiptapRules;

        $userContent = !empty($customInstruction) && ($userPrompt === 'Document Context' || empty(trim($userPrompt)))
            ? $customInstruction
            : ($userPrompt . (!empty($customInstruction) && $customInstruction !== $userPrompt ? "\n\nSpecific Editorial Directive: " . $customInstruction : ''));

        return [
            'system' => $systemPrompt,
            'user' => $userContent,
        ];
    }

    /**
     * Determine if a transformation type represents a full-article generation request.
     */
    public function isFullArticleType(string $type, ?string $customInstruction = null, array $context = []): bool
    {
        if (in_array($type, ['full_article', 'article', 'blog_post', 'generate_article', 'pipeline_article'])) {
            return true;
        }

        $componentTypes = [
            'comparison_table', 'quick_answer', 'generate_faq', 'faq', 'key_takeaways',
            'seo_fix_title', 'seo_fix_meta', 'seo_fix_intro', 'seo_fix_subheadings',
            'seo_fix_citations', 'seo_fix_density', 'content_gaps', 'eeat_trust',
            'generate_outline', 'outline', 'summarize', 'tldr', 'action_items',
            'continue', 'fix_grammar', 'recreate', 'rewrite', 'polish', 'expand',
            'shorten', 'simplify', 'seo_optimize',
        ];

        if (in_array($type, $componentTypes) || str_starts_with($type, 'tone:')) {
            return false;
        }

        if ($type === 'custom') {
            if (!empty($context['has_selection']) && !empty($context['selected_text'])) {
                return false;
            }
            $actionTool = $context['action_tool'] ?? '';
            if (!empty($actionTool) && (in_array($actionTool, $componentTypes) || str_starts_with($actionTool, 'tone:'))) {
                return false;
            }
            return true;
        }

        return false;
    }
}
