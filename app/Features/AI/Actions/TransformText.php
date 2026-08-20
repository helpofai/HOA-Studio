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

            // Custom Instruction
            'custom' => !empty($customInstruction) 
                ? "You are an AI writing assistant. Modify the provided text strictly according to this user instruction: {$customInstruction}. Output ONLY the resulting text without conversational intro or commentary."
                : 'Improve and refine the following text. Output ONLY the improved text.',

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