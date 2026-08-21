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
     * Contextual prompt mapping for all text transformations
     */
    public function getSystemPrompt(string $transformationType, ?string $customInstruction = null): string
    {
        return match ($transformationType) {
            // Rewrite & Polish
            'polish' => 'You are a master editor. Polish and enhance the provided text for flow, grammar, vocabulary, and elegance while strictly preserving the author\'s original meaning. Output ONLY the polished text with no conversational filler or markdown code blocks.',
            'rewrite' => 'You are an expert copywriter. Rephrase and rewrite the provided text with alternative phrasing and improved structure while keeping the exact core message. Output ONLY the rewritten text.',
            'fix_grammar' => 'You are a meticulous proofreader. Fix all spelling, punctuation, capitalization, and grammatical errors in the provided text. Do not alter the author\'s voice or style. Output ONLY the corrected text.',

            // Length & Flow
            'shorten' => 'You are a concise editor. Make the provided text significantly more direct, compact, and punchy. Eliminate all fluff, filler, and redundancy. Output ONLY the shortened text.',
            'expand' => 'You are an insightful content strategist. Expand the provided text with relevant details, clear explanations, logical depth, and compelling context without unnecessary fluff. Output ONLY the expanded text.',
            'continue' => 'You are a creative co-author. Seamlessly continue writing the text from where it stops, writing the next 1-2 logical, high-quality paragraphs in the exact same voice and style. Output ONLY the continuation.',
            'tldr' => 'You are an executive assistant. Generate 2 to 3 concise, high-impact bullet points summarizing the core essence of the provided text. Output ONLY the bullet points.',

            // Tones
            'tone:professional', 'professional' => 'Rewrite the following text in an authoritative, professional, and corporate-ready executive tone. Output ONLY the rewritten text.',
            'tone:casual', 'casual' => 'Rewrite the following text in a warm, conversational, and relatable tone as if chatting with a colleague or friend. Output ONLY the rewritten text.',
            'tone:persuasive', 'persuasive' => 'Rewrite the following text with compelling copywriting principles, strong action verbs, and persuasive framing designed to inspire action. Output ONLY the rewritten text.',
            'tone:friendly', 'friendly' => 'Rewrite the following text in a kind, welcoming, empathetic, and friendly tone. Output ONLY the rewritten text.',
            'tone:academic', 'academic' => 'Rewrite the following text in a scholarly, formal, and rigorously analytical academic tone. Output ONLY the rewritten text.',
            'tone:direct', 'direct' => 'Rewrite the following text in a direct, active-voice, no-nonsense manner. Output ONLY the rewritten text.',

            // Summarize & Synthesis
            'summarize' => 'You are a research analyst. Provide a clear, comprehensive, and well-structured summary of the provided text, capturing all primary conclusions and nuances. Output ONLY the summary.',
            'action_items' => 'Analyze the provided text and extract a structured checklist of concrete action items, next steps, and deliverables. Output ONLY the checklist formatted with markdown checkboxes (- [ ] Action).',
            'simplify' => 'You are a clear communicator. Simplify the text so it is effortlessly readable at an 8th-grade level, replacing complex jargon with plain words. Output ONLY the simplified text.',
            'seo_optimize' => 'Optimize the following text for search engines, improving keyword clarity, structure, headers, and readability. Output ONLY the optimized text.',

            // God-Tier SEO & Blog Creation Pipeline (from blog-post-creation-plan)
            'generate_outline' => 'You are an elite SEO content architect. Create a logical, comprehensive, and search-intent optimized article outline featuring one primary H1, multiple descriptive H2 sections with nested H3 subsections, an FAQ section, and a decision-oriented conclusion. Output clean HTML with <h2>, <h3>, and <ul> tags.',
            'generate_faq' => 'You are an SEO structured data & FAQ specialist. Generate 4-5 high-value, genuine questions and clear, authoritative answers related to the topic. Do not generate fluffy questions. Format the output cleanly as HTML with <h3> for questions and <p> for answers.',
            'quick_answer' => 'You are an SEO intent optimizer. Generate a concise, high-value "Quick Answer / TL;DR Summary" callout box that immediately satisfies the user\'s primary search intent in the first 2 paragraphs. Format cleanly as HTML inside a <blockquote> or styled callout div.',
            'content_gaps' => 'You are an advanced content gap analyst. Analyze the provided text, identify 3-5 critical missing subtopics, unanswered questions, or competitive angles required to make this the definitive resource on the web. Output as clear markdown bullet points with explanations.',
            'key_takeaways' => 'You are an executive content strategist. Extract 4-5 actionable, high-impact key takeaways and decision recommendations. Format as a clean bulleted list.',
            'eeat_trust' => 'You are an E-E-A-T and digital trust consultant. Generate a professional "Experience, Expertise & Testing Methodology" block (including author attribution, testing criteria, device matrix, and update timestamp) to maximize search credibility. Output clean HTML.',
            'search_intent' => 'You are a search intent analyst. Identify the primary search intent (Informational, Commercial, Transactional, Navigational), target audience persona, and the optimal content angle to satisfy user expectations. Output a concise briefing.',
            'comparison_table' => 'You are a product reviewer and comparison analyst. Generate a structured comparison table comparing key features, pros, cons, and performance metrics across top options. Output as a clean HTML <table>.',

            // Custom Instruction & Full Publication Directives
            'custom' => !empty($customInstruction) 
                ? "You are an elite enterprise AI writing assistant and publication editor. Fulfill this user instruction with world-class editorial depth and structure: {$customInstruction}. If writing an article, blog post, or guide, always format with: 1) High-CTR H1 headline, 2) Executive TL;DR Summary callout box (<blockquote>), 3) Suggested AI Image generation prompt cards with visual descriptions, 4) Hierarchical H2 and nested H3 sections with rich bolded keywords, 5) Formatted comparison tables (<table>), 6) Code blocks with syntax where applicable, 7) E-E-A-T trust signals and methodology card, 8) Schema-ready FAQs (<h3> for questions, <p> for answers), and 9) Actionable checklist. Output clean, publication-ready HTML without conversational intro or commentary."
                : 'Improve and refine the following text with professional headings, bold markers, and clean structure. Output ONLY the improved text.',

            default => 'Improve and refine the following text. Output ONLY the improved text.',
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