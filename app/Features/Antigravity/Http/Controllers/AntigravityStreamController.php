<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Stream Controller
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

namespace App\Features\Antigravity\Http\Controllers;

use App\Features\AI\Models\AiModel;
use App\Features\Antigravity\Services\AntigravityGatewayService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AntigravityStreamController extends Controller
{
    protected AntigravityGatewayService $gateway;

    public function __construct(AntigravityGatewayService $gateway)
    {
        $this->gateway = $gateway;
    }

    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:50000',
            'system_prompt' => 'nullable|string|max:10000',
            'model' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        $user = $request->user();
        if (! $user->hasQuota(1)) {
            return response()->stream(function () {
                echo "event: error\ndata: " . json_encode(['message' => 'Monthly word quota exceeded. Please upgrade plan.']) . "\n\n";
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $messages = [];
        if (! empty($validated['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $validated['system_prompt']];
        }
        $messages[] = ['role' => 'user', 'content' => $validated['prompt']];

        $options = [
            'model' => $validated['model'] ?? 'gemini-1.5-flash',
            'temperature' => $validated['temperature'] ?? 0.7,
        ];

        return response()->stream(function () use ($user, $messages, $options) {
            ob_implicit_flush(1);
            $accumulated = '';

            try {
                $generator = $this->gateway->streamChatCompletion($user, $messages, $options);

                foreach ($generator as $tokenData) {
                    $chunk = $tokenData['chunk'] ?? '';
                    $accumulated .= $chunk;

                    echo "data: " . json_encode([
                        'chunk' => $chunk,
                        'done' => false,
                        'model' => $tokenData['model'] ?? $options['model'],
                    ]) . "\n\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                $words = max(1, str_word_count(strip_tags($accumulated)));
                echo "event: complete\n";
                echo "data: " . json_encode([
                    'done' => true,
                    'result' => $accumulated,
                    'words_used' => $words,
                    'quota_remaining' => max(0, $user->monthly_word_quota - $user->used_word_quota),
                ]) . "\n\n";

                flush();

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
}
