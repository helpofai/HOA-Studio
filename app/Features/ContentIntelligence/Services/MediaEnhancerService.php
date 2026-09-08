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

        $slug = \Illuminate\Support\Str::slug($topic, '_');
        return "```mermaid\ngraph TD;\n    A[\"Input / Request Layer\"] --> B[\"{$topic} Core Engine\"];\n    B --> C[\"Processing & Model Execution\"];\n    C --> D[\"Verification & Safety Layer\"];\n    D --> E[\"Final Output Generation\"];\n```";
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
        return '<div class="overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60">'.
            '<table class="w-full text-left text-sm text-slate-300">'.
            '<thead class="bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10">'.
            '<tr><th class="p-3">Feature / Dimension</th><th class="p-3">Specification</th><th class="p-3">Capability & Impact</th></tr>'.
            '</thead><tbody class="divide-y divide-white/5">'.
            '<tr><td class="p-3 font-mono text-violet-300">Core Architecture</td><td class="p-3">Next-Gen Multimodal Foundation</td><td class="p-3">Optimized low-latency inference</td></tr>'.
            '<tr><td class="p-3 font-mono text-violet-300">Context Window</td><td class="p-3">High-Capacity Scaling</td><td class="p-3">Deep cross-document reasoning</td></tr>'.
            '<tr><td class="p-3 font-mono text-violet-300">Deployment Footprint</td><td class="p-3">API & Edge Configurations</td><td class="p-3">Seamless integration across workflows</td></tr>'.
            '</tbody></table></div>';
    }
}
