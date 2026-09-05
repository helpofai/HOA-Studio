@props([
    'graphData' => [],
    'timeRange' => 24,
    'statusFilter' => 'all',
])

@php
    $summary = array_merge([
        'total_requests' => 0,
        'pass' => 0,
        'info' => 0,
        'warning' => 0,
        'fail' => 0,
        'total_tokens' => 0,
        'avg_latency_ms' => 12,
        'success_rate' => 100.0,
    ], (array) ($graphData['summary'] ?? []));
    $buckets = $graphData['buckets'] ?? [];
    $svgPaths = $graphData['svg_paths'] ?? [];
    $points = $svgPaths['points'] ?? [];
    $maxReq = max(1, $graphData['max_bucket_requests'] ?? 1);

    // Layer definitions with user requested color palette
    $layers = [
        'all' => [
            'label' => 'All Traffic',
            'stroke' => '#8b5cf6', // Violet/Indigo
            'gradStart' => 'rgba(139, 92, 246, 0.45)',
            'gradEnd' => 'rgba(99, 102, 241, 0.0)',
            'path' => $svgPaths['all'] ?? null,
            'count' => $summary['total_requests'],
            'badgeClass' => 'bg-violet-600/20 text-violet-300 border-violet-500/40',
        ],
        'pass' => [
            'label' => 'Pass (200 OK)',
            'stroke' => '#10b981', // Emerald Green
            'gradStart' => 'rgba(16, 185, 129, 0.45)',
            'gradEnd' => 'rgba(16, 185, 129, 0.0)',
            'path' => $svgPaths['pass'] ?? null,
            'count' => $summary['pass'],
            'badgeClass' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
        ],
        'info' => [
            'label' => 'Info (Routed)',
            'stroke' => '#38bdf8', // Sky-Blue
            'gradStart' => 'rgba(56, 189, 248, 0.45)',
            'gradEnd' => 'rgba(56, 189, 248, 0.0)',
            'path' => $svgPaths['info'] ?? null,
            'count' => $summary['info'],
            'badgeClass' => 'bg-sky-500/20 text-sky-300 border-sky-500/40',
        ],
        'warning' => [
            'label' => 'Warn (Latency/429)',
            'stroke' => '#fbbf24', // Amber/Gold
            'gradStart' => 'rgba(251, 191, 36, 0.45)',
            'gradEnd' => 'rgba(251, 191, 36, 0.0)',
            'path' => $svgPaths['warning'] ?? null,
            'count' => $summary['warning'],
            'badgeClass' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
        ],
        'fail' => [
            'label' => 'Fail (Error/500)',
            'stroke' => '#f43f5e', // Rose/Crimson
            'gradStart' => 'rgba(244, 63, 94, 0.45)',
            'gradEnd' => 'rgba(244, 63, 94, 0.0)',
            'path' => $svgPaths['fail'] ?? null,
            'count' => $summary['fail'],
            'badgeClass' => 'bg-red-500/20 text-red-300 border-red-500/40',
        ],
    ];

    $activeTheme = $layers[$statusFilter] ?? $layers['all'];
@endphp

<div 
    wire:key="telemetry-graph-{{ $timeRange }}-{{ $statusFilter }}"
    x-data="{ 
        hoverIndex: null,
        activePoint: null,
        points: @js($points),
        handleMouseMove(e) {
            if (!this.$refs.svgContainer || !this.points || !this.points.length) return;
            const rect = this.$refs.svgContainer.getBoundingClientRect();
            if (!rect || !rect.width) return;
            const relX = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
            const ratio = relX / rect.width;
            const index = Math.min(this.points.length - 1, Math.max(0, Math.round(ratio * (this.points.length - 1))));
            this.hoverIndex = index;
            this.activePoint = this.points[index] || null;
        },
        handleMouseLeave() {
            this.hoverIndex = null;
            this.activePoint = null;
        }
    }" 
    class="relative rounded-2xl bg-gradient-to-b from-slate-900/95 via-slate-950/95 to-slate-950 border border-white/10 p-5 sm:p-6 shadow-2xl backdrop-blur-2xl overflow-hidden group"
