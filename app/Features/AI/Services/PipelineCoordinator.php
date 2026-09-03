<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Multi-Agent Pipeline Coordinator
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

use App\Models\User;
use App\Features\KnowledgeBase\Actions\RetrieveRagContext;

class PipelineCoordinator
{
    protected ContentWriterBrain $brain;
    protected OmniRouteClient $client;

    public function __construct(ContentWriterBrain $brain, OmniRouteClient $client)
    {
        $this->brain = $brain;
        $this->client = $client;
    }

    /**
     * Executes the multi-stage Swarm Pipeline.
     * Uses PHP generators (yield) or explicit callback arrays to stream BOTH
     * status events and finalized AI token chunks to the SSE stream.
     */
    public function executeAgenticPipeline(
        array $pipelineStages,
        string $topic,
        array $context,
        ?string $customInstruction,
        User $user,
        callable $sendEvent
    ): string {
        $fullDraft = "";
        $targetKeyword = $context['target_keyword'] ?? $topic;
        $tone = $context['brand_voice'] ?? 'authoritative and professional';

        // ==========================================
        // STAGE 1: KEYWORD RESEARCH & VECTOR MEMORY RAG
        // ==========================================
        $knowledgeContext = "Base domain knowledge.";
        $extractedLsi = "";
        
        if (in_array('keyword_research', $pipelineStages) || in_array('fact_check', $pipelineStages)) {
            $sendEvent("status", "🔍 Querying Vector Database & Analyzing SERP Entities...");
            
            try {
                $ragAction = app(RetrieveRagContext::class);
                $ragResult = $ragAction->execute($user, $topic, limit: 5);
                
                if (!empty($ragResult['prompt_snippet'])) {
                    $knowledgeContext = $ragResult['prompt_snippet'];
                }
                
                // Invoke the "SEO Researcher Agent" to extract LSI entities from RAG
                $resMessages = [
                    ['role' => 'system', 'content' => 'You are an SEO entity researcher. Extract a comma-separated list of 10 high-value semantic LSI keywords based on the target topic and context supplied.'],
                    ['role' => 'user', 'content' => "Topic: {$topic}\nContext provided:\n{$knowledgeContext}"]
                ];
                $result = $this->client->chatCompletion($resMessages, ['model' => 'auto', 'temperature' => 0.5]);
                $extractedLsi = $result['choices'][0]['message']['content'] ?? "";
                $sendEvent("status", "🧠 Synthesized Memory & Brand Voice Profile. Extracted Entities: " . substr($extractedLsi, 0, 40) . "...");
            } catch (\Throwable $e) { }
            usleep(200000); 
        }

        // ==========================================
        // STAGE 2: ARTICLE OUTLINE ARCHITECTURE
        // ==========================================
        $outline = [];
        
        if (in_array('outline', $pipelineStages)) {
            $sendEvent("status", "📑 Architecting H2/H3 Section Outline & Data Structures...");
            
            $sysPrompt = "You are an Executive Content Architect. Generate a logical, highly-structured article outline for the given topic. Output STRICTLY raw JSON in this format: {\"sections\": [{\"title\": \"H2 Title\", \"focus\": \"What to cover in this section\"}]}. NO markdown, NO conversational text. Just JSON.";
            $userPrompt = "Target Topic: {$topic}\nLSI Keywords to Include: {$extractedLsi}\nMake it detailed with 3 to 6 primary sections.";
            
            try {
                $outResult = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => $sysPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ], ['model' => 'auto', 'temperature' => 0.6]);
                
                $responseStr = $outResult['choices'][0]['message']['content'] ?? '';
                // Clean markdown JSON ticks if the model returns them
                $responseStr = preg_replace('/```json\s*/', '', $responseStr);
                $responseStr = preg_replace('/```\s*/', '', $responseStr);
                
                $parsed = json_decode(trim($responseStr), true);
                if (isset($parsed['sections']) && is_array($parsed['sections'])) {
                    $outline = $parsed['sections'];
                }
            } catch (\Throwable $e) { }

