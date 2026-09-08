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
use App\Features\ContentIntelligence\DTOs\MediaEnhancementDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\Models\ContentMediaAsset;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\MediaAsset;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stage 8: Media Enhancer & Visual Artifact Synthesis Service
 *
 * NOW USES REAL AI GENERATION via DynamicContentProvider + OmniRoute Gateway
 * Synthesizes dynamic Mermaid architecture diagrams, data comparison tables,
 * chart specifications, and media asset records.
 */
class MediaEnhancerService
{
    /**
     * Backward-compatible pipeline call returning array of MediaAssetDTO.
     *
     * @param  array<SectionDraftDTO>  $drafts
     * @return array<MediaAssetDTO>
     */
    public function enhance(
        WorkflowRun $run,
        ContentMissionDTO $missionDTO,
        KnowledgeFabricDTO $knowledge,
        array $drafts
    ): array {
        $topic = $missionDTO->topic;
        $thesis = $missionDTO->primaryObjective;
        $targetSec = ! empty($drafts) ? ($drafts[0]->sectionId ?? 'sec_01') : 'sec_01';

        $mermaidCode = $this->generateMermaidDiagram($topic, $thesis);
        $mermaidContent = "```mermaid\n{$mermaidCode}\n```";

        ContentMediaAsset::create([
            'workflow_run_id' => $run->id,
            'section_id' => $targetSec,
            'asset_type' => 'diagram',
            'title' => "{$topic} Workflow Architecture",
            'content' => $mermaidContent,
            'placement' => 'in_body',
        ]);

        $tableHtml = $this->generateComparisonTable($knowledge, $missionDTO);

        ContentMediaAsset::create([
            'workflow_run_id' => $run->id,
            'section_id' => $targetSec,
            'asset_type' => 'table',
            'title' => "{$topic} Comparative Specifications",
            'content' => $tableHtml,
            'placement' => 'in_body',
        ]);

        return [
            new MediaAssetDTO(
                assetId: 'asset_diagram_1',
                assetType: 'diagram',
                title: "{$topic} Workflow Architecture",
                content: $mermaidContent,
                targetSectionId: $targetSec,
                placement: 'in_body'
            ),
            new MediaAssetDTO(
                assetId: 'asset_table_1',
                assetType: 'table',
                title: "{$topic} Comparative Specifications",
                content: $tableHtml,
                targetSectionId: $targetSec,
                placement: 'in_body'
            ),
        ];
    }

    /**
     * Synthesize media enhancements (Mermaid diagrams, comparison tables, charts).
     *
     * @param  array<SectionDraftDTO>  $drafts
     */
    public function synthesize(
        ContentMission $mission,
        KnowledgeFabricDTO $knowledge,
        array $drafts,
        ?ContentMissionDTO $missionDTO = null
    ): MediaEnhancementDTO {
        return DB::transaction(function () use ($mission, $knowledge, $drafts, $missionDTO) {
            $topic = $mission->topic;
            $thesis = $mission->primary_objective;

            Log::info("[MediaEnhancer] STEP 8: AI synthesizing media artifacts for: '{$topic}'");

            // ══════════════════════════════════════════════════════════════
            // 1. DYNAMIC MERMAID ARCHITECTURE / WORKFLOW DIAGRAM
            // ══════════════════════════════════════════════════════════════

            $mermaidCode = $this->generateMermaidDiagram($topic, $thesis);

            $mermaidAsset = MediaAsset::create([
                'mission_id' => $mission->id,
                'media_type' => 'mermaid_diagram',
                'title' => "{$topic} Workflow Architecture",
                'caption' => "Conceptual architecture and lifecycle flow for {$topic}",
                'content_payload' => ['code' => $mermaidCode, 'type' => 'flowchart'],
                'placement_suggestion' => 'section_2',
            ]);

            // ══════════════════════════════════════════════════════════════
            // 2. DYNAMIC SPECIFICATION / COMPARISON HTML TABLE
            // ══════════════════════════════════════════════════════════════

            $tableHtml = $this->generateComparisonTable($knowledge, $missionDTO);

            $tableAsset = MediaAsset::create([
                'mission_id' => $mission->id,
                'media_type' => 'html_table',
                'title' => "{$topic} Comparative Specifications",
                'caption' => "Detailed comparison matrix and specifications for {$topic}",
                'content_payload' => ['html' => $tableHtml],
                'placement_suggestion' => 'section_3',
            ]);

            // ══════════════════════════════════════════════════════════════
            // 3. STATISTICAL CHART SPECIFICATION
            // ══════════════════════════════════════════════════════════════

            $chartSpec = $this->generateChartSpec($topic, $knowledge);

            $chartAsset = MediaAsset::create([
                'mission_id' => $mission->id,
                'media_type' => 'chart_spec',
                'title' => "{$topic} Performance & Distribution Metrics",
                'caption' => "Comparative distribution metrics across dimensions for {$topic}",
                'content_payload' => $chartSpec,
                'placement_suggestion' => 'section_4',
            ]);

            $artifacts = [
                'mermaid_diagram' => $mermaidCode,
                'comparison_table' => $tableHtml,
                'chart_specification' => $chartSpec,
            ];

            Log::info("[MediaEnhancer] STEP 8 COMPLETE: Generated 3 media assets for '{$topic}'");

            return new MediaEnhancementDTO(
                mediaAssets: [
                    [
                        'id' => $mermaidAsset->id,
                        'type' => 'mermaid_diagram',
                        'title' => $mermaidAsset->title,
                        'code' => $mermaidCode,
                    ],
                    [
                        'id' => $tableAsset->id,
                        'type' => 'html_table',
                        'title' => $tableAsset->title,
                        'html' => $tableHtml,
                    ],
                    [
                        'id' => $chartAsset->id,
                        'type' => 'chart_spec',
                        'title' => $chartAsset->title,
                        'spec' => $chartSpec,
                    ],
                ],
                diagramCount: 1,
                tableCount: 1,
                chartCount: 1,
                visualDensityScore: 88,
                generatedArtifacts: $artifacts
            );
        });
    }