>
    <!-- Background Ambient Neon Glows -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Toolbar: Title, Health Pulse, Filter Pills, and Time Range -->
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-white/5">
        <!-- Title & Pulse Beacon -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-white/15 flex items-center justify-center text-lg shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                📈
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-black text-white tracking-tight flex items-center gap-2">
                        <span>OmniRoute Telemetry Graph</span>
                    </h3>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        MULTI-LAYER STREAM
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Real-time multi-color failover curves, latency tracking, and token throughput layers.
                </p>
            </div>
        </div>

        <!-- Interactive Color Layer Filter Options & Time Range Switcher -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Combined Status Filter Buttons with Color Badges -->
            <div class="flex items-center bg-slate-900/90 p-1 rounded-xl border border-white/10 text-xs font-mono shadow-inner">
                <!-- All Traffic (Violet/Indigo) -->
                <button 
                    type="button" 
                    wire:click="$set('graphStatusFilter', 'all')"
                    class="px-3 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5 {{ $statusFilter === 'all' ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white' }}"
                    title="View all combined telemetry stream layers"
                >
                    <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                    <span>All ({{ $summary['total_requests'] }})</span>
                </button>

                <!-- Pass (Emerald Green) -->
                <button 
                    type="button" 
                    wire:click="$set('graphStatusFilter', 'pass')"
                    class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5 {{ $statusFilter === 'pass' ? 'bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/40 shadow-sm' : 'text-slate-400 hover:text-emerald-300' }}"
                    title="Filter 200 OK successful responses"
                >
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Pass ({{ $summary['pass'] }})</span>
                </button>

                <!-- Info (Sky Blue) -->
                <button 
                    type="button" 
                    wire:click="$set('graphStatusFilter', 'info')"
                    class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5 {{ $statusFilter === 'info' ? 'bg-sky-500/20 text-sky-300 font-bold border border-sky-500/40 shadow-sm' : 'text-slate-400 hover:text-sky-300' }}"
                    title="Filter routed & fallback responses"
                >
                    <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                    <span>Info ({{ $summary['info'] }})</span>
                </button>

                <!-- Warning (Amber/Gold) -->
                <button 
                    type="button" 
                    wire:click="$set('graphStatusFilter', 'warning')"
                    class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5 {{ $statusFilter === 'warning' ? 'bg-amber-500/20 text-amber-300 font-bold border border-amber-500/40 shadow-sm' : 'text-slate-400 hover:text-amber-300' }}"
                    title="Filter high latency or 429 rate limit retries"
                >
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Warn ({{ $summary['warning'] }})</span>
                </button>

                <!-- Fail (Rose/Crimson) -->
                <button 
                    type="button" 
                    wire:click="$set('graphStatusFilter', 'fail')"
                    class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5 {{ $statusFilter === 'fail' ? 'bg-red-500/20 text-red-300 font-bold border border-red-500/40 shadow-sm' : 'text-slate-400 hover:text-red-300' }}"
                    title="Filter 500 error and connection timeouts"
                >
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Fail ({{ $summary['fail'] }})</span>
                </button>
            </div>

            <!-- Time Window Selector (1h, 5h, 12h, 24h) -->
            <div class="flex items-center bg-slate-900/90 p-1 rounded-xl border border-white/10 text-xs font-mono font-bold shadow-inner">
                @foreach([1 => '1H', 5 => '5H', 12 => '12H', 24 => '24H'] as $val => $lbl)
                    <button 
                        type="button" 
                        wire:click="$set('graphTimeRange', {{ $val }})"
                        class="px-3 py-1.5 rounded-lg transition-all cursor-pointer {{ (int)$timeRange === $val ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                    >
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- KPI Performance Ribbon -->
    <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-3 py-4">
        <div class="p-3.5 rounded-xl bg-slate-900/70 border border-white/5 shadow-inner">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Requests</span>
            <div class="text-xl font-black text-white font-mono mt-0.5 flex items-center justify-between">
                <span>{{ number_format($summary['total_requests']) }}</span>
                <span class="text-xs font-normal text-slate-400">calls</span>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-900/70 border border-white/5 shadow-inner">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Reliability SLA</span>
            <div class="text-xl font-black text-emerald-400 font-mono mt-0.5 flex items-center justify-between">
                <span>{{ $summary['success_rate'] }}%</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-300 font-bold">HEALTHY</span>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-900/70 border border-white/5 shadow-inner">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Average Latency</span>
            <div class="text-xl font-black text-indigo-300 font-mono mt-0.5 flex items-center justify-between">
                <span>{{ $summary['avg_latency_ms'] }} <span class="text-xs text-slate-400 font-normal">ms</span></span>
                <span class="text-[10px] text-slate-400 font-normal">Loopback</span>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-900/70 border border-white/5 shadow-inner">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tokens Streamed</span>
            <div class="text-xl font-black text-violet-300 font-mono mt-0.5 flex items-center justify-between">
                <span>{{ number_format($summary['total_tokens']) }}</span>
                <span class="text-xs font-normal text-slate-400">tok</span>
            </div>
        </div>
    </div>

    <!-- Premium Multi-Layer Curved SVG Area Chart Canvas -->
    <div 
        wire:key="telemetry-svg-container-{{ $timeRange }}-{{ $statusFilter }}"
        x-ref="svgContainer"
        x-on:mousemove="handleMouseMove($event)"
        x-on:mouseleave="handleMouseLeave()"
        class="relative w-full h-44 sm:h-48 bg-slate-950/80 rounded-xl border border-white/5 pt-4 pb-2 px-2 overflow-visible select-none cursor-crosshair"
    >
        <!-- Horizontal Subtle Gridlines & Y-Axis Scale Markers -->
        <div class="absolute inset-0 px-4 py-3 flex flex-col justify-between pointer-events-none opacity-25 text-[10px] font-mono text-slate-400">
            <div class="border-b border-dashed border-white/20 pb-0.5 flex justify-between">
                <span>Peak ({{ $maxReq }} req)</span>
                <span>100%</span>
            </div>
            <div class="border-b border-dashed border-white/20 pb-0.5 flex justify-between">
                <span>{{ (int) round($maxReq * 0.5) }} req</span>
                <span>50%</span>
            </div>
            <div class="flex justify-between">
                <span>0 req</span>
                <span>0%</span>
            </div>
        </div>

        <!-- SVG Vector Path Canvas -->
        <svg viewBox="0 0 800 160" preserveAspectRatio="none" class="w-full h-full relative z-10 overflow-visible">
            <defs>
                <!-- Multi-Layer Neon Linear Gradients -->
                <linearGradient id="grad-violet" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="rgba(139, 92, 246, 0.40)" />
                    <stop offset="100%" stop-color="rgba(99, 102, 241, 0.0)" />
                </linearGradient>

                <linearGradient id="grad-emerald" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="rgba(16, 185, 129, 0.45)" />
                    <stop offset="100%" stop-color="rgba(16, 185, 129, 0.0)" />
                </linearGradient>

                <linearGradient id="grad-sky" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="rgba(56, 189, 248, 0.45)" />
                    <stop offset="100%" stop-color="rgba(56, 189, 248, 0.0)" />
                </linearGradient>

                <linearGradient id="grad-amber" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="rgba(251, 191, 36, 0.45)" />
                    <stop offset="100%" stop-color="rgba(251, 191, 36, 0.0)" />
                </linearGradient>

                <linearGradient id="grad-rose" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="rgba(244, 63, 94, 0.45)" />
                    <stop offset="100%" stop-color="rgba(244, 63, 94, 0.0)" />
                </linearGradient>

                <!-- Filters for Neon Drop Shadow -->
                <filter id="glow-violet" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#8b5cf6" flood-opacity="0.6" />
                </filter>
                <filter id="glow-emerald" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#10b981" flood-opacity="0.6" />
                </filter>
                <filter id="glow-sky" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#38bdf8" flood-opacity="0.6" />
                </filter>
                <filter id="glow-amber" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#fbbf24" flood-opacity="0.6" />
                </filter>
                <filter id="glow-rose" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#f43f5e" flood-opacity="0.6" />
                </filter>
            </defs>

            @if($statusFilter === 'all')
                <!-- Layer 1: Pass Layer (Emerald Green) -->
                @if(!empty($svgPaths['pass']['area']))
                    <path d="{{ $svgPaths['pass']['area'] }}" fill="url(#grad-emerald)" class="transition-all duration-500 ease-out" />
                    <path d="{{ $svgPaths['pass']['line'] }}" fill="none" stroke="#10b981" stroke-width="2" filter="url(#glow-emerald)" class="transition-all duration-500 ease-out" />
                @endif

                <!-- Layer 2: Info Layer (Sky Blue) -->
                @if(!empty($svgPaths['info']['area']) && $summary['info'] > 0)
                    <path d="{{ $svgPaths['info']['area'] }}" fill="url(#grad-sky)" class="transition-all duration-500 ease-out" />
                    <path d="{{ $svgPaths['info']['line'] }}" fill="none" stroke="#38bdf8" stroke-width="2" filter="url(#glow-sky)" class="transition-all duration-500 ease-out" />
                @endif

                <!-- Layer 3: Warning Layer (Amber Gold) -->
                @if(!empty($svgPaths['warning']['area']) && $summary['warning'] > 0)
                    <path d="{{ $svgPaths['warning']['area'] }}" fill="url(#grad-amber)" class="transition-all duration-500 ease-out" />
                    <path d="{{ $svgPaths['warning']['line'] }}" fill="none" stroke="#fbbf24" stroke-width="2" filter="url(#glow-amber)" class="transition-all duration-500 ease-out" />
                @endif

                <!-- Layer 4: Fail Layer (Rose Crimson) -->
                @if(!empty($svgPaths['fail']['area']) && $summary['fail'] > 0)
                    <path d="{{ $svgPaths['fail']['area'] }}" fill="url(#grad-rose)" class="transition-all duration-500 ease-out" />
                    <path d="{{ $svgPaths['fail']['line'] }}" fill="none" stroke="#f43f5e" stroke-width="2.5" filter="url(#glow-rose)" class="transition-all duration-500 ease-out" />
                @endif

                <!-- Primary Layer: Overall Combined Traffic Line (Violet Neon Spline) -->
                @if(!empty($svgPaths['all']['line']))
                    <path d="{{ $svgPaths['all']['line'] }}" fill="none" stroke="#a78bfa" stroke-width="2.5" stroke-dasharray="4 2" filter="url(#glow-violet)" class="transition-all duration-500 ease-out" />
                @endif
            @else
                <!-- Single Focused Color Layer -->
                @php
                    $activePath = $svgPaths[$statusFilter] ?? $svgPaths['all'];
                    $gradId = match($statusFilter) {
                        'pass' => 'grad-emerald',
                        'info' => 'grad-sky',
                        'warning' => 'grad-amber',
                        'fail' => 'grad-rose',
                        default => 'grad-violet',
                    };
                    $glowId = match($statusFilter) {
                        'pass' => 'glow-emerald',
                        'info' => 'glow-sky',
                        'warning' => 'glow-amber',
                        'fail' => 'glow-rose',
                        default => 'glow-violet',
                    };
                @endphp
                <path d="{{ $activePath['area'] }}" fill="url(#{{ $gradId }})" class="transition-all duration-500 ease-out" />
                <path d="{{ $activePath['line'] }}" fill="none" stroke="{{ $activeTheme['stroke'] }}" stroke-width="3" filter="url(#{{ $glowId }})" class="transition-all duration-500 ease-out" />
            @endif

            <!-- Interactive Cursor Tracking Line -->
            <g x-show="$data.activePoint" x-cloak style="display: none;">
                <!-- Vertical Crosshair Line -->
                <line 
                    :x1="($data.activePoint && $data.activePoint.x !== undefined) ? $data.activePoint.x : 0" 
                    y1="10" 
                    :x2="($data.activePoint && $data.activePoint.x !== undefined) ? $data.activePoint.x : 0" 
                    y2="155" 
                    stroke="rgba(255, 255, 255, 0.4)" 
                    stroke-width="1.5" 
                    stroke-dasharray="3 3" 
                />
                <!-- Outer Glowing Ring -->
                <circle 
                    :cx="($data.activePoint && $data.activePoint.x !== undefined) ? $data.activePoint.x : 0" 
                    :cy="($data.activePoint && $data.activePoint.y !== undefined) ? $data.activePoint.y : 0" 
                    r="7" 
                    fill="none" 
                    stroke="{{ $activeTheme['stroke'] }}" 
                    stroke-width="2.5" 
                    class="animate-ping"
                />
                <!-- Inner Solid Dot -->
                <circle 
                    :cx="($data.activePoint && $data.activePoint.x !== undefined) ? $data.activePoint.x : 0" 
                    :cy="($data.activePoint && $data.activePoint.y !== undefined) ? $data.activePoint.y : 0" 
                    r="5" 
                    fill="#ffffff" 
                    stroke="{{ $activeTheme['stroke'] }}" 
                    stroke-width="2.5" 
                />
            </g>
        </svg>

        <!-- Floating Live HUD Tooltip -->
        <div 
            x-show="$data.activePoint && $data.activePoint.bucket"
            x-cloak
            class="absolute z-30 pointer-events-none min-w-[170px] p-3 rounded-xl bg-slate-900/95 border border-violet-500/40 shadow-2xl text-xs font-mono text-white backdrop-blur-xl transition-all duration-75"
            :style="($data.activePoint && $data.activePoint.x !== undefined) ? ('top: 10px; left: ' + Math.min(80, Math.max(10, ($data.activePoint.x / 800) * 100)) + '%; transform: translateX(-50%);') : 'display: none;'"
            style="display: none;"
        >
            <div class="font-bold text-violet-300 pb-1.5 mb-1.5 border-b border-white/10 flex items-center justify-between">
                <span x-text="'🕒 ' + ($data.activePoint?.bucket?.time_label ?? '')"></span>
                <span class="text-emerald-400 font-bold" x-text="($data.activePoint?.bucket?.avg_latency ?? 0) + 'ms'"></span>
            </div>
            <div class="space-y-1 text-[11px]">
                <div class="flex items-center justify-between text-slate-300">
                    <span>Total Calls:</span>
                    <span class="font-bold text-white" x-text="$data.activePoint?.bucket?.total_requests ?? 0"></span>
                </div>
                <div class="flex items-center justify-between text-emerald-400">
                    <span>🟢 Pass (200 OK):</span>
                    <span class="font-bold" x-text="$data.activePoint?.bucket?.pass ?? 0"></span>
                </div>
                <div class="flex items-center justify-between text-sky-400">
                    <span>🔵 Info (Routed):</span>
                    <span class="font-bold" x-text="$data.activePoint?.bucket?.info ?? 0"></span>
                </div>
                <div class="flex items-center justify-between text-amber-400">
                    <span>🟡 Warn (Retried):</span>
                    <span class="font-bold" x-text="$data.activePoint?.bucket?.warning ?? 0"></span>
                </div>
                <div class="flex items-center justify-between text-rose-400">
                    <span>🔴 Fail (Error):</span>
                    <span class="font-bold" x-text="$data.activePoint?.bucket?.fail ?? 0"></span>
                </div>
                <div class="flex items-center justify-between text-violet-300 pt-1 border-t border-white/5 font-semibold">
                    <span>Tokens Streamed:</span>
                    <span x-text="Number($data.activePoint?.bucket?.tokens ?? 0).toLocaleString()"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Axis Footer & Multi-Color Legend -->
    <div class="relative z-10 flex flex-col sm:flex-row justify-between items-center text-[11px] font-mono text-slate-400 mt-2 px-1 gap-2">
        <span>{{ $buckets[0]['time_label'] ?? 'Start' }}</span>
        
        <!-- Multi-Color Layer Indicators Legend -->
        <div class="flex flex-wrap items-center gap-3 text-[10px]">
            <span class="flex items-center gap-1 text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Pass
            </span>
            <span class="flex items-center gap-1 text-sky-400">
                <span class="w-2 h-2 rounded-full bg-sky-400"></span> Info
            </span>
            <span class="flex items-center gap-1 text-amber-400">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span> Warn
            </span>
            <span class="flex items-center gap-1 text-rose-400">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Fail
            </span>
            <span class="flex items-center gap-1 text-violet-400">
                <span class="w-2 h-2 rounded-full bg-violet-400"></span> Combined Spline
            </span>
        </div>

        <span class="text-emerald-400 font-bold">Now (Live)</span>
    </div>
</div>
