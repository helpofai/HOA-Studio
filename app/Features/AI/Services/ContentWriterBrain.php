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

use Illuminate\Support\Str;

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

        // Synthesize missing surrounding context and narrative placement from full document
        $narrativeRole = 'Core Document Exposition & Analysis';
        if ($fullDocText !== '' && $selectedText !== '') {
            $pos = mb_strpos($fullDocText, $selectedText);
            if ($pos !== false) {
                $docLen = mb_strlen($fullDocText);
                $relPos = $docLen > 0 ? ($pos / $docLen) : 0.5;
                if ($relPos < 0.15) {
                    $narrativeRole = 'Document Opening & Introduction Hook';
                } elseif ($relPos > 0.85) {
                    $narrativeRole = 'Concluding Synthesis, Verdict & Action Steps';
                } else {
                    $narrativeRole = 'Mid-Article Analytical Deep-Dive & Argumentation';
                }

                if ($precedingText === '') {
                    $preStart = max(0, $pos - 700);
                    $precedingText = trim(mb_substr($fullDocText, $preStart, $pos - $preStart));
                }
                if ($followingText === '') {
                    $postStart = $pos + mb_strlen($selectedText);
                    $followingText = trim(mb_substr($fullDocText, $postStart, 700));
                }
            }
        }

        // 1. Core Brain Memory Bank
        $memoryBank = "=== 🧠 CONTENT WRITER BRAIN & MEMORY (GLOBAL DOCUMENT MEMORY) ===\n";
        if ($docTitle !== '') {
            $memoryBank .= "Article Working Title: {$docTitle}\n";
        }
        if ($targetKeyword !== '') {
            $memoryBank .= "Target SEO Keyword: {$targetKeyword}\n";
        }
        $memoryBank .= "Narrative Role of Selection: {$narrativeRole}\n";
        if ($fullDocText !== '') {
            $memoryBank .= "Full Document Knowledge & Thematic Trajectory:\n\"\"\"\n" . mb_substr($fullDocText, 0, 10000) . "\n\"\"\"\n";
        }
        if ($precedingText !== '') {
            $memoryBank .= "Immediate Preceding Context (Flow Inflow):\n\"\"\"\n{$precedingText}\n\"\"\"\n";
        }
        if ($followingText !== '') {
            $memoryBank .= "Immediate Following Context (Flow Outflow):\n\"\"\"\n{$followingText}\n\"\"\"\n";
        }
        $memoryBank .= "=== END OF BRAIN MEMORY ===\n\n";

        // 2. Action-Specific Algorithmic Directive
        $kwSnippet = $targetKeyword !== '' ? " (Naturally align with focus keyword: '{$targetKeyword}')" : "";

        $toolDirective = match ($actionTool) {
            'recreate' => <<<EOT
ACTION TOOL: RECREATE PARAGRAPH (STRUCTURAL & THEMATIC RE-ARCHITECTURE)
OBJECTIVE:
Completely re-architect and rewrite the marked text from scratch with elevated vocabulary, captivating cadence, and authoritative domain clarity{$kwSnippet}.
DEEP DOCUMENT INTEGRATION:
- Synthesize the overarching document title ('{$docTitle}') and the surrounding narrative flow from your Brain Memory.
- Discard the old sentence structure entirely. Write completely fresh, high-impact prose that accomplishes the original paragraph's purpose with 10x higher clarity, fresh metaphors/analogies, and superior engagement.
RULES:
1. STRICT ANTI-ECHO GUARANTEE: Never repeat the original opening phrasing or sentence architecture. The paragraph must be noticeably re-imagined.
2. Upgrade weak verbs and repetitive wording into authoritative domain terminology.
3. OUTPUT ONLY the single recreated paragraph as pure prose. Do NOT output headings, bullet lists, or conversational filler.
EOT,

            'rewrite', 'polish', 'rewrite_polish' => <<<EOT
ACTION TOOL: REWRITE & POLISH (SUBSTANTIVE STYLISTIC ELEVATION)
OBJECTIVE:
Significantly elevate the quality, flow, active voice, sentence rhythm, and articulateness of the marked text{$kwSnippet}.
DEEP DOCUMENT INTEGRATION:
- Read the preceding context and following context to ensure the rewritten paragraph transitions seamlessly into the next thought of the article.
- Eliminate all passive voice, weak verbs, filler words (e.g. 'in order to', 'it is important to note', 'basically', 'due to the fact that'), and awkward phrasing.
RULES:
1. STRICT ANTI-ECHO GUARANTEE: You are strictly forbidden from returning the original text unchanged or with only 1-2 trivial word swaps. The revision must be immediately and noticeably superior in style, impact, and rhythm.
2. Maintain perfect narrative flow with the preceding and following paragraphs.
3. OUTPUT ONLY the single rewritten paragraph as pure prose without markdown headings or conversational chatter.
EOT,

            'expand' => <<<EOT
ACTION TOOL: EXPAND WITH DEPTH (ANALYTICAL SUBSTANCE & RIGOR)
OBJECTIVE:
Deepen and expand the marked text with rich analytical substance, practical implications, illustrative depth, and concrete rationale{$kwSnippet}.
DEEP DOCUMENT INTEGRATION:
- Unpack the underlying 'why' and 'how' behind the statements in light of the document's central thesis ('{$docTitle}').
- Provide practical real-world nuance or tactical considerations that directly connect to the surrounding paragraphs without generic fluff.
RULES:
1. Expand the content to approximately 1.5x - 2.2x depth with high information density.
2. Ensure seamless continuity with the following text — do not introduce repetitive points already covered.
3. OUTPUT ONLY the expanded content (1 to 2 rich paragraphs).
EOT,

            'shorten', 'condense' => <<<EOT
ACTION TOOL: SHORTEN & CONDENSE (HIGH-DENSITY DISTILLATION)
OBJECTIVE:
Distill the marked text into its absolute, crystal-clear, high-impact essence in 40% to 60% of the original word count.
DEEP DOCUMENT INTEGRATION:
- Identify the single most critical message this paragraph conveys within the document ('{$docTitle}').
- Ruthlessly eliminate fluff, qualifiers, redundant subordinate clauses, and filler adjectives.
RULES:
1. Preserve 100% of the core factual substance and domain insight.
2. Combine fragmented sentences into punchy, high-velocity active statements.
3. OUTPUT ONLY the condensed paragraph.
EOT,

            'simplify', 'simplify_8th' => <<<EOT
ACTION TOOL: SIMPLIFY (8TH-GRADE READING LEVEL / HEMINGWAY STYLE)
OBJECTIVE:
Translate dense, multi-clause, or academic phrasing into effortless, crisp plain English at an 8th-grade reading level.
DEEP DOCUMENT INTEGRATION:
- Ensure the simplified text maintains the intelligent core insight within the broader article ('{$docTitle}') while removing all cognitive friction for the reader.
- Replace polysyllabic jargon with common, powerful everyday words (e.g. 'utilize' -> 'use', 'facilitate' -> 'help', 'optimal' -> 'best').
RULES:
1. Keep sentences short (average 12-16 words) with clean subject-verb-object structures.
2. Maximize readability and scannability while keeping the tone smart and respectful.
3. OUTPUT ONLY the simplified paragraph.
EOT,

            'generate_faq', 'faq' => <<<EOT
ACTION TOOL: GENERATE FAQ BLOCK (SEARCH INTENT ANSWERS)
OBJECTIVE:
Generate 2 to 3 high-value, search-intent FAQ questions and authoritative answers directly addressing queries readers will have about this content{$kwSnippet}.
DEEP DOCUMENT INTEGRATION:
- Align questions with the document's primary theme ('{$docTitle}') and target keyword.
- Answer each question directly in 2 to 3 concise, authoritative sentences with key entities highlighted in **bold**.
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

            'seo_optimize', 'seo' => <<<EOT
ACTION TOOL: SEO OPTIMIZE TEXT (AI OVERVIEWS & TOPICAL AUTHORITY)
OBJECTIVE:
Optimize the marked text for search authority, semantic entity density, and AI search engine extraction (Google AI Overviews, Perplexity, GEO){$kwSnippet}.
DEEP DOCUMENT INTEGRATION:
- Naturally weave the focus keyword ('{$targetKeyword}') and topically relevant semantic LSI entities into the text without keyword stuffing.
- Front-load primary subject matter so search bots and AI answer engines immediately parse the core entity-relationship.
- Emphasize critical entities and conclusions in **bold**.
RULES:
1. Preserve natural, compelling editorial voice.
2. Ensure high scannability and structured flow.
3. OUTPUT ONLY the SEO-optimized paragraph as pure prose.
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
1. STRICT ANTI-ECHO GUARANTEE:
   - You are STRICTLY FORBIDDEN from returning the original text unchanged or with mere punctuation/synonym swapping.
   - You MUST execute a noticeable, high-value qualitative transformation matching the requested action.
2. TARGET ISOLATION:
   - Transform ONLY the exact text enclosed within <target_marked_content>.
   - NEVER repeat surrounding paragraphs, full document outlines, or unrelated sections.
3. HEADING TYPE INTEGRITY:
   - If the marked text is a standard paragraph or sentence, output STRICTLY a standard body paragraph without ANY markdown headings (#, ##, ###), title tags, label prefixes (e.g. "**Revised:**", "**Rewritten:**", "Introduction:"), or bullet lists.
   - If the marked text is an H2 heading, output ONLY a single updated H2 heading (## ...).
   - If the marked text is an H3 heading, output ONLY a single updated H3 heading (### ...).
   - NEVER convert a standard paragraph into a heading unless the action tool explicitly specifies it (like 'generate_faq').
4. ZERO SPACING & FORMATTING BLOAT:
   - Output clean, tight prose without leading/trailing empty lines, excessive line breaks, or redundant blank paragraphs.
5. ZERO CONVERSATIONAL CHATTER:
   - Output pure finished text immediately with zero conversational preambles (no "Here is...", no "Sure!", no "Certainly!").
EOT;

        $systemPrompt = $memoryBank . "\n" . $toolDirective . "\n\n" . $guardrail;

        $actionNameUpper = strtoupper(str_replace('_', ' ', $actionTool));
        $docTitleDisplay = $docTitle !== '' ? $docTitle : 'Content Production Workspace';
        $targetKeywordDisplay = $targetKeyword !== '' ? "'{$targetKeyword}'" : 'Topical Authority';
        $placementSummary = "Narrative Placement: {$narrativeRole}\n";
        if ($precedingText !== '') {
            $placementSummary .= "Preceding Flow Context: \"" . Str::limit($precedingText, 160) . "\"\n";
        }
        if ($followingText !== '') {
            $placementSummary .= "Following Flow Context: \"" . Str::limit($followingText, 160) . "\"\n";
        }

        $userContent = <<<EOT
DOCUMENT INTELLIGENCE CONTEXT:
- Document Title: {$docTitleDisplay}
- Target Keyword: {$targetKeywordDisplay}
{$placementSummary}
ACTION TO EXECUTE: [{$actionNameUpper}]

ORIGINAL TARGET CONTENT TO TRANSFORM:
<target_marked_content>
{$selectedText}
</target_marked_content>

CRITICAL EXECUTION MANDATE:
1. Understand the full document thesis and narrative placement before writing.
2. Execute the [{$actionNameUpper}] transformation decisively on the marked content.
3. STRICT ANTI-ECHO PROTOCOL: Under NO circumstances return the original text unchanged. You MUST provide a distinct, noticeably upgraded result.
4. Output ONLY the pure transformed text without introductory greetings, commentary, or meta-labels.
EOT;

        if (!empty($customInstruction) && !in_array($customInstruction, ['rewrite', 'recreate', 'polish', 'expand', 'shorten', 'simplify', 'generate_faq', 'seo_optimize', $actionTool])) {
            $userContent .= "\n\nAdditional Editorial Guidance: {$customInstruction}";
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
        if (in_array($type, ['full_article', 'article', 'blog_post', 'generate_article', 'pipeline_article', 'multi_agent_pipeline', 'seo_auto_heal'])) {
            return true;
        }

        $componentTypes = [
            'comparison_table', 'quick_answer', 'generate_faq', 'faq', 'key_takeaways',
            'geo_direct_answer', 'geo_data_points',
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

    /**
     * Algorithmic zero-token local transformation engine (Offline / Quota-exhausted fallback)
     *
     * @param string $actionTool
     * @param string $selectedText
     * @param array $context
     * @return string
     */
    public function executeLocalActionTransform(string $actionTool, string $selectedText, array $context = []): string
    {
        $text = trim($selectedText);
        if ($text === '') {
            return '';
        }

        $docTitle = trim($context['document_title'] ?? '');
        $targetKeyword = trim($context['target_keyword'] ?? '');

        // Normalize action tool name
        $action = match ($actionTool) {
            'polish', 'rewrite_polish' => 'rewrite',
            'condense' => 'shorten',
            'simplify_8th' => 'simplify',
            'faq' => 'generate_faq',
            'seo' => 'seo_optimize',
            default => $actionTool,
        };

        return match ($action) {
            'recreate' => $this->localRecreate($text, $docTitle, $targetKeyword),
            'rewrite' => $this->localRewrite($text, $targetKeyword),
            'expand' => $this->localExpand($text, $docTitle, $targetKeyword),
            'shorten' => $this->localShorten($text),
            'simplify' => $this->localSimplify($text),
            'generate_faq' => $this->localGenerateFaq($text, $docTitle, $targetKeyword),
            'seo_optimize' => $this->localSeoOptimize($text, $targetKeyword),
            'key_takeaways' => $this->localKeyTakeaways($text),
            default => $this->localRewrite($text, $targetKeyword),
        };
    }

    protected function localRecreate(string $text, string $docTitle, string $targetKeyword): string
    {
        $replacements = [
            '/\bimportant\b/i' => 'pivotal',
            '/\bgood\b/i' => 'exceptional',
            '/\buse\b/i' => 'leverage',
            '/\busing\b/i' => 'leveraging',
            '/\bused\b/i' => 'leveraged',
            '/\buses\b/i' => 'leverages',
            '/\bhelp\b/i' => 'accelerate',
            '/\bhelps\b/i' => 'accelerates',
            '/\bhelping\b/i' => 'accelerating',
            '/\bmake\b/i' => 'architect',
            '/\bmakes\b/i' => 'architects',
            '/\bchange\b/i' => 'transform',
            '/\bchanges\b/i' => 'transforms',
            '/\bshow\b/i' => 'demonstrate',
            '/\bshows\b/i' => 'demonstrates',
            '/\bneed\b/i' => 'require',
            '/\bneeds\b/i' => 'requires',
            '/\bproblem\b/i' => 'bottleneck',
            '/\bproblems\b/i' => 'bottlenecks',
            '/\bfix\b/i' => 'remedy',
            '/\bfixes\b/i' => 'remedies',
            '/\bbig\b/i' => 'substantial',
            '/\bfast\b/i' => 'high-velocity',
            '/\bnew\b/i' => 'next-generation',
            '/\bbest\b/i' => 'premier',
            '/\bsimple\b/i' => 'frictionless',
        ];

        $recreated = preg_replace(array_keys($replacements), array_values($replacements), $text);

        // Discard old sentence opening with a dynamic rhetorical lead-in
        $sentences = preg_split('/(?<=[.?!])\s+/', $recreated, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($sentences)) {
            $first = ltrim($sentences[0]);
            $first = lcfirst($first);
            if ($docTitle !== '') {
                $leadIn = "To systematically advance {$docTitle}, ";
            } elseif ($targetKeyword !== '') {
                $leadIn = "When strategically implementing {$targetKeyword}, ";
            } else {
                $leadIn = "Fundamentally, ";
            }
            $sentences[0] = $leadIn . $first;
            $recreated = implode(' ', $sentences);
        }

        // Anti-echo guarantee: If identical, append authoritative strategic synthesis
        if (trim($recreated) === trim($text)) {
            $recreated = "Fundamentally, " . lcfirst($text) . " This approach ensures enduring clarity, precision, and operational resilience.";
        }

        return $recreated;
    }

    protected function localRewrite(string $text, string $targetKeyword): string
    {
        $fillers = [
            '/\bin order to\b/i' => 'to',
            '/\bdue to the fact that\b/i' => 'because',
            '/\bat this point in time\b/i' => 'currently',
            '/\bfor the purpose of\b/i' => 'to',
            '/\bin the event that\b/i' => 'if',
            '/\bit is important to note that\s*/i' => '',
            '/\bit should be noted that\s*/i' => '',
            '/\bbasically,\s*/i' => '',
            '/\bessentially,\s*/i' => '',
            '/\bvery\s+/i' => '',
            '/\breally\s+/i' => '',
            '/\bquite\s+/i' => '',
            '/\bis able to\b/i' => 'can',
            '/\bhas the ability to\b/i' => 'can',
            '/\bserves to\b/i' => 'directly',
        ];

        $polished = preg_replace(array_keys($fillers), array_values($fillers), $text);
        $polished = preg_replace('/\s{2,}/', ' ', trim($polished));

        // Capitalize sentences after punctuation
        $polished = preg_replace_callback('/(^|[.!?]\s+)([a-z])/', function ($matches) {
            return $matches[1] . strtoupper($matches[2]);
        }, $polished);

        // Anti-echo guarantee
        if (trim($polished) === trim($text)) {
            $words = explode(' ', $polished);
            if (count($words) > 3) {
                $polished = "Notably, " . lcfirst($polished);
            }
        }

        return $polished;
    }

    protected function localExpand(string $text, string $docTitle, string $targetKeyword): string
    {
        $expanded = $text;

        $analyticalExpansion = "\n\nSpecifically, this dynamic establishes a resilient foundation by addressing the nuanced operational trade-offs inherent in modern execution.";
        if ($targetKeyword !== '') {
            $analyticalExpansion .= " Aligning directly with {$targetKeyword} empowers practitioners to eliminate systemic bottlenecks while maintaining uncompromising qualitative consistency.";
        } elseif ($docTitle !== '') {
            $analyticalExpansion .= " Within the strategic framework of {$docTitle}, this ensures that every stage delivers measurable tactical impact.";
        }

        return trim($expanded . $analyticalExpansion);
    }

    protected function localShorten(string $text): string
    {
        // Strip parentheticals and filler phrases
        $condensed = preg_replace('/\([^)]*\)/', '', $text);

        $stripPatterns = [
            '/\b(in order to|due to the fact that|as a matter of fact|at the end of the day|it goes without saying that|needless to say)\b/i' => '',
            '/\b(basically|essentially|actually|literally|virtually|practically|frankly|honestly)\b/i' => '',
            '/\b(very|extremely|really|quite|somewhat|fairly|pretty much)\b/i' => '',
        ];

        $condensed = preg_replace(array_keys($stripPatterns), array_values($stripPatterns), $condensed);
        $condensed = preg_replace('/\s{2,}/', ' ', trim($condensed));

        $sentences = preg_split('/(?<=[.?!])\s+/', $condensed, -1, PREG_SPLIT_NO_EMPTY);
        if (count($sentences) > 2) {
            // Retain the first and most informative sentences
            $condensed = $sentences[0] . ' ' . end($sentences);
        }

        return trim($condensed);
    }

    protected function localSimplify(string $text): string
    {
        $jargonMap = [
            '/\butilize\b/i' => 'use',
            '/\butilizes\b/i' => 'uses',
            '/\butilized\b/i' => 'used',
            '/\butilizing\b/i' => 'using',
            '/\bfacilitate\b/i' => 'help',
            '/\bfacilitates\b/i' => 'helps',
            '/\bfacilitated\b/i' => 'helped',
            '/\bsubsequently\b/i' => 'then',
            '/\bcommence\b/i' => 'start',
            '/\bcommences\b/i' => 'starts',
            '/\bterminate\b/i' => 'end',
            '/\bterminates\b/i' => 'ends',
            '/\bimplement\b/i' => 'set up',
            '/\bimplements\b/i' => 'sets up',
            '/\bendeavor\b/i' => 'try',
            '/\bsubstantiate\b/i' => 'prove',
            '/\boptimal\b/i' => 'best',
            '/\bparamount\b/i' => 'key',
            '/\bconsequently\b/i' => 'so',
            '/\bdisseminate\b/i' => 'share',
            '/\bexpedite\b/i' => 'speed up',
            '/\bcomprehensive\b/i' => 'complete',
            '/\bfundamental\b/i' => 'basic',
            '/\bprioritize\b/i' => 'focus on',
            '/\bdemonstrate\b/i' => 'show',
            '/\bdemonstrates\b/i' => 'shows',
            '/\bsufficient\b/i' => 'enough',
        ];

        $simplified = preg_replace(array_keys($jargonMap), array_values($jargonMap), $text);
        // Break semicolons into full stops
        $simplified = str_replace(';', '.', $simplified);
        $simplified = preg_replace('/\s{2,}/', ' ', trim($simplified));

        // Capitalize after periods
        $simplified = preg_replace_callback('/(^|[.!?]\s+)([a-z])/', function ($matches) {
            return $matches[1] . strtoupper($matches[2]);
        }, $simplified);

        return $simplified;
    }

    protected function localGenerateFaq(string $text, string $docTitle, string $targetKeyword): string
    {
        $clean = trim(strip_tags($text));
        $firstSentence = preg_split('/(?<=[.?!])\s+/', $clean)[0] ?? $clean;
        $subject = $targetKeyword !== '' ? $targetKeyword : ($docTitle !== '' ? $docTitle : 'this process');

        $faq = "### What is the primary purpose of {$subject}?\n";
        $faq .= "**" . ucfirst($subject) . "** plays a vital role by establishing a clear, actionable workflow that eliminates complexity and ensures high reliability.\n\n";

        $faq .= "### How does this impact overall implementation?\n";
        $faq .= "By directly addressing core operational constraints, this approach provides **measurable consistency** and accelerates long-term project outcomes.\n\n";

        $faq .= "### What is the key takeaway to remember?\n";
        $faq .= "The foundational principle is that **{$firstSentence}** provides the clearest benchmark for sustained progress.";

        return $faq;
    }

    protected function localSeoOptimize(string $text, string $targetKeyword): string
    {
        $clean = trim($text);

        // If target keyword is defined and not yet in text, weave into opening
        if ($targetKeyword !== '' && !str_contains(mb_strtolower($clean), mb_strtolower($targetKeyword))) {
            $clean = "**" . ucfirst($targetKeyword) . "** is essential here: " . lcfirst($clean);
        }

        // Bold key analytical phrases and nouns
        $clean = preg_replace('/\b(key takeaway|best practice|proven strategy|primary benefit|essential metric)\b/i', '**$1**', $clean);

        return $clean;
    }

    protected function localKeyTakeaways(string $text): string
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', trim(strip_tags($text)), -1, PREG_SPLIT_NO_EMPTY);
        $bullets = [];

        $labels = ['Core Principle', 'Strategic Impact', 'Actionable Execution'];
        $i = 0;
        foreach ($sentences as $sentence) {
            if ($i >= 3) break;
            $label = $labels[$i] ?? 'Insight';
            $bullets[] = "- **{$label}:** " . trim($sentence);
            $i++;
        }

        if (empty($bullets)) {
            $bullets[] = "- **Core Insight:** " . trim($text);
        }

        return implode("\n", $bullets);
    }
}
