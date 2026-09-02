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

namespace App\Features\AI\Http\Controllers;

use App\Core\Exceptions\AiProviderDownException;
use App\Core\Exceptions\AiRateLimitException;
use App\Core\Exceptions\AiTokenLimitException;
use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\AI\Actions\TransformText;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Services\AiCircuitBreaker;
use App\Features\AI\Services\AiRateLimiterService;
use App\Features\AI\Services\OmniRouteClient;
use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\Documents\Models\Document;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WordPressBridgeController extends Controller
{
    /**
     * Connect & Handshake Verification
     * Validates the user's Studio Connect Token and returns live quota, profile, and available features.
     */
    public function connect(Request $request): JsonResponse
    {
        $user = Auth::user();

        $remainingWords = max(0, (int) $user->monthly_word_quota - (int) $user->used_word_quota);
        $pct = $user->monthly_word_quota > 0 
            ? min(100, round(($user->used_word_quota / $user->monthly_word_quota) * 100))
            : 0;

        $models = AiModel::where('is_active', true)
            ->with('provider:id,name,slug')
            ->select('id', 'ai_provider_id', 'name', 'model_id', 'context_window')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'model_id' => $m->model_id,
                'provider' => $m->provider?->name ?? 'Default',
                'context_window' => $m->context_window,
            ]);

        $brandVoices = BrandProfile::where('user_id', $user->id)
            ->select('id', 'name', 'tone_description', 'target_audience', 'guidelines')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'tone' => $b->tone_description,
                'audience' => $b->target_audience,
                'style_guide' => $b->guidelines,
            ]);

        return response()->json([
            'success' => true,
            'status' => 'connected',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan ?? 'Starter',
                'quota' => [
                    'monthly_limit' => (int) $user->monthly_word_quota,
                    'used_words' => (int) $user->used_word_quota,
                    'remaining_words' => $remainingWords,
                    'percentage_used' => $pct,
                ],
                'preferences' => $user->preferences ?? [],
            ],
            'available_models' => $models,
            'brand_voices' => $brandVoices,
            'server_time' => now()->toIso8601String(),
            'protocol_version' => '2.6.0',
        ]);
    }

    /**
     * Live SSE Streaming Transformation API for WordPress TipTap & Gutenberg Blocks
     */
    public function stream(Request $request, TransformText $action, OmniRouteClient $client, RecordGenerationUsage $recordUsage, AiCircuitBreaker $breaker, AiRateLimiterService $limiter): StreamedResponse
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
            'text' => 'required|string',
            'type' => 'required|string',
            'model' => 'nullable|string',
            'custom_instruction' => 'nullable|string',
            'context' => 'nullable|array',
            'brand_voice_id' => 'nullable|integer',
        ]);

        $promptData = $action->buildPrompt(
            $validated['text'],
            $validated['type'],
            $validated['custom_instruction'] ?? null,
            $validated['context'] ?? []
        );

        $model = $validated['model'] ?? ($user->preferences['default_model'] ?? null);

        // Fetch brand voice if specified
        if (!empty($validated['brand_voice_id'])) {
            $brand = BrandProfile::where('id', $validated['brand_voice_id'])->where('user_id', $user->id)->first();
            if ($brand) {
                $promptData['system'] .= "\nApply Brand Voice: " . $brand->name . " (Tone: " . ($brand->tone_description ?? 'Professional') . ", Audience: " . ($brand->target_audience ?? 'General') . ")";
            }
        }

        return response()->stream(function () use ($client, $promptData, $model, $user, $recordUsage, $validated) {
            // Disable output buffering
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
    public function transform(Request $request, TransformText $action, RecordGenerationUsage $recordUsage, AiCircuitBreaker $breaker, AiRateLimiterService $limiter): JsonResponse
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
    public function syncDocument(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_html' => 'required|string',
            'wp_post_id' => 'nullable|integer',
            'document_id' => 'nullable|integer',
        ]);

        if (!empty($validated['document_id'])) {
            $doc = Document::where('id', $validated['document_id'])->where('user_id', $user->id)->first();
        } else {
            $doc = new Document();
            $doc->user_id = $user->id;
            $doc->status = 'draft';
            $doc->slug = \Illuminate\Support\Str::slug($validated['title']) . '-' . \Illuminate\Support\Str::random(6);
        }

        if ($doc) {
            $doc->title = $validated['title'];
            $doc->word_count = str_word_count(strip_tags($validated['content_html']));
            $doc->character_count = strlen(strip_tags($validated['content_html']));
            $doc->reading_time_minutes = max(1, (int) ceil($doc->word_count / 200));
            $doc->save();

            // Persist into document_contents
            $content = $doc->content()->firstOrCreate(['document_id' => $doc->id]);
            $content->content_html = $validated['content_html'];
            $content->content_plain = strip_tags($validated['content_html']);
            $content->save();

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'word_count' => $doc->word_count,
                    'updated_at' => $doc->updated_at->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Document not found or access denied.',
        ], 404);
    }
}
