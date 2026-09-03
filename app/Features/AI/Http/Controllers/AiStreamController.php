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

namespace App\Features\AI\Http\Controllers;

use App\Core\Exceptions\AiProviderDownException;
use App\Core\Exceptions\AiRateLimitException;
use App\Core\Exceptions\AiTokenLimitException;
use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\AI\Actions\TransformText;
use App\Features\AI\Services\AiCircuitBreaker;
use App\Features\AI\Services\AiRateLimiterService;
use App\Features\AI\Services\ContentWriterBrain;
use App\Features\AI\Services\OmniRouteClient;
use App\Features\AI\Services\OmniRouteUrlResolver;
use App\Features\Auth\Models\UserApiKey;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiStreamController extends Controller
{
    /**
     * Contextual Transformation API (Synchronous JSON)
     */
    public function transform(Request $request, TransformText $action, AiCircuitBreaker $breaker, AiRateLimiterService $limiter): JsonResponse
    {
        if ($breaker->isTripped()) {
            return response()->json([
                'success' => false,
                'error' => 'AI Gateway Paused: ' . $breaker->getStatus()['reason'],
            ], 503);
        }

        $user = Auth::user();
        $rateCheck = $limiter->checkRateLimit($user);

        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'error' => $rateCheck['reason'],
                'retry_after' => $rateCheck['retry_after'],
            ], 429);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:50000',
            'type' => 'required|string|max:100',
            'custom_instruction' => 'nullable|string|max:1000',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        $context = $request->input('context', []);

        try {
            $result = $action->execute($user, $validated['text'], $validated['type'], [
                'model' => $validated['model'] ?? null,
                'custom_instruction' => $validated['custom_instruction'] ?? null,
                'temperature' => $validated['temperature'] ?? 0.7,
                'context' => $context,
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
                'type' => $validated['type'],
                'word_count' => str_word_count(strip_tags($result)),
                'quota_remaining' => max(0, $user->monthly_word_quota - $user->used_word_quota),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Live SSE Streaming Contextual Transformation API
     */
    public function streamTransform(
        Request $request, 
        TransformText $action, 
        OmniRouteClient $client, 
        RecordGenerationUsage $recordUsage, 
        AiCircuitBreaker $breaker, 
        AiRateLimiterService $limiter,
        ContentWriterBrain $brain
    ): StreamedResponse
    {
        if ($breaker->isTripped()) {
            return response()->stream(function () use ($breaker) {
                echo "event: error\ndata: " . json_encode(['message' => 'AI Gateway Paused: ' . $breaker->getStatus()['reason']]) . "\n\n";
                ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $user = Auth::user();
        $rateCheck = $limiter->checkRateLimit($user);

        if (!$rateCheck['allowed']) {
            return response()->stream(function () use ($rateCheck) {
                echo "event: error\ndata: " . json_encode(['message' => $rateCheck['reason']]) . "\n\n";
                ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:50000',
            'type' => 'required|string|max:100',
            'custom_instruction' => 'nullable|string|max:1000',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        $user = Auth::user();

        if (!$user->hasQuota(1)) {
            return response()->stream(function () {
                echo "event: error\ndata: " . json_encode(['message' => 'Monthly word quota exceeded. Please upgrade plan.']) . "\n\n";
                ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $context = $request->input('context', []);
        if (!isset($context['selected_text']) && !isset($context['target_text'])) {
            $context['target_text'] = $validated['text'];
        }
        $pipelineStages = $request->input('pipeline_stages', []);
        $isFullArticle = $brain->isFullArticleType($validated['type'], $validated['custom_instruction'] ?? null, $context);
        $fullDocText = $context['full_document_text'] ?? null;
        $targetKeyword = $context['target_keyword'] ?? null;
        $docTitle = $context['document_title'] ?? null;

        if (!$isFullArticle) {
            $brainPrompt = $brain->buildSurgicalPrompt(
                $validated['type'],
                $context,
                $validated['custom_instruction'] ?? null
            );
            $systemPrompt = $brainPrompt['system'];
            $userContent = $brainPrompt['user'];
        } else {
            // Enterprise 15-Stage Production Pipeline with Content Writer Brain & Memory
            $brainPrompt = $brain->buildPipelineArticlePrompt(
                $validated['text'],
                $context,
                $pipelineStages,
                $validated['custom_instruction'] ?? null
            );
            $systemPrompt = $brainPrompt['system'];
            $userContent = $brainPrompt['user'];

            // Ground with Knowledge Base RAG context if available for full generation
            try {
                $ragAction = app(\App\Features\KnowledgeBase\Actions\RetrieveRagContext::class);
                $ragResult = $ragAction->execute($user, $userContent, limit: 3);
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

        return response()->stream(function () use ($client, $messages, $validated, $user, $recordUsage, $hasSelection, $context) {
            $accumulated = '';
            $routedModel = $validated['model'] ?? config('omniroute.default_model', 'auto');

            // Disable PHP output buffering entirely for zero-latency SSE token delivery
            @ob_implicit_flush(true);
            while (ob_get_level() > 0) { ob_end_flush(); }

            $streamOptions = [
                'model' => $routedModel,
                'temperature' => (float) ($validated['temperature'] ?? 0.75),
            ];

            if ($hasSelection) {
                $selLen = mb_strlen($context['selected_text'] ?? '');
                if (in_array($validated['type'], ['expand', 'generate_faq', 'key_takeaways', 'eeat_trust'])) {
                    $streamOptions['max_tokens'] = max(350, min(1200, (int) ceil($selLen * 2.0)));
                } else {
                    $streamOptions['max_tokens'] = max(200, min(650, (int) ceil($selLen * 1.5)));
                }
            }

            try {
                $generator = $client->streamChatCompletion($messages, $streamOptions);

                foreach ($generator as $chunk) {
                    $token = is_array($chunk) ? ($chunk['token'] ?? '') : (string) $chunk;
                    $accumulated .= $token;
                    
                    if (is_array($chunk) && isset($chunk['model'])) {
                        $routedModel = $chunk['model'];
                    }

                    echo "event: token\n";
                    echo "data: " . json_encode(['token' => $token, 'model' => $routedModel]) . "\n\n";

                    flush();
                }

                $words = max(1, str_word_count(strip_tags($accumulated)));
                $recordUsage->execute($user, [
                    'words_used' => $words,
                    'tokens_used' => (int) ceil(mb_strlen($accumulated) / 4),
                    'model_slug' => $routedModel,
                ]);

                echo "event: complete\n";
                echo "data: " . json_encode([
                    'done' => true,
                    'result' => $accumulated,
                    'words_used' => $words,
                    'quota_remaining' => max(0, $user->monthly_word_quota - $user->used_word_quota),
                ]) . "\n\n";

                flush();
            } catch (AiTokenLimitException $e) {
                echo "event: error\ndata: " . json_encode(['message' => 'Token limit exceeded.']) . "\n\n";
            } catch (AiProviderDownException $e) {
                echo "event: error\ndata: " . json_encode(['message' => 'Provider unavailable.']) . "\n\n";
            } catch (Exception $e) {
                echo "event: error\n";
                echo "data: " . json_encode(['message' => $e->getMessage()]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Standard Server-Sent Events (SSE) Live Token Streaming API
     */
    public function stream(Request $request, OmniRouteClient $client, RecordGenerationUsage $recordUsage): StreamedResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:20000',
            'system_prompt' => 'nullable|string|max:5000',
            'model' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        $user = Auth::user();

        if (!$user->hasQuota(1)) {
            return response()->stream(function () {
                echo "event: error\ndata: " . json_encode(['message' => 'Quota exceeded']) . "\n\n";
                ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
            ]);
        }

        $messages = [];
        if (!empty($validated['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $validated['system_prompt']];
        }
        $messages[] = ['role' => 'user', 'content' => $validated['prompt']];

        return response()->stream(function () use ($client, $messages, $validated, $user, $recordUsage) {
            $accumulated = '';
            $routedModel = $validated['model'] ?? config('omniroute.default_model', 'auto');

            // Disable PHP output buffering entirely for zero-latency SSE token delivery
            @ob_implicit_flush(true);
            while (ob_get_level() > 0) { ob_end_flush(); }

            try {
                $generator = $client->streamChatCompletion($messages, [
                    'model' => $routedModel,
                    'temperature' => (float) ($validated['temperature'] ?? 0.7),
                ]);

                foreach ($generator as $chunk) {
                    $token = is_array($chunk) ? ($chunk['token'] ?? '') : (string) $chunk;
                    $accumulated .= $token;
                    
                    if (is_array($chunk) && isset($chunk['model'])) {
                        $routedModel = $chunk['model'];
                    }

                    echo "event: token\n";
                    echo "data: " . json_encode(['token' => $token, 'model' => $routedModel]) . "\n\n";
                    flush();
                }

                $words = max(1, str_word_count(strip_tags($accumulated)));
                $recordUsage->execute($user, [
                    'words_used' => $words,
                    'tokens_used' => (int) ceil(mb_strlen($accumulated) / 4),
                    'model_slug' => $routedModel,
                ]);

                echo "event: complete\n";
                echo "data: " . json_encode([
                    'done' => true,
                    'words_used' => $words,
                    'quota_remaining' => max(0, $user->monthly_word_quota - $user->used_word_quota),
                ]) . "\n\n";

                flush();
            } catch (AiTokenLimitException $e) {
                echo "event: error\ndata: " . json_encode(['message' => 'Token limit exceeded.']) . "\n\n";
            } catch (AiProviderDownException $e) {
                echo "event: error\ndata: " . json_encode(['message' => 'Provider unavailable.']) . "\n\n";
            } catch (Exception $e) {
                echo "event: error\n";
                echo "data: " . json_encode(['message' => $e->getMessage()]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Prepare Brain Context & RAG Grounded Prompt for Hybrid Routing
     */
    public function preparePrompt(
        Request $request,
        ContentWriterBrain $brain,
        AiCircuitBreaker $breaker,
        AiRateLimiterService $limiter
    ): JsonResponse {
        if ($breaker->isTripped()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Gateway Paused: ' . $breaker->getStatus()['reason']
            ], 503);
        }

        $user = Auth::user();
        $rateCheck = $limiter->checkRateLimit($user);
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['reason']
            ], 429);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:50000',
            'type' => 'required|string|max:100',
            'custom_instruction' => 'nullable|string|max:1000',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'context' => 'nullable|array',
            'pipeline_stages' => 'nullable|array',
        ]);

        if (!$user->hasQuota(1)) {
            return response()->json([
                'success' => false,
                'message' => 'Monthly word quota exceeded. Please upgrade plan.'
            ], 402);
        }

        $context = $request->input('context', []);
        if (!isset($context['selected_text']) && !isset($context['target_text'])) {
            $context['target_text'] = $validated['text'];
        }
        $pipelineStages = $request->input('pipeline_stages', []);
        $isFullArticle = $brain->isFullArticleType($validated['type'], $validated['custom_instruction'] ?? null, $context);

        if (!$isFullArticle) {
            $brainPrompt = $brain->buildSurgicalPrompt(
                $validated['type'],
                $context,
                $validated['custom_instruction'] ?? null
            );
            $systemPrompt = $brainPrompt['system'];
            $userContent = $brainPrompt['user'];
        } else {
            $brainPrompt = $brain->buildPipelineArticlePrompt(
                $validated['text'],
                $context,
                $pipelineStages,
                $validated['custom_instruction'] ?? null
            );
            $systemPrompt = $brainPrompt['system'];
            $userContent = $brainPrompt['user'];

            // Ground with Knowledge Base RAG context if available
            try {
                $ragAction = app(\App\Features\KnowledgeBase\Actions\RetrieveRagContext::class);
                $ragResult = $ragAction->execute($user, $userContent, limit: 3);
                if (!empty($ragResult['has_context']) && !empty($ragResult['prompt_snippet'])) {
                    $systemPrompt .= "\n\n" . $ragResult['prompt_snippet'];
                }
            } catch (\Throwable $e) {
                // Non-blocking fallback
            }
        }

        // Determine user Gateway endpoint and BYOK key
        $userKeyRow = UserApiKey::where('user_id', $user->id)
            ->where('provider_slug', 'omniroute')
            ->first();

        $userCustomUrl = $userKeyRow ? $userKeyRow->custom_base_url : null;
        $userApiKey = $userKeyRow ? $userKeyRow->getRawKeyForOwner($user) : null;

        $endpoints = OmniRouteUrlResolver::resolve($userCustomUrl);
        $isLocal = !$endpoints['is_remote'];

        $resolvedApiKey = $userApiKey ?: config('omniroute.api_key', 'omniroute-default-key');
        $routedModel = $validated['model'] ?? config('omniroute.default_model', 'auto');

        return response()->json([
            'success' => true,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContent],
            ],
            'model' => $routedModel,
            'temperature' => (float) ($validated['temperature'] ?? 0.75),
            'has_selection' => $hasSelection,
            'routing' => [
                'mode' => $isLocal ? 'browser_direct' : 'server_proxy',
                'is_local' => $isLocal,
                'gateway_url' => $endpoints['openai_base'],
                'chat_completions_url' => $isLocal ? 'http://127.0.0.1:20128/v1/chat/completions' : $endpoints['chat_completions_endpoint'],
                'api_key' => $resolvedApiKey,
            ],
            'quota_remaining' => max(0, $user->monthly_word_quota - $user->used_word_quota),
        ]);
    }

    /**
     * Record Telemetry & Consume Quota after Browser-Direct Stream
     */
    public function recordUsage(Request $request, RecordGenerationUsage $recordUsage): JsonResponse
    {
        $validated = $request->validate([
            'tokens' => 'nullable|integer|min:0',
            'words' => 'required|integer|min:1',
            'model' => 'nullable|string|max:100',
            'latency_ms' => 'nullable|integer|min:0',
            'document_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $words = max(1, (int) $validated['words']);
        $tokens = (int) ($validated['tokens'] ?? round($words * 1.33));
        $model = $validated['model'] ?? 'omniroute-direct';

        try {
            $recordUsage->execute($user, [
                'words_used' => $words,
                'tokens_used' => $tokens,
                'model_slug' => $model,
            ]);
        } catch (\Throwable $e) {
            Log::warning("[AiStreamController::recordUsage] Failed to record usage: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'words_deducted' => $words,
            'quota_remaining' => max(0, $user->fresh()->monthly_word_quota - $user->fresh()->used_word_quota),
        ]);
    }
}