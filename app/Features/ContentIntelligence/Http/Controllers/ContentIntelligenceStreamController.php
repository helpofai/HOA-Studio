<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Intelligence Stream Controller
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

namespace App\Features\ContentIntelligence\Http\Controllers;

use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Pipeline\ContentWorkflowEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ContentIntelligenceStreamController extends Controller
{
    /**
     * Stream a single workflow step with real-time SSE token telemetry.
     */
    public function streamStep(Request $request, int $runId): StreamedResponse
    {
        $user = Auth::user();

        return new StreamedResponse(function () use ($user, $runId) {
            // Disable output buffering
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            ob_implicit_flush(1);

            $sendEvent = function (array $data, ?string $event = null) {
                if ($event) {
                    echo "event: {$event}\n";
                }
                echo 'data: '.json_encode($data)."\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();
            };

            if (! $user) {
                $sendEvent(['message' => 'Unauthorized access.'], 'error');
                return;
            }

            if (! $user->hasQuota(1)) {
                $sendEvent(['message' => 'Monthly word quota exceeded. Please upgrade your plan.'], 'error');
                return;
            }

            $run = WorkflowRun::with(['mission', 'nodes'])
                ->where('id', $runId)
                ->where('user_id', $user->id)
                ->first();

            if (! $run) {
                $sendEvent(['message' => 'Workflow run not found.'], 'error');
                return;
            }

            if ($run->status === ContentWorkflowStatus::COMPLETED) {
                $sendEvent([
                    'type' => 'already_completed',
                    'message' => 'Workflow is already fully completed.',
                    'status' => 'completed',
                    'document_id' => $run->document_id,
                    'done' => true,
                ]);
                return;
            }

            $currentNode = $run->current_node;
            $sendEvent([
                'type' => 'node_start',
                'run_id' => $run->id,
                'node' => $currentNode,
                'topic' => $run->mission->topic ?? 'Untitled Mission',
                'message' => "Executing Synapse Node: {$currentNode}",
            ]);

            try {
                $engine = new ContentWorkflowEngine;
                $startTime = microtime(true);
                $stepResult = $engine->step($run);
                $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

                $freshRun = $run->fresh(['nodes', 'drafts']);
                $completedCount = $freshRun->nodes->count();
                $progressPercent = min(100, (int) round(($completedCount / 10) * 100));

                $isSuccess = $stepResult['result']->isSuccess();
                $lastNode = $stepResult['result']->outputPayload['last_completed_node'] ?? $currentNode;

                // Stream token preview if section drafts or content generated
                $drafts = $freshRun->drafts;
                $latestDraft = $drafts->sortByDesc('id')->first();
                $previewSnippet = '';

                if ($latestDraft && ! empty($latestDraft->body_html)) {
                    $cleanText = strip_tags($latestDraft->body_html);
                    $previewSnippet = mb_substr($cleanText, 0, 300);
                } elseif (! empty($stepResult['result']->outputPayload)) {
                    $payloadStr = json_encode($stepResult['result']->outputPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    $previewSnippet = mb_substr($payloadStr, 0, 300);
                }

                if (! empty($previewSnippet)) {
                    // Typewriter token chunks
                    $words = explode(' ', $previewSnippet);
                    $chunk = '';
                    foreach ($words as $i => $word) {
                        $chunk .= $word . ' ';
                        if ($i % 6 === 0 || $i === count($words) - 1) {
                            $sendEvent([
                                'type' => 'token',
                                'chunk' => $chunk,
                                'node' => $lastNode,
                            ]);
                            $chunk = '';
                            usleep(25000); // 25ms delay for visual stream effect
                        }
                    }
                }

                $sendEvent([
                    'type' => 'node_complete',
                    'success' => $isSuccess,
                    'node' => $lastNode,
                    'latency_ms' => $latencyMs,
                    'confidence' => $stepResult['result']->confidence,
                    'progress' => $progressPercent,
                    'completed_stages' => $completedCount,
                    'is_workflow_completed' => $freshRun->status === ContentWorkflowStatus::COMPLETED,
                    'document_id' => $freshRun->document_id,
                    'next_node' => $freshRun->current_node,
                    'done' => true,
                ]);

            } catch (Throwable $e) {
                Log::error("Content Intelligence Stream Step Error: {$e->getMessage()}", [
                    'run_id' => $runId,
                    'trace' => $e->getTraceAsString(),
                ]);

                $sendEvent([
                    'type' => 'error',
                    'message' => 'Pipeline step failure: '.$e->getMessage(),
                ], 'error');
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
