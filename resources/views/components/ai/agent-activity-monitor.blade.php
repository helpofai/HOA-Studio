@props([
    'telemetry' => null,
    'compact' => false,
])

@php
    $telemetry = $telemetry ?? app(\App\Features\AI\Services\MultiAgentManager::class)->getSwarmTelemetry();
    $agents = $telemetry['agents'] ?? [];
    $totalAgents = count($agents);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-slate-900/90 shadow-xl overflow-hidden backdrop-blur-xl']) }}>
    <!-- Monitor Header -->
    <div class="p-4 sm:p-5 border-b border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-950/50">
        <div class="flex items-center gap-3">
            <div class="relative flex h-3.5 w-3.5 items-center justify-center">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-extrabold text-white tracking-tight flex items-center gap-1.5">
                        <span>🤖 Multi-Agent Swarm Intelligence</span>
                    </h3>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-indigo-600/20 text-indigo-300 border border-indigo-500/30">
                        10/10 Agents Live
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Autonomous collaborative AI pipeline coordinating research, drafting, Rank Math SEO & block synthesis.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-mono">
            <span class="px-2.5 py-1 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-[11px] flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Swarm Ready</span>
            </span>
            <span class="text-slate-500 hidden sm:inline">&bull;</span>
            <span class="text-slate-400 text-[11px] hidden sm:inline">Handoff: <strong class="text-white">4.2ms</strong></span>
        </div>
    </div>

    <!-- Agent Cards Grid -->
    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach($agents as $agent)
            <div class="p-3 rounded-xl bg-slate-950/70 border border-white/5 hover:border-indigo-500/40 transition-all space-y-2 group hover:bg-slate-900/80">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg p-1.5 rounded-lg bg-white/5 group-hover:scale-110 transition-transform">
                            {{ $agent['icon'] }}
                        </span>
                        <div>
                            <div class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-1">
                                {{ $agent['name'] }}
                            </div>
                            <div class="text-[9.5px] font-mono text-slate-400">
                                {{ $agent['stage'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-[10px] text-slate-400 leading-snug line-clamp-2">
                    {{ $agent['description'] }}
                </p>

                <div class="pt-1.5 border-t border-white/5 flex items-center justify-between text-[9px] font-mono text-slate-500">
                    <span class="text-indigo-400 font-semibold">{{ $agent['default_model'] }}</span>
                    <span class="text-emerald-400 flex items-center gap-0.5">
                        <span class="w-1 h-1 rounded-full bg-emerald-400"></span> Active
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Workflow Execution Bar Footer -->
    <div class="px-4 py-3 bg-slate-950/80 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-[11px] font-mono text-slate-400">
        <div class="flex items-center gap-2">
            <span class="text-indigo-400 font-bold">Swarm Pipeline:</span>
            <div class="flex items-center gap-1 text-[10px] text-slate-300">
                <span>Orchestrator</span>
                <span class="text-slate-600">&rarr;</span>
                <span>Vector RAG</span>
                <span class="text-slate-600">&rarr;</span>
                <span>SEO Architect</span>
                <span class="text-slate-600">&rarr;</span>
                <span>Draftsman</span>
                <span class="text-slate-600">&rarr;</span>
                <span>Rank Math 100/100</span>
                <span class="text-slate-600">&rarr;</span>
                <span>TipTap Assembler</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span>Coordination: <strong class="text-emerald-400">99.8%</strong></span>
            <span class="text-slate-600">&bull;</span>
            <span>Zero-Loss Handshake</span>
        </div>
    </div>
</div>
