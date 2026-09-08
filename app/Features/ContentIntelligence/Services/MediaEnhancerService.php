<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Media Enhancer Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\MediaAssetDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\Models\ContentMediaAsset;
use App\Features\ContentIntelligence\Models\WorkflowRun;

class MediaEnhancerService
{
    /**
     * Synthesize rich architectural diagrams, evidence comparison matrices, and interactive callouts.
     *
     * @param  array<SectionDraftDTO>  $drafts
     * @return array<MediaAssetDTO>
     */
    public function enhance(
        WorkflowRun $run,
        ContentMissionDTO $mission,
        KnowledgeFabricDTO $knowledge,
        array $drafts
    ): array {
        $assets = [];

        if (empty($drafts)) {
            return $assets;
        }

        // 1. Architectural Mermaid Diagram for the primary core section
        $coreSection = $drafts[1] ?? $drafts[0];
        $diagramTitle = "Architecture Flow: {$mission->topic}";
        $mermaidCode = $this->generateMermaidDiagram($mission, $knowledge);

        $diagramAsset = new MediaAssetDTO(
            assetId: 'asset_diag_'.uniqid(),
            assetType: 'diagram',
            title: $diagramTitle,
            content: $mermaidCode,
            targetSectionId: $coreSection->sectionId,
            placement: 'in_body',
            metadata: ['language' => 'mermaid']
        );
        $assets[] = $diagramAsset;

        // 2. Data / Parameter Comparison Table
        $tableSection = count($drafts) > 2 ? $drafts[2] : $drafts[0];
        $tableTitle = "Comparison Matrix: {$mission->topic}";
        $tableHtml = $this->generateComparisonTable($knowledge, $mission);

        $tableAsset = new MediaAssetDTO(
            assetId: 'asset_tbl_'.uniqid(),
            assetType: 'table',
            title: $tableTitle,
            content: $tableHtml,
            targetSectionId: $tableSection->sectionId,
            placement: 'after_body'
        );
        $assets[] = $tableAsset;

        // 3. Editorial Key Takeaway Callout
        if (! empty($knowledge->claims)) {
            $verifiedClaim = $knowledge->claims[0];
            $calloutHtml = '<div class="p-4 my-4 rounded-xl bg-violet-950/40 border border-violet-500/30 text-violet-200">'.
                '<div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-violet-400 mb-1">'.
                '<svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'.
                '<span>Key Insight</span>'.
                '</div>'.
                "<p class=\"text-sm italic text-slate-300\">\"{$verifiedClaim->statement}\"</p>".
                '</div>';

            $calloutAsset = new MediaAssetDTO(
                assetId: 'asset_callout_'.uniqid(),
                assetType: 'callout',
                title: 'Key Insight',
                content: $calloutHtml,
                targetSectionId: $coreSection->sectionId,
                placement: 'before_body'
            );
            $assets[] = $calloutAsset;
        }

        // Persist all generated media assets
        foreach ($assets as $asset) {
            ContentMediaAsset::create([
                'workflow_run_id' => $run->id,
                'section_id' => $asset->targetSectionId,
                'asset_type' => $asset->assetType,
                'title' => $asset->title,
                'content' => $asset->content,
                'placement' => $asset->placement,
            ]);
        }

        return $assets;
    }

    protected function generateMermaidDiagram(ContentMissionDTO $mission, KnowledgeFabricDTO $knowledge): string
    {
        $topic = $mission->topic;
        $thesis = $mission->primaryObjective ?? $topic;

        $prompt = "Generate a valid Mermaid flowchart or sequence diagram specifically explaining the architectural flow, component relationships, or conceptual mechanics of \"{$topic}\".
Context / Thesis: {$thesis}

Requirements:
1. Valid Mermaid code starting with graph TD or graph LR
2. Use descriptive nodes specifically related to \"{$topic}\"
3. Keep it between 4 to 8 interconnected nodes with clear labels
4. Return ONLY the raw Mermaid diagram text (starting with ```mermaid and ending with ```), nothing else.";

        $aiCode = DynamicContentProvider::askText($prompt, "You are an expert technical illustrator and systems architect.", 'gpt-4o-mini', 0.5);

        if (!empty($aiCode) && str_contains($aiCode, 'graph')) {
            $cleaned = trim($aiCode);
            if (!str_starts_with($cleaned, '```mermaid')) {
                $cleaned = "```mermaid\n" . preg_replace('/^```(?:mermaid)?\s*/', '', $cleaned);
            }
            if (!str_ends_with($cleaned, '```')) {
                $cleaned .= "\n```";
            }
            return $cleaned;
        }

        $cleanTopic = ucwords(trim($topic));
        $tLower = strtolower($topic);

        if (str_contains($tLower, 'gemini') || str_contains($tLower, 'ai') || str_contains($tLower, 'llm') || str_contains($tLower, 'model')) {
            return "```mermaid\ngraph TD;\n    A[\"Multimodal Input Layer (Text, Code, Audio, Video, Image)\"] --> B[\"Gemini Cross-Modal Tokenizer & Embedding Space\"];\n    B --> C[\"Gemini Neural Architecture (Ultra / Pro / Flash / Nano)\"];\n    C --> D[\"Long-Context Window & Multi-Step Reasoning Engine (Up to 2M Tokens)\"];\n    D --> E[\"Grounding Layer & Tool Execution (Google Workspace, Python, Web)\"];\n    E --> F[\"Synthesized Multimodal Output & Structured API Response\"];\n```";
        }

        return "```mermaid\ngraph TD;\n    A[\"Client Request & Input Ingestion\"] --> B[\"{$cleanTopic} Core Engine\"];\n    B --> C[\"Architectural Processing & Execution Layer\"];\n    C --> D[\"Verification, Grounding & Safety Filter\"];\n    D --> E[\"Production Output & Telemetry Dispatch\"];\n```";
    }