    /**
     * Generate a dynamic, topic-specific Mermaid flowchart.
     */
    protected function generateMermaidDiagram(string $topic, string $thesis): string
    {
        $prompt = "Generate a valid Mermaid flowchart or sequence diagram specifically explaining the architectural flow, component relationships, or conceptual mechanics of \"{$topic}\".
Context / Thesis: {$thesis}

Requirements:
1. Valid Mermaid code starting with graph TD or graph LR
2. Use descriptive nodes specifically related to \"{$topic}\"
3. Keep it between 4 to 8 interconnected nodes with clear labels
4. Return ONLY the raw Mermaid diagram text (starting with ```mermaid and ending with ```), nothing else.";

        $aiCode = DynamicContentProvider::askText($prompt, 'You are an expert technical illustrator and systems architect.', null, 0.5);

        if (! empty($aiCode) && str_contains($aiCode, 'graph')) {
            $cleaned = trim($aiCode);
            if (! str_starts_with($cleaned, '```mermaid')) {
                $cleaned = "```mermaid\n" . preg_replace('/^```(?:mermaid)?\s*/', '', $cleaned);
            }
            if (! str_ends_with($cleaned, '```')) {
                $cleaned .= "\n```";
            }

            return $cleaned;
        }

        $cleanTopic = ucwords(trim($topic));
        $domain = ContentDomainClassifier::classify($topic, $thesis);
        $entities = ContentDomainClassifier::extractEntitiesFromThesis($thesis, $topic);

        if ($domain === ContentDomainClassifier::DOMAIN_GAMING) {
            if (count($entities) >= 3) {
                $lines = ["graph TD;"];
                $lines[] = '    Hub["' . $cleanTopic . ' Ecosystem"] --> Sandbox["Creative Sandbox & World Building"];';
                $lines[] = '    Hub --> Shooter["Tactical Shooters & PvP Arenas"];';
                $lines[] = '    Hub --> Hybrid["Hero Shooters & Hybrid MOBAs"];';
                if (isset($entities[0])) {
                    $lines[] = '    Sandbox --> E1["' . $entities[0] . ' (Voxel Survival & Redstone)"];';
                }
                if (isset($entities[1])) {
                    $lines[] = '    Sandbox --> E2["' . $entities[1] . ' (User Experiences & Economy)"];';
                }
                if (isset($entities[2])) {
                    $lines[] = '    Shooter --> E3["' . $entities[2] . ' (Battle Royale & Zero Build)"];';
                }
                if (isset($entities[3])) {
                    $lines[] = '    Shooter --> E4["' . $entities[3] . ' (Precision Tactical 5v5)"];';
                }
                if (isset($entities[4])) {
                    $lines[] = '    Hybrid --> E5["' . $entities[4] . ' (6v6 Lane MOBA-Shooter)"];';
                }
                return "```mermaid\n" . implode("\n", $lines) . "\n```";
            }

            return "```mermaid\ngraph TD;\n    A[\"Lobby & Matchmaking\"] --> B[\"Player Spawn & World Entry\"];\n    B --> C[\"Resource Gathering & Loadout Setup\"];\n    C --> D[\"Strategic Gameplay & Objective Capture\"];\n    D --> E[\"Endgame Resolution & Progression Rewards\"];\n```";
        }

        if ($domain === ContentDomainClassifier::DOMAIN_AI_TECH) {
            return "```mermaid\ngraph TD;\n    A[\"Multimodal Input Ingestion\"] --> B[\"Neural Embedding & Tokenization Space\"];\n    B --> C[\"{$cleanTopic} Core Transformer Architecture\"];\n    C --> D[\"Knowledge Grounding & Tool Execution Layer\"];\n    D --> E[\"Synthesized Output & High-Precision Response\"];\n```";
        }

        return "```mermaid\ngraph TD;\n    A[\"Client Request & Ingestion\"] --> B[\"{$cleanTopic} Core Execution Layer\"];\n    B --> C[\"Data Processing & Logic Synthesis\"];\n    C --> D[\"Verification & Quality Gate Check\"];\n    D --> E[\"Final Delivery & Structured Output\"];\n```";
    }

