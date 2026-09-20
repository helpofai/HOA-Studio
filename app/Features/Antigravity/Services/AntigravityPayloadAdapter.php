<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Payload Adapter
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

namespace App\Features\Antigravity\Services;

class AntigravityPayloadAdapter
{
    /**
     * Convert OpenAI-style chat completion messages array and options
     * into Google Generative Language API (Gemini) payload structure.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function formatToGooglePayload(array $messages, array $options = []): array
    {
        $systemText = '';
        $contents = [];

        foreach ($messages as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';

            if (empty(trim($content))) {
                continue;
            }

            if ($role === 'system') {
                $systemText .= ($systemText !== '' ? "\n\n" : '') . trim($content);
                continue;
            }

            // Map OpenAI roles to Google Gemini roles ('user' or 'model')
            $geminiRole = ($role === 'assistant') ? 'model' : 'user';

            // Merge with previous element if consecutive same role
            $lastIndex = count($contents) - 1;
            if ($lastIndex >= 0 && $contents[$lastIndex]['role'] === $geminiRole) {
                $contents[$lastIndex]['parts'][0]['text'] .= "\n\n" . $content;
            } else {
                $contents[] = [
                    'role' => $geminiRole,
                    'parts' => [
                        ['text' => $content],
                    ],
                ];
            }
        }

        // Ensure at least one content exists
        if (empty($contents)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => 'Hello'],
                ],
            ];
        }

        $payload = [
            'contents' => $contents,
        ];

        if (! empty($systemText)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemText],
                ],
            ];
        }

        // Generation Config
        $generationConfig = [];
        if (isset($options['temperature'])) {
            $generationConfig['temperature'] = (float) $options['temperature'];
        }
        if (isset($options['max_tokens']) || isset($options['max_output_tokens'])) {
            $generationConfig['maxOutputTokens'] = (int) ($options['max_tokens'] ?? $options['max_output_tokens']);
        }
        if (isset($options['top_p'])) {
            $generationConfig['topP'] = (float) $options['top_p'];
        }
        if (isset($options['response_format']) && is_array($options['response_format'])) {
            if (($options['response_format']['type'] ?? '') === 'json_object') {
                $generationConfig['responseMimeType'] = 'application/json';
            }
        }

        if (! empty($generationConfig)) {
            $payload['generationConfig'] = $generationConfig;
        }

        return $payload;
    }

    /**
     * Extract generated content text from Google Generative Language API response.
     */
    public function extractTextFromResponse(array $responseJson): string
    {
        $candidates = $responseJson['candidates'] ?? [];
        if (empty($candidates)) {
            return '';
        }

        $parts = $candidates[0]['content']['parts'] ?? [];
        $text = '';
        foreach ($parts as $part) {
            $text .= $part['text'] ?? '';
        }

        return $text;
    }

    /**
     * Extract token metrics from Google Generative Language API response.
     *
     * @return array{prompt_tokens: int, completion_tokens: int, total_tokens: int}
     */
    public function extractUsageFromResponse(array $responseJson, string $generatedText = ''): array
    {
        $usageMetadata = $responseJson['usageMetadata'] ?? [];

        $promptTokens = $usageMetadata['promptTokenCount'] ?? 100;
        $completionTokens = $usageMetadata['candidatesTokenCount'] ?? (int) ceil(mb_strlen($generatedText) / 4);
        $totalTokens = $usageMetadata['totalTokenCount'] ?? ($promptTokens + $completionTokens);

        return [
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
        ];
    }

    /**
     * Extract text chunk from parsed SSE stream payload object.
     */
    public function extractChunkText(array $chunkJson): ?string
    {
        $candidates = $chunkJson['candidates'] ?? [];
        if (empty($candidates)) {
            return null;
        }

        $parts = $candidates[0]['content']['parts'] ?? [];
        $text = '';
        foreach ($parts as $part) {
            $text .= $part['text'] ?? '';
        }

        return $text !== '' ? $text : null;
    }

    /**
     * Extract clean model slug suitable for Google API endpoint.
     * (e.g., 'antigravity/gemini-1.5-pro' -> 'gemini-1.5-pro')
     */
    public function sanitizeModelSlug(string $modelId): string
    {
        $slug = preg_replace('#^antigravity/#', '', $modelId);
        return preg_replace('#^models/#', '', $slug);
    }
}
