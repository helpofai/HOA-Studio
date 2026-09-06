{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Integration Card
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
--}}

@php
    $pluginService = app(\App\Features\WordPress\Services\WordPressPluginService::class);
    $pluginInfo = $pluginService->getPluginInfo();
@endphp

<div class="hoa-wordpress-plugin-suite">
    <x-glass.card variant="elevated" class="p-6 sm:p-7 space-y-6">
        <!-- Plugin Header & Architecture Showcase -->
        <div class="plugin-header-section flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-5 border-b border-white/10">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-600 flex items-center justify-center text-xl shadow-lg shadow-indigo-500/20 shrink-0 select-none">
                    🔌
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base sm:text-lg font-bold text-white tracking-tight">HOA Studio WordPress Suite</h3>
                        <x-glass.badge variant="purple" size="sm">
                            v{{ $pluginInfo['version'] }}
                        </x-glass.badge>
                        <x-glass.badge variant="emerald" size="sm">
                            GPLv2 / Enterprise
                        </x-glass.badge>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Supercharge any self-hosted WordPress site with HOA-Studio’s flagship 3-column TipTap AI Editor, real-time SSE streaming copilot, Gutenberg AI blocks, and bidirectional post sync.
                    </p>
                </div>
            </div>

            <!-- Download Button -->
            <div class="plugin-download-section shrink-0 w-full md:w-auto">
                <a 
                    href="{{ route('dashboard.wordpress.download') }}" 
                    class="inline-flex items-center justify-center gap-2.5 w-full md:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all cursor-pointer hover:scale-[1.02] active:scale-95 text-center"
                >
                    <span class="text-base">📦</span>
                    <span>Download Plugin (.zip)</span>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-black/30 border border-white/20">
                        {{ $pluginInfo['zip_size_human'] }}
                    </span>
                </a>
            </div>
        </div>

        <!-- System Architecture & Capabilities Matrix -->
        <div class="plugin-meta-matrix grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-white/5 space-y-1">
                <div class="text-[10.5px] font-mono text-slate-400 uppercase tracking-wider">Editor Canvas</div>
                <div class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <span>✨</span>
                    <span>TipTap 3.30 + Gutenberg</span>
                </div>
                <div class="text-[10px] text-slate-400">Fullscreen distracted-free or native blocks</div>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-white/5 space-y-1">
                <div class="text-[10.5px] font-mono text-slate-400 uppercase tracking-wider">AI Copilot</div>
                <div class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <span>⚡</span>
                    <span>Direct SSE Streaming</span>
                </div>
                <div class="text-[10px] text-slate-400">Zero-latency token generation via OmniRoute</div>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-white/5 space-y-1">
                <div class="text-[10.5px] font-mono text-slate-400 uppercase tracking-wider">Security Engine</div>
                <div class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <span>🛡️</span>
                    <span>SHA-256 Token Bearer</span>
                </div>
                <div class="text-[10px] text-slate-400">Scoped per-site connect authorization</div>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-white/5 space-y-1">
                <div class="text-[10.5px] font-mono text-slate-400 uppercase tracking-wider">SEO Bridge</div>
                <div class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <span>🎯</span>
                    <span>Rank Math & Yoast Sync</span>
                </div>
                <div class="text-[10px] text-slate-400">Automatic focus keyword & meta updates</div>
            </div>
        </div>

        <!-- 3-Step Fast Onboarding Guide -->
        <div class="plugin-install-guide p-4 rounded-xl bg-slate-950/40 border border-white/5 space-y-3">
            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span>🚀</span>
                <span>Quick 3-Step Setup Instructions</span>
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-300">
                <div class="p-3 rounded-lg bg-slate-900/60 border border-white/5 space-y-1">
                    <div class="font-mono text-indigo-400 font-bold text-[11px]">Step 1: Install Plugin</div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Download the zip above, log in to WordPress Admin, go to <strong class="text-slate-200">Plugins &rarr; Add New &rarr; Upload Plugin</strong>, and click Activate.
                    </p>
                </div>
                <div class="p-3 rounded-lg bg-slate-900/60 border border-white/5 space-y-1">
                    <div class="font-mono text-purple-400 font-bold text-[11px]">Step 2: Generate Key</div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Generate a new <strong class="text-slate-200">Studio Connect Key</strong> below for your WordPress site domain or staging URL.
                    </p>
                </div>
                <div class="p-3 rounded-lg bg-slate-900/60 border border-white/5 space-y-1">
                    <div class="font-mono text-emerald-400 font-bold text-[11px]">Step 3: Connect & Write</div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        In WordPress, open <strong class="text-slate-200">HOA Studio &rarr; Connection</strong>, paste your key, and click <em>Test Connection</em> to activate.
                    </p>
                </div>
            </div>
        </div>
    </x-glass.card>
</div>