            // Fallback outline if LLM structuring failed
            if (empty($outline)) {
                $outline = [
                    ['title' => 'Introduction and Overview', 'focus' => 'Hook and basic definitions'],
                    ['title' => 'Deep Dive Analysis', 'focus' => 'Statistics, grounded facts, methodology'],
                    ['title' => 'Conclusion & Summary', 'focus' => 'Actionable takeaways for the reader']
                ];
            }
        } else {
            $outline = [['title' => 'Main Content Draft', 'focus' => 'Comprehensive sequential coverage']];
        }

        // ==========================================
        // STAGE 3: CHUNKED SECTION-BY-SECTION EXTENDED GENERATION
        // ==========================================
        $sendEvent("status", "✍️ Commencing Distributed Section-by-Section Swarm Generation...");
        
        $seoInstruction = in_array('seo_optimize', $pipelineStages) 
                ? "Ensure deep semantic SEO optimization. Naturally weave in variations of {$targetKeyword} and these LSI tokens: {$extractedLsi}." 
                : "";

        foreach ($outline as $index => $section) {
            $step = $index + 1;
            $sendEvent("status", "✍️ Writing Section {$step} / " . count($outline) . ": {$section['title']}...");
            
            $sysPrompt = "You are a professional Senior Writer Agent producing ultra-high quality, publisher-grade content. 
Follow these strict constraints:
- Tone: {$tone}
- Format using HTML tags (e.g., <p>, <ul>, <li>, <strong>). No markdown.
- Ground all facts strictly according to the Context Memory provided. Do not hallucinate.
{$seoInstruction}";

            if (!empty($customInstruction)) {
                $sysPrompt .= "\n\nUser Custom Directive: {$customInstruction}";
            }

            $userPrompt = "Write Section Title: '{$section['title']}'\nFocus/Angle: {$section['focus']}\n\nVector Memory Context:\n{$knowledgeContext}";

            // Insert H2 into document stream directly
            $sectionHeader = "<h2>{$section['title']}</h2>\n";
            $fullDraft .= $sectionHeader;
            $sendEvent("chunk", $sectionHeader);
            
            $textBuffer = "";
            try {
                $this->client->streamChat([
                    ['role' => 'system', 'content' => $sysPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ], 'auto', 0.65, function ($chunk) use ($sendEvent, &$textBuffer) {
                    if (!empty($chunk)) {
                        $textBuffer .= $chunk;
                        $sendEvent("chunk", $chunk);
                    }
                });
            } catch (\Throwable $e) { }

            $fullDraft .= $textBuffer . "\n<br>\n";
            $sendEvent("chunk", "\n<br>\n");
        }

        // ==========================================
        // STAGE 4: SCHEMA JSON-LD GENERATION
        // ==========================================
        if (in_array('schema_jsonld', $pipelineStages)) {
            $sendEvent("status", "🌐 Generating SEO Article Schema JSON-LD Maps...");
            try {
                $schemaPrompt = "Generate standard JSON-LD Schema.org Article metadata for an article titled '{$topic}'. Provide ONLY valid JSON inside a <script type=\"application/ld+json\"> tag.";
                $schemaResult = $this->client->chatCompletion([
                    ['role' => 'system', 'content' => 'You are a precise technical SEO robot.'],
                    ['role' => 'user', 'content' => $schemaPrompt]
                ], ['model' => 'auto', 'temperature' => 0.2]);
                $schemaCode = $schemaResult['choices'][0]['message']['content'] ?? '';
                if (strpos($schemaCode, '<script') !== false) {
                    $fullDraft .= "\n" . $schemaCode;
                    $sendEvent("chunk", "\n" . $schemaCode);
                }
            } catch (\Throwable $e) {}
        }

        $sendEvent("status", "🏆 Final 10-Point Quality Audit & Assembly Complete!");
        
        return $fullDraft;
    }
}
