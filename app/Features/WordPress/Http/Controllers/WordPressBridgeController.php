<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Bridge API Controller
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

namespace App\Features\WordPress\Http\Controllers;

use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\AI\Actions\TransformText;
use App\Features\AI\Services\AiCircuitBreaker;
use App\Features\AI\Services\AiRateLimiterService;
use App\Features\AI\Services\OmniRouteClient;
use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\Usage\Services\QuotaManager;
use App\Features\WordPress\Actions\SyncWordPressDocument;
use App\Features\WordPress\Actions\VerifyWordPressHandshake;
use App\Features\WordPress\Services\WordPressPluginService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WordPressBridgeController extends Controller
{
    /**
     * Connect & Handshake Verification
     * Validates the user's Studio Connect Token and returns live quota, profile, and available features.
     */
    public function connect(Request $request, VerifyWordPressHandshake $handshake): JsonResponse
    {
        $user = Auth::user();
        $payload = $handshake->execute($user);

        return response()->json($payload);
    }

    /**
     * Live SSE Streaming Transformation API for WordPress TipTap & Gutenberg Blocks
     */
    public function stream(
        Request $request, 
        TransformText $action, 
        OmniRouteClient $client, 
        RecordGenerationUsage $recordUsage, 
        AiCircuitBreaker $breaker, 
        AiRateLimiterService $limiter
    ): StreamedResponse {
        if ($breaker->isTripped()) {
            return response()->stream(function () use ($breaker) {
                $status = $breaker->getStatus();
                $msg = 'AI Gateway Paused: ' . ($status['reason'] ?? 'Circuit breaker active');
                echo "event: error\ndata: " . json_encode(['error' => $msg, 'message' => $msg, 'done' => true]) . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
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
                $msg = $rateCheck['reason'] ?? 'Too many requests. Please slow down.';
                echo "event: error\ndata: " . json_encode(['error' => $msg, 'message' => $msg, 'done' => true]) . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $quotaManager = app(QuotaManager::class);
        if (!$quotaManager->hasQuota($user, 1)) {
            return response()->stream(function () {
                $msg = 'Monthly AI word quota exhausted. Please upgrade your HOA Studio plan or configure a personal API key in HOA Studio Settings.';
                echo "data: " . json_encode(['error' => $msg, 'message' => $msg, 'done' => true]) . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $validated = $request->validate([
            'text' => 'nullable|string',
            'type' => 'nullable|string',
            'model' => 'nullable|string',
            'custom_instruction' => 'nullable|string',
            'context' => 'nullable|array',
            'brand_voice_id' => 'nullable|integer',
        ]);

        $text = $validated['text'] ?? $validated['custom_instruction'] ?? '';
        $customInstruction = $validated['custom_instruction'] ?? null;
        $type = (!empty($validated['type']) && $validated['type'] !== 'undefined') ? $validated['type'] : 'generate';
        $context = $validated['context'] ?? [];

        if (trim($text) === '' && empty($customInstruction)) {
            return response()->stream(function () {
                $msg = 'Please provide a prompt instruction or select text in the editor to transform.';
                echo "data: " . json_encode(['error' => $msg, 'message' => $msg, 'done' => true]) . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $promptData = $action->buildPrompt(
            $text,
            $type,
            $customInstruction,
            $context
        );

        $model = (!empty($validated['model']) && $validated['model'] !== 'auto') 
            ? $validated['model'] 
            : ($user->preferences['default_model'] ?? null);

        // Fetch brand voice if specified
        if (!empty($validated['brand_voice_id'])) {
            $brand = BrandProfile::where('id', $validated['brand_voice_id'])->where('user_id', $user->id)->first();
            if ($brand) {
                $promptData['system'] .= "\nApply Brand Voice: " . $brand->name . " (Tone: " . ($brand->tone_description ?? 'Professional') . ", Audience: " . ($brand->target_audience ?? 'General') . ")";
            }
        }

        return response()->stream(function () use ($client, $promptData, $model, $user, $recordUsage, $validated) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();

            $accumulated = '';
            $tokenCount = 0;
            $routedModel = $model ?? 'auto';

            try {
                $stream = $client->streamChatCompletion([
                    ['role' => 'system', 'content' => $promptData['system']],
                    ['role' => 'user', 'content' => $promptData['user']],
                ], [
                    'model' => $model,
                    'temperature' => 0.7,
                ]);

                foreach ($stream as $chunk) {
                    if (connection_aborted()) {
                        break;
                    }

                    $delta = $chunk['token'] ?? $chunk['text'] ?? $chunk['delta'] ?? '';
                    if (!empty($chunk['model'])) {
                        $routedModel = $chunk['model'];
                    }

                    if ($delta !== '') {
                        $accumulated .= $delta;
                        $tokenCount += max(1, (int) ceil(strlen($delta) / 4));

                        echo "data: " . json_encode([
                            'delta' => $delta,
                            'done' => false,
                            'model' => $routedModel,
                        ]) . "\n\n";

                        flush();
                    }
                }

                if (trim($accumulated) === '') {
                    echo "data: " . json_encode([
                        'error' => 'The AI model returned an empty response. Please verify that your AI provider and model are accessible, or rephrase your prompt.',
                        'message' => 'The AI model returned an empty response. Please verify that your AI provider and model are accessible, or rephrase your prompt.',
                        'done' => true,
                    ]) . "\n\n";
                    flush();
                    return;
                }

                // Record Word Quota Usage & Telemetry
                $wordsUsed = max(1, str_word_count(strip_tags($accumulated)));
                $recordUsage->execute($user, [
                    'words_used' => $wordsUsed,
                    'tokens_used' => $tokenCount,
                    'model_slug' => $routedModel,
                ]);

                echo "data: " . json_encode([
                    'done' => true,
                    'words' => $wordsUsed,
                    'tokens' => $tokenCount,
                    'model' => $routedModel,
                    'quota_remaining' => max(0, $user->monthly_word_quota - $user->used_word_quota),
                ]) . "\n\n";

                flush();

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('WordPress Stream Error: ' . $e->getMessage());
                echo "data: " . json_encode([
                    'error' => 'AI Generation Error: ' . $e->getMessage(),
                    'message' => 'AI Generation Error: ' . $e->getMessage(),
                    'done' => true,
                ]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Synchronous Transformation Action for WordPress
     */
    public function transform(
        Request $request, 
        TransformText $action, 
        RecordGenerationUsage $recordUsage, 
        AiCircuitBreaker $breaker, 
        AiRateLimiterService $limiter
    ): JsonResponse {
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
            ], 429);
        }

        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|string',
            'model' => 'nullable|string',
            'custom_instruction' => 'nullable|string',
            'context' => 'nullable|array',
        ]);

        try {
            $result = $action->execute($user, $validated['text'], $validated['type'], [
                'model' => $validated['model'] ?? null,
                'custom_instruction' => $validated['custom_instruction'] ?? null,
                'context' => $validated['context'] ?? [],
            ]);

            $words = max(1, str_word_count(strip_tags($result)));
            $user->consumeQuota($words);

            return response()->json([
                'success' => true,
                'result' => $result,
                'type' => $validated['type'],
                'word_count' => $words,
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
     * Document Synchronization Endpoint
     * Push or pull documents between WordPress posts and HOA Studio documents.
     */
    public function syncDocument(Request $request, SyncWordPressDocument $action): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_html' => 'required|string',
            'wp_post_id' => 'nullable|integer',
            'document_id' => 'nullable|integer',
        ]);

        try {
            $doc = $action->execute($user, $validated);

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'word_count' => $doc->word_count,
                    'updated_at' => $doc->updated_at->toIso8601String(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Document not found or access denied: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Download Latest WordPress Plugin ZIP Distribution Archive
     */
    public function downloadPlugin(WordPressPluginService $pluginService): BinaryFileResponse
    {
        return $pluginService->downloadResponse();
    }
}