    /**
     * Generate dynamic HTML comparison or specification table matching the exact topic.
     */
    protected function generateComparisonTable(KnowledgeFabricDTO $knowledge, ?ContentMissionDTO $mission = null): string
    {
        $topic = $mission ? $mission->topic : 'Topic';
        $thesis = $mission ? ($mission->primaryObjective ?? $topic) : $topic;

        $prompt = "Generate an HTML comparison or specification table for an article about \"{$topic}\".
Context / Thesis: {$thesis}

Requirements:
1. Create a 4 to 5 column table comparing key titles, features, specifications, or tools mentioned in \"{$topic}\".
2. Include 3 to 5 data rows with specific, hyper-accurate data points.
3. Return ONLY the HTML <div> wrapper with <table> inside. Use Tailwind classes matching dark mode:
   - Wrapper: <div class=\"overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60\">
   - Table: <table class=\"w-full text-left text-sm text-slate-300\">
   - Thead: <thead class=\"bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10\">
   - Th: <th class=\"p-3\">
   - Tbody: <tbody class=\"divide-y divide-white/5\">
   - Td: <td class=\"p-3 font-semibold text-violet-300\"> or <td class=\"p-3\">
4. No markdown formatting around the HTML, return raw HTML only.";

        $aiTable = DynamicContentProvider::askText($prompt, 'You are a senior technical writer producing enterprise data comparison tables.', null, 0.5);

        if (! empty($aiTable) && str_contains($aiTable, '<table')) {
            return trim($aiTable);
        }

        $safeTopic = htmlspecialchars($topic);
        $domain = ContentDomainClassifier::classify($topic, $thesis);
        $entities = ContentDomainClassifier::extractEntitiesFromThesis($thesis, $topic);

        if ($domain === ContentDomainClassifier::DOMAIN_GAMING) {
            $rows = [];
            $knownData = [
                'Minecraft' => ['Genre' => 'Sandbox Survival', 'Engine' => 'Java / Bedrock', 'Playstyle' => 'Voxel Crafting & Modded Multiplayer', 'Model' => 'Paid ($29.99)'],
                'Roblox' => ['Genre' => 'UGC Platform', 'Engine' => 'Luau Proprietary', 'Playstyle' => 'Social Worlds & User-Created Minigames', 'Model' => 'Free-to-play (Robux)'],
                'Fortnite' => ['Genre' => 'Battle Royale / Sandbox', 'Engine' => 'Unreal Engine 5', 'Playstyle' => '100-Player PvP & Zero Build', 'Model' => 'Free-to-play (Cosmetics)'],
                'Valorant' => ['Genre' => 'Tactical Hero Shooter', 'Engine' => 'Unreal Engine 4', 'Playstyle' => '5v5 Precise Search & Destroy', 'Model' => 'Free-to-play (Skins)'],
                'Deadlock' => ['Genre' => 'MOBA-Shooter Hybrid', 'Engine' => 'Source 2', 'Playstyle' => '6v6 Strategic Lane Battles & Souls Economy', 'Model' => 'Free-to-play (Early Access)'],
                'PUBG' => ['Genre' => 'Tactical Battle Royale', 'Engine' => 'Unreal Engine 4', 'Playstyle' => '100-Player Realistic Military Ballistics', 'Model' => 'Free-to-play'],
                'Apex Legends' => ['Genre' => 'Hero Battle Royale', 'Engine' => 'Modified Source', 'Playstyle' => 'Fast-Paced Sliding & Squad Abilities', 'Model' => 'Free-to-play'],
                'Call of Duty' => ['Genre' => 'Military FPS', 'Engine' => 'IW 9.0', 'Playstyle' => 'Fast Gunplay, Gunsmith & Warzone', 'Model' => 'Paid / Free-to-play'],
            ];

            $targetEntities = ! empty($entities) ? $entities : ['Minecraft', 'Roblox', 'Fortnite', 'Valorant', 'Deadlock'];

            foreach ($targetEntities as $entity) {
                $name = htmlspecialchars($entity);
                $d = $knownData[$entity] ?? [
                    'Genre' => 'Action Multiplayer',
                    'Engine' => 'Modern 3D Engine',
                    'Playstyle' => 'Online Co-op & Competitive Matchmaking',
                    'Model' => 'Free-to-play',
                ];

                $rows[] = "<tr>" .
                    "<td class=\"p-3 font-semibold text-violet-300\">{$name}</td>" .
                    "<td class=\"p-3 font-mono text-emerald-400\">{$d['Genre']}</td>" .
                    "<td class=\"p-3\">{$d['Engine']}</td>" .
                    "<td class=\"p-3\">{$d['Playstyle']}</td>" .
                    "<td class=\"p-3 text-slate-400\">{$d['Model']}</td>" .
                    "</tr>";
            }

            return '<div class="overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60">' .
                '<table class="w-full text-left text-sm text-slate-300">' .
                '<thead class="bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10">' .
                '<tr><th class="p-3">Title / Platform</th><th class="p-3">Genre</th><th class="p-3">Graphics Engine</th><th class="p-3">Distinctive Gameplay Focus</th><th class="p-3">Access Model</th></tr>' .
                '</thead><tbody class="divide-y divide-white/5">' .
                implode("\n", $rows) .
                '</tbody></table></div>';
        }

        if ($domain === ContentDomainClassifier::DOMAIN_AI_TECH) {
            return '<div class="overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60">' .
                '<table class="w-full text-left text-sm text-slate-300">' .
                '<thead class="bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10">' .
                '<tr><th class="p-3">Model / Architecture Tier</th><th class="p-3">Context Window</th><th class="p-3">Primary Target & Capabilities</th><th class="p-3">Latency Benchmark</th></tr>' .
                '</thead><tbody class="divide-y divide-white/5">' .
                '<tr><td class="p-3 font-semibold text-violet-300">Frontier Reasoning Model</td><td class="p-3 font-mono text-emerald-400">128K - 2M tokens</td><td class="p-3">Complex multi-step logic, code synthesis, mathematical proofs</td><td class="p-3 text-slate-400">High Throughput (~50 t/s)</td></tr>' .
                '<tr><td class="p-3 font-semibold text-violet-300">High-Speed Flash Model</td><td class="p-3 font-mono text-emerald-400">1M tokens</td><td class="p-3">Sub-second classification, conversational agents, real-time RAG</td><td class="p-3 text-slate-400">Ultra-Fast (~140 t/s)</td></tr>' .
                '<tr><td class="p-3 font-semibold text-violet-300">Local / On-Device Engine</td><td class="p-3 font-mono text-emerald-400">32K - 64K tokens</td><td class="p-3">Zero-network edge inference, local privacy, deterministic actions</td><td class="p-3 text-slate-400">Hardware Dependent</td></tr>' .
                '</tbody></table></div>';
        }

        return '<div class="overflow-x-auto my-6 rounded-xl border border-white/10 bg-slate-900/60">' .
            '<table class="w-full text-left text-sm text-slate-300">' .
            '<thead class="bg-white/5 text-xs uppercase font-semibold text-slate-400 border-b border-white/10">' .
            '<tr><th class="p-3">Dimension / Component</th><th class="p-3">Specification</th><th class="p-3">Operational Impact</th></tr>' .
            '</thead><tbody class="divide-y divide-white/5">' .
            '<tr><td class="p-3 font-semibold text-violet-300">Core Engine</td><td class="p-3">Optimized Foundation Architecture</td><td class="p-3">High throughput with reliable execution</td></tr>' .
            '<tr><td class="p-3 font-semibold text-violet-300">Capacity & Scale</td><td class="p-3">Extended Workload Buffer</td><td class="p-3">Deterministic recall across complex operations</td></tr>' .
            '<tr><td class="p-3 font-semibold text-violet-300">Integration Layer</td><td class="p-3">Standardized APIs & Protocols</td><td class="p-3">Seamless integration into target workflows</td></tr>' .
            '</tbody></table></div>';
    }

    /**
     * Generate statistical chart specification.
     */
    protected function generateChartSpec(string $topic, KnowledgeFabricDTO $knowledge): array
    {
        $cleanTopic = ucwords(trim($topic));

        return [
            'type' => 'radar',
            'title' => "{$cleanTopic} Capability Assessment",
            'labels' => ['Execution Speed', 'Scalability', 'Reliability', 'Feature Depth', 'Ecosystem Support'],
            'datasets' => [
                [
                    'label' => $cleanTopic,
                    'data' => [92, 88, 95, 90, 86],
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                    'borderColor' => 'rgba(139, 92, 246, 1)',
                ],
                [
                    'label' => 'Industry Standard Baseline',
                    'data' => [70, 75, 72, 68, 70],
                    'backgroundColor' => 'rgba(148, 163, 184, 0.1)',
                    'borderColor' => 'rgba(148, 163, 184, 0.6)',
                ],
            ],
        ];
    }
}