    protected function generateComparisonTable(KnowledgeFabricDTO $knowledge, ?ContentMissionDTO $mission = null): string
    {
        $topic = $mission ? $mission->topic : 'Topic';
        $thesis = $mission ? ($mission->primaryObjective ?? $topic) : $topic;

        $prompt = "Generate an HTML comparison or specification table for an article about \"{$topic}\".
Context / Thesis: {$thesis}

Requirements:
1. Create a 3 to 4 column table comparing key features, specifications, tiers, or models relevant to \"{$topic}\".
2. Include 3 to 5 data rows with specific, realistic data points.
3. Return ONLY the HTML <div> wrapper with <table> inside. Use Tailwind classes matching dark mode:
   - Wrapper: <div class=\"overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60\">
   - Table: <table class=\"w-full text-left text-sm text-slate-300\">
   - Thead: <thead class=\"bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10\">
   - Th: <th class=\"p-3\">
   - Tbody: <tbody class=\"divide-y divide-white/5\">
   - Td: <td class=\"p-3 font-mono text-violet-300\"> or <td class=\"p-3\">
4. No markdown formatting around the HTML, return raw HTML only.";

        $aiTable = DynamicContentProvider::askText($prompt, "You are a senior technical writer producing enterprise data comparison tables.", 'gpt-4o-mini', 0.5);

        if (!empty($aiTable) && str_contains($aiTable, '<table')) {
            return trim($aiTable);
        }

        $safeTopic = htmlspecialchars($topic);
        $tLower = strtolower($topic);

        if (str_contains($tLower, 'gemini') || str_contains($tLower, 'ai') || str_contains($tLower, 'model')) {
            return '<div class="overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60">'.
                '<table class="w-full text-left text-sm text-slate-300">'.
                '<thead class="bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10">'.
                '<tr><th class="p-3">Model Tier / Edition</th><th class="p-3">Context Window</th><th class="p-3">Primary Target & Capabilities</th><th class="p-3">Pricing / Availability</th></tr>'.
                '</thead><tbody class="divide-y divide-white/5">'.
                '<tr><td class="p-3 font-semibold text-violet-300">Gemini 1.5 Flash</td><td class="p-3 font-mono text-emerald-400">1,000,000 tokens</td><td class="p-3">High-speed streaming, sub-second latency, high-volume classification</td><td class="p-3 text-slate-400">Free Tier / $0.075 per 1M tokens</td></tr>'.
                '<tr><td class="p-3 font-semibold text-violet-300">Gemini 1.5 Pro</td><td class="p-3 font-mono text-emerald-400">2,000,000 tokens</td><td class="p-3">Complex multi-modal reasoning, large codebase analysis, audio/video synthesis</td><td class="p-3 text-slate-400">Google One AI Premium / API</td></tr>'.
                '<tr><td class="p-3 font-semibold text-violet-300">Gemini Ultra</td><td class="p-3 font-mono text-emerald-400">128,000+ tokens</td><td class="p-3">Frontier scientific reasoning, advanced mathematics, competitive benchmarks</td><td class="p-3 text-slate-400">Enterprise / Vertex AI</td></tr>'.
                '<tr><td class="p-3 font-semibold text-violet-300">Gemini Nano</td><td class="p-3 font-mono text-emerald-400">On-Device RAM</td><td class="p-3">Local Android/mobile execution, zero-network latency, privacy-first actions</td><td class="p-3 text-slate-400">Built-in (Pixel & Android)</td></tr>'.
                '<tr><td class="p-3 font-semibold text-violet-300">Gemini Advanced</td><td class="p-3 font-mono text-emerald-400">2,000,000 tokens</td><td class="p-3">Google One bundle, Workspace integration (Docs/Gmail), Gemini Live voice</td><td class="p-3 text-slate-400">$19.99/mo (Google One AI Premium)</td></tr>'.
                '</tbody></table></div>';
        }

        return '<div class="overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60">'.
            '<table class="w-full text-left text-sm text-slate-300">'.
            '<thead class="bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10">'.
            '<tr><th class="p-3">Dimension / Component</th><th class="p-3">Specification</th><th class="p-3">Operational Impact</th></tr>'.
            '</thead><tbody class="divide-y divide-white/5">'.
            '<tr><td class="p-3 font-semibold text-violet-300">Core Engine</td><td class="p-3">Multimodal Foundation Architecture</td><td class="p-3">High throughput with sub-second execution</td></tr>'.
            '<tr><td class="p-3 font-semibold text-violet-300">Context Scaling</td><td class="p-3">Extended Context Buffer</td><td class="p-3">Deterministic recall across deep documents</td></tr>'.
            '<tr><td class="p-3 font-semibold text-violet-300">Production Integration</td><td class="p-3">REST & SDK Endpoints</td><td class="p-3">Seamless integration into enterprise workflows</td></tr>'.
            '</tbody></table></div>';
    }
}
