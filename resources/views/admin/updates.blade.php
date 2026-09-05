{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Updates & Rollback Blade View
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

<div class="space-y-8" x-data="{ activeTab: @entangle('activeTab'), showLogs: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>Core Updates & Time Machine</span>
                <x-glass.badge variant="violet">v{{ $updateInfo['current_version'] ?? '2.5.0' }}</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Zero-downtime GitHub updates, isolated database snapshots, and instant self-healing rollback.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                wire:click="createSnapshot" 
                wire:loading.attr="disabled"
                class="px-4 py-2 rounded-xl bg-slate-900 border border-white/15 text-slate-200 hover:text-white hover:border-violet-500/40 text-xs font-semibold shadow-md transition-all flex items-center gap-2 cursor-pointer"
            >
                <span>💾</span>
                <span wire:loading.remove wire:target="createSnapshot">Create Full Snapshot</span>
                <span wire:loading wire:target="createSnapshot">Archiving Codebase...</span>
            </button>

            <button 
                wire:click="triggerCheck" 
                wire:loading.attr="disabled"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/25 transition-all flex items-center gap-2 cursor-pointer"
            >
                <span wire:loading.remove wire:target="triggerCheck">🔄</span>
                <span wire:loading wire:target="triggerCheck" class="animate-spin">⏳</span>
                <span wire:loading.remove wire:target="triggerCheck">Check Updates</span>
                <span wire:loading wire:target="triggerCheck">Checking GitHub...</span>
            </button>
        </div>
    </div>

    <!-- Alert / Feedback Banner -->
    @if($feedbackMessage)
        <div class="p-4 rounded-2xl border backdrop-blur-xl flex items-center justify-between gap-3 animate-in {{ $feedbackType === 'success' ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-200' : ($feedbackType === 'error' ? 'bg-rose-950/80 border-rose-500/40 text-rose-200' : 'bg-slate-900/90 border-white/15 text-slate-200') }}">
            <div class="flex items-center gap-3 text-xs sm:text-sm font-medium">
                <span>{{ $feedbackType === 'success' ? '✅' : ($feedbackType === 'error' ? '❌' : 'ℹ️') }}</span>
                <span>{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-white/10 pb-3">
        <button 
            type="button"
            @click="activeTab = 'core'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'core' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>🚀</span>
            <span>Core Codebase Updates</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'database'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'database' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>🗄️</span>
            <span>Database Updates & Rollback</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-900 text-slate-300 font-mono">{{ count($dbSnapshots) }}</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'health'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'health' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>🛡️</span>
            <span>System Health Diagnostics</span>
        </button>
    </div>

    <!-- TAB 1: CORE CODEBASE UPDATES -->
    <div x-show="activeTab === 'core'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Update Banner -->
            <div class="lg:col-span-2 space-y-6">
                <x-glass.card variant="elevated" class="p-6 sm:p-8 border border-white/15 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/10">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Current Deployment</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                    ACTIVE
                                </span>
                            </div>
                            <div class="text-2xl sm:text-3xl font-black text-white flex items-center gap-3">
                                <span>HelpOfAi Studio v{{ $updateInfo['current_version'] ?? '2.5.0' }}</span>
                            </div>
                            @if(!empty($updateInfo['commit_sha']))
                                <div class="text-xs font-mono text-slate-400 mt-1">Commit SHA: <span class="text-indigo-300 font-semibold">{{ $updateInfo['commit_sha'] }}</span></div>
                            @endif
                        </div>

                        @if(!empty($updateInfo['has_update']))
                            <button 
                                wire:click="applyUpdate" 
                                wire:loading.attr="disabled"
                                class="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 hover:scale-105 active:scale-95 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/20 transition-all flex items-center gap-2.5 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="applyUpdate">⚡</span>
                                <span wire:loading wire:target="applyUpdate" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                                
                                <span wire:loading.remove wire:target="applyUpdate">Apply Update to {{ $updateInfo['latest_version'] }}</span>
                                <span wire:loading wire:target="applyUpdate">Deploying & Health Checking...</span>
                            </button>
                        @else
                            <button 
                                wire:click="applyUpdate" 
                                wire:loading.attr="disabled"
                                class="px-4 py-2.5 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-xs font-bold text-slate-300 hover:text-white transition-all flex items-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
                                title="Re-sync and force verify with latest release"
                            >
                                <span wire:loading.remove wire:target="applyUpdate" class="text-emerald-400">●</span>
                                <span wire:loading wire:target="applyUpdate" class="w-3.5 h-3.5 border-2 border-violet-400 border-t-transparent rounded-full animate-spin"></span>
                                <span wire:loading.remove wire:target="applyUpdate">Re-Deploy / Force Sync</span>
                                <span wire:loading wire:target="applyUpdate">Syncing Live Deployment...</span>
                            </button>
                        @endif
                    </div>

                    <!-- GitHub Live Delta & Changed Files Preview -->
                    @if(!empty($updateInfo['has_update']))
                        <div class="pt-6 border-b border-white/10 pb-6 space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 p-3.5 rounded-xl bg-violet-950/40 border border-violet-500/30">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">🚀</span>
                                    <div>
                                        <div class="text-xs font-bold text-white flex items-center gap-2">
                                            <span>New Release Ready: <span class="text-emerald-300 font-mono">{{ $updateInfo['latest_version'] }}</span></span>
                                            @if(!empty($updateInfo['latest_sha']))
                                                <span class="px-1.5 py-0.5 rounded bg-violet-900/60 text-violet-300 text-[10px] font-mono">commit {{ $updateInfo['latest_sha'] }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-300">
                                            Direct from <a href="https://github.com/helpofai/HOA-Studio" target="_blank" class="text-indigo-400 hover:underline">github.com/helpofai/HOA-Studio</a>
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($updateInfo['commits_behind']) && $updateInfo['commits_behind'] > 0)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                        {{ $updateInfo['commits_behind'] }} commits ahead
                                    </span>
                                @endif
                            </div>

                            <!-- Changed / Updated Files Preview -->
                            @if(!empty($updateInfo['changed_files']))
                                <div class="space-y-2">
                                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                        <span>Updated Files In This Release</span>
                                        <span class="font-mono text-slate-500">{{ count($updateInfo['changed_files']) }} files changed</span>
                                    </h5>
                                    <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto custom-scrollbar p-2 rounded-xl bg-slate-900/60 border border-white/5">
                                        @foreach($updateInfo['changed_files'] as $cf)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono flex items-center gap-1.5 {{ ($cf['status'] ?? '') === 'added' ? 'bg-emerald-500/20 text-emerald-300' : (($cf['status'] ?? '') === 'removed' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-800 text-slate-300') }}">
                                                <span class="font-bold">{{ ($cf['status'] ?? '') === 'added' ? '+' : (($cf['status'] ?? '') === 'removed' ? '-' : '•') }}</span>
                                                <span>{{ $cf['filename'] ?? '' }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Recent Commits In This Release -->
                            @if(!empty($updateInfo['recent_commits']))
                                <div class="space-y-2">
                                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                        Recent GitHub Commits
                                    </h5>
                                    <div class="space-y-1.5">
                                        @foreach($updateInfo['recent_commits'] as $rc)
                                            <div class="flex items-start justify-between gap-2 p-2 rounded-lg bg-slate-900/40 border border-white/5 text-xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-[10px] text-indigo-400 font-bold bg-indigo-950/80 px-1.5 py-0.5 rounded">{{ $rc['sha'] }}</span>
                                                    <span class="text-slate-200 truncate max-w-md">{{ $rc['message'] }}</span>
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ $rc['author'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Release Notes / Details -->
                    <div class="pt-6 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                            <span>Latest Release Notes & Commit Message</span>
                            <span class="text-[10px] font-mono text-slate-500">{{ $updateInfo['published_at'] ?? 'Verified Release' }}</span>
                        </h4>
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-white/5 font-mono text-xs text-slate-300 whitespace-pre-wrap max-h-48 overflow-y-auto custom-scrollbar">
                            {{ $updateInfo['release_notes'] ?? 'No release notes provided.' }}
                        </div>
                    </div>

                    <!-- Enterprise Terminal Output Console (Live Real-Time Telemetry) -->
                    <div 
                        class="mt-6 pt-6 border-t border-white/10 space-y-3" 
                        x-data="{ 
                            copied: false,
                            autoScroll: true,
                            scrollToBottom() {
                                if (this.autoScroll && this.$refs.terminalBody) {
                                    this.$refs.terminalBody.scrollTop = this.$refs.terminalBody.scrollHeight;
                                }
                            }
                        }"
                        x-init="$watch('$wire.updateLogs', () => { $nextTick(() => scrollToBottom()); })"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500/90 shadow-[0_0_6px_#f43f5e]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500/90 shadow-[0_0_6px_#f59e0b]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/90 shadow-[0_0_6px_#10b981]"></span>
                                </div>
                                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-200 ml-1 flex items-center gap-2">
                                    <span>hoa-updater@terminal: ~</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-normal">
                                        {{ count($updateLogs) }} lines
                                    </span>
                                </h4>
                            </div>

                            <div class="flex items-center gap-2">
                                <div wire:loading wire:target="applyUpdate,runDbMigrations,rollbackMigrationStep,createSnapshot,createDbSnapshot" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[10px] font-mono font-bold animate-pulse">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                                    <span>STREAMING TELEMETRY LIVE</span>
                                </div>

                                <div wire:loading.remove wire:target="applyUpdate,runDbMigrations,rollbackMigrationStep,createSnapshot,createDbSnapshot" class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-mono font-medium border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>READY</span>
                                </div>

                                @if(!empty($updateLogs))
                                    <button 
                                        type="button" 
                                        wire:click="clearTerminalLogs"
                                        class="px-2 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-rose-500/40 text-[10px] font-mono text-slate-400 hover:text-rose-300 transition-colors cursor-pointer"
                                        title="Clear all logs"
                                    >
                                        🧹 Clear
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="navigator.clipboard.writeText(JSON.stringify(@js($updateLogs), null, 2)); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="px-2 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-violet-500/40 text-[10px] font-mono text-slate-400 hover:text-white transition-colors cursor-pointer flex items-center gap-1"
                                    >
                                        <span x-show="!copied">📋 Copy</span>
                                        <span x-show="copied" class="text-emerald-400 font-bold">✓ Copied</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Terminal Output Window -->
                        <div 
                            x-ref="terminalBody"
                            class="p-4 rounded-xl bg-slate-950/95 border border-white/15 font-mono text-xs text-slate-300 space-y-2 max-h-80 overflow-y-auto custom-scrollbar shadow-2xl select-text"
                        >
                            @if(empty($updateLogs))
                                <div class="text-slate-500 italic flex items-center gap-2 py-2">
                                    <span class="text-indigo-400 font-bold">&gt;</span>
                                    <span>Console idle. Click "Apply Update", "Re-Deploy", or run database migrations to stream real-time execution telemetry.</span>
                                </div>
                            @else
                                @foreach($updateLogs as $logEntry)
                                    @php
                                        $type = is_array($logEntry) ? ($logEntry['type'] ?? 'info') : 'info';
                                        $msg = is_array($logEntry) ? ($logEntry['message'] ?? '') : $logEntry;
                                        $time = is_array($logEntry) ? ($logEntry['time'] ?? date('H:i:s')) : date('H:i:s');
                                    @endphp
                                    <div class="flex items-start gap-2.5 leading-relaxed font-mono">
                                        <span class="text-[10px] text-slate-500 select-none shrink-0 pt-0.5">[{{ $time }}]</span>
                                        @if($type === 'command')
                                            <span class="text-cyan-400 font-black shrink-0">&gt;&gt;</span>
                                            <span class="text-cyan-200 font-semibold">{{ $msg }}</span>
                                        @elseif($type === 'success')
                                            <span class="text-emerald-400 font-bold shrink-0">✔</span>
                                            <span class="text-emerald-300 font-medium">{{ $msg }}</span>
                                        @elseif($type === 'warning')
                                            <span class="text-amber-400 font-bold shrink-0">⚠</span>
                                            <span class="text-amber-300">{{ $msg }}</span>
                                        @elseif($type === 'error')
                                            <span class="text-rose-400 font-bold shrink-0">✖</span>
                                            <span class="text-rose-300 font-bold">{{ $msg }}</span>
                                        @else
                                            <span class="text-indigo-400 font-bold shrink-0">&bull;</span>
                                            <span class="text-slate-300">{{ $msg }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </x-glass.card>
            </div>

            <!-- Version Meta Card -->
            <div class="space-y-6">
                <x-glass.card variant="standard" class="p-6 space-y-4">
                    <h3 class="text-sm font-bold text-white tracking-tight pb-3 border-b border-white/10 flex items-center gap-2">
                        <span>📦 Version Management Spec</span>
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">App Name</span>
                            <span class="font-bold text-white">{{ $versionMeta['name'] ?? 'HelpOfAi Studio' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Core Version</span>
                            <span class="font-mono text-violet-300 font-bold">v{{ $versionMeta['version'] ?? '2.5.0' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Build Number</span>
                            <span class="font-mono text-slate-300">{{ $versionMeta['build_number'] ?? '20260824.1' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Build Channel</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-300 font-mono">{{ $versionMeta['channel'] ?? 'stable' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">PHP Requirement</span>
                            <span class="font-mono text-slate-300">&gt;= {{ $versionMeta['min_php_version'] ?? '8.2.0' }} (Running {{ phpversion() }})</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Schema Version</span>
                            <span class="font-mono text-indigo-300 text-[11px] truncate max-w-[150px]">{{ $versionMeta['schema_version'] ?? '2026_08_24_000001' }}</span>
                        </div>
                    </div>
                </x-glass.card>

                <!-- GitHub Live Repository Sync -->
                <x-glass.card variant="standard" class="p-6 space-y-4 border border-violet-500/20">
                    <h3 class="text-sm font-bold text-white tracking-tight pb-3 border-b border-white/10 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span>🌐 GitHub Live Sync</span>
                        </span>
                        @if(!empty($updateInfo['connection_error']))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">OFFLINE</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">CONNECTED</span>
                        @endif
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Repository</span>
                            <a href="https://github.com/helpofai/HOA-Studio" target="_blank" class="font-mono text-indigo-400 hover:text-indigo-300 font-semibold hover:underline">helpofai/HOA-Studio</a>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Live Branch</span>
                            <span class="font-mono text-slate-200 font-bold">main</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Remote Version</span>
                            <span class="font-mono text-emerald-300 font-bold">v{{ $updateInfo['remote_version_meta']['version'] ?? $updateInfo['latest_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Remote Build No.</span>
                            <span class="font-mono text-slate-300">{{ $updateInfo['remote_version_meta']['build_number'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-900/80 border border-white/5">
                            <span class="text-slate-400">Latest Commit ID</span>
                            <span class="font-mono text-indigo-300 font-bold">{{ $updateInfo['latest_sha'] ?? 'N/A' }}</span>
                        </div>
                        @if(!empty($updateInfo['connection_error']))
                            <div class="p-2.5 rounded-lg bg-rose-950/60 border border-rose-500/30 text-rose-200 text-[11px] leading-relaxed">
                                ⚠ {{ $updateInfo['connection_error'] }}
                            </div>
                        @endif
                    </div>
                </x-glass.card>
            </div>
        </div>

        <!-- Full Codebase Snapshot Restore Points -->
        <div class="space-y-4" x-data="{ optionsOpen: false }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                        <span>⏱️ Codebase Snapshot Restore Points</span>
                    </h3>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-full bg-slate-800/80 text-slate-400 border border-white/10">
                        {{ count($restorePoints) }} saved
                    </span>
                    @php
                        $totalDiskMb = array_sum(array_column($restorePoints, 'file_size_mb'));
                    @endphp
                    @if($totalDiskMb > 0)
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-violet-950/60 text-violet-300 border border-violet-500/30">
                            📦 {{ round($totalDiskMb, 1) }} MB total
                        </span>
                    @endif
                </div>

                <!-- Top Quick Action Buttons & Selection Presets -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Selection Presets Dropdown -->
                    @if(count($restorePoints) > 0)
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                type="button" 
                                @click="open = !open" 
                                @click.outside="open = false"
                                class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-xs font-semibold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
                            >
                                <span>☑️ Bulk Select</span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div 
                                x-show="open" 
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-52 rounded-2xl bg-slate-900/98 border border-white/20 p-2 shadow-2xl z-30 space-y-1 backdrop-blur-2xl text-xs"
                                style="display: none;"
                            >
                                <button 
                                    type="button" 
                                    wire:click="selectAllRestorePoints" 
                                    @click="open = false" 
                                    class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-violet-600/20 text-slate-300 hover:text-white transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    <span>Select All</span>
                                    <span class="font-mono text-[10px] text-slate-500">{{ count($restorePoints) }}</span>
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="selectAutoSnapshots" 
                                    @click="open = false" 
                                    class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-violet-600/20 text-slate-300 hover:text-white transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    <span>Select Auto Updates</span>
                                    <span>🤖</span>
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="selectManualSnapshots" 
                                    @click="open = false" 
                                    class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-violet-600/20 text-slate-300 hover:text-white transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    <span>Select Manual Snapshots</span>
                                    <span>👤</span>
                                </button>
                                <div class="border-t border-white/5 my-1"></div>
                                <button 
                                    type="button" 
                                    wire:click="clearSelectedRestorePoints" 
                                    @click="open = false" 
                                    class="w-full text-left px-3 py-1.5 rounded-lg hover:bg-white/5 text-slate-400 hover:text-slate-200 transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    <span>Clear Selection</span>
                                    <span>✕</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Prune Older Snapshots Quick Button -->
                    @if(count($restorePoints) > 3)
                        <button 
                            type="button" 
                            wire:click="pruneOlderRestorePoints(3)" 
                            wire:confirm="Prune older snapshots and keep only the 3 most recent backups? Older archives will be permanently removed to free disk space."
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-amber-500/40 text-xs font-semibold text-amber-300 hover:text-amber-200 transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
                            title="Keep 3 latest snapshots and delete all older ones"
                        >
                            <span>🧹</span>
                            <span>Prune (Keep 3)</span>
                        </button>
                    @endif

                    <!-- Create Snapshot Button -->
                    <button 
                        type="button" 
                        wire:click="createSnapshot"
                        wire:loading.attr="disabled"
                        class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold text-xs shadow-md shadow-violet-500/20 transition-all flex items-center gap-1.5 cursor-pointer"
                        title="Create an on-demand snapshot of the current codebase and database"
                    >
                        <span wire:loading.remove wire:target="createSnapshot">➕ Create Snapshot</span>
                        <span wire:loading wire:target="createSnapshot">Creating...</span>
                    </button>
                </div>
            </div>

            <!-- Floating / Docked Bulk Action Ribbon (Active when 1 or more snapshots are selected) -->
            @if(count($selectedRestorePoints) > 0)
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-violet-950/90 via-slate-900/95 to-indigo-950/90 border border-violet-500/40 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-3 animate-fade-in backdrop-blur-xl">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-violet-600 text-white font-mono font-bold text-xs shadow-md shadow-violet-600/30">
                            {{ count($selectedRestorePoints) }}
                        </span>
                        <div>
                            <span class="text-xs font-bold text-white">
                                {{ count($selectedRestorePoints) }} {{ Str::plural('snapshot', count($selectedRestorePoints)) }} selected
                            </span>
                            <span class="text-[11px] font-mono text-violet-300 ml-1.5">
                                (~{{ $this->getSelectedSizeMb() }} MB)
                            </span>
                        </div>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Bulk Download Button -->
                        <button 
                            type="button" 
                            wire:click="bulkDownloadRestorePoints" 
                            class="px-3 py-1.5 rounded-xl bg-slate-900/90 border border-white/10 hover:border-violet-500/40 text-xs font-semibold text-slate-200 hover:text-white transition-all flex items-center gap-1.5 shadow-sm cursor-pointer"
                            title="Download selected snapshot archives as zip"
                        >
                            <span>📥</span>
                            <span>Download ({{ count($selectedRestorePoints) }})</span>
                        </button>

                        <!-- Bulk Delete Button -->
                        <button 
                            type="button" 
                            wire:click="bulkDeleteRestorePoints" 
                            wire:confirm="Permanently delete the {{ count($selectedRestorePoints) }} selected snapshot archives from server disk? This action cannot be undone."
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 rounded-xl bg-rose-600/20 border border-rose-500/40 hover:bg-rose-600/40 text-xs font-semibold text-rose-300 hover:text-white transition-all flex items-center gap-1.5 shadow-sm cursor-pointer"
                            title="Permanently delete selected snapshot archives"
                        >
                            <span wire:loading.remove wire:target="bulkDeleteRestorePoints">🗑️ Delete ({{ count($selectedRestorePoints) }})</span>
                            <span wire:loading wire:target="bulkDeleteRestorePoints">Deleting...</span>
                        </button>

                        <!-- Clear Selection Button -->
                        <button 
                            type="button" 
                            wire:click="clearSelectedRestorePoints" 
                            class="px-2.5 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-medium text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
                            title="Clear selection"
                        >
                            ✕ Clear
                        </button>
                    </div>
                </div>
            @endif

            <x-glass.card variant="standard" class="p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/80 text-slate-400 border-b border-white/5 uppercase text-[10px]">
                            <tr>
                                <th class="p-4 w-10 text-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectAll" 
                                        class="rounded border-white/20 bg-slate-900 text-violet-600 focus:ring-violet-500 focus:ring-offset-slate-950 w-4 h-4 cursor-pointer" 
                                        title="Select or deselect all snapshots"
                                    />
                                </th>
                                <th class="p-4">Snapshot ID</th>
                                <th class="p-4">Label</th>
                                <th class="p-4">Version</th>
                                <th class="p-4">Codebase & DB Size</th>
                                <th class="p-4">Timestamp</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-200">
                            @forelse($restorePoints as $rp)
                                @php
                                    $isSelected = in_array($rp['id'], $selectedRestorePoints, true);
                                @endphp
                                <tr class="hover:bg-white/5 transition-colors {{ $isSelected ? 'bg-violet-600/15 border-l-2 border-violet-500' : '' }}">
                                    <td class="p-4 w-10 text-center">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="selectedRestorePoints" 
                                            value="{{ $rp['id'] }}" 
                                            class="rounded border-white/20 bg-slate-900 text-violet-600 focus:ring-violet-500 focus:ring-offset-slate-950 w-4 h-4 cursor-pointer"
                                        />
                                    </td>
                                    <td class="p-4 font-mono font-bold text-violet-300">
                                        {{ $rp['id'] }}
                                    </td>
                                    <td class="p-4 font-medium text-white">
                                        <div class="flex items-center gap-1.5">
                                            <span>🌐</span>
                                            <span>{{ $rp['label'] ?? 'Full Website Snapshot' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 font-mono text-indigo-300">
                                        v{{ $rp['version'] ?? '2.5.0' }}
                                        @if(!empty($rp['git_sha']))
                                            <span class="text-[10px] text-slate-500">({{ $rp['git_sha'] }})</span>
                                        @endif
                                    </td>
                                    <td class="p-4 font-mono text-slate-300">
                                        <div class="text-[11.5px]">📦 Code: <span class="text-white font-bold">{{ $rp['file_size_mb'] ?? 0 }} MB</span></div>
                                        @if(!empty($rp['db_size_kb']) && $rp['db_size_kb'] > 0)
                                            <div class="text-[10.5px] text-indigo-300">🗄️ DB: <span class="font-bold">{{ $rp['db_size_kb'] }} KB</span></div>
                                        @else
                                            <div class="text-[10px] text-slate-500">🗄️ DB: Included</div>
                                        @endif
                                    </td>
                                    <td class="p-4 text-slate-400 font-mono text-[11px]">
                                        {{ $rp['timestamp'] }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                wire:click="downloadRestorePoint('{{ $rp['id'] }}')"
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs font-semibold shadow-sm transition-all cursor-pointer flex items-center gap-1.5"
                                                title="Download complete website zip archive"
                                            >
                                                <span>📥</span>
                                                <span>Download</span>
                                            </button>

                                            <button 
                                                wire:click="rollbackTo('{{ $rp['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:confirm="Are you sure you want to roll back to this restore snapshot? All codebase files and database state will be reverted."
                                                class="px-3 py-1.5 rounded-lg bg-rose-600/20 text-rose-300 border border-rose-500/40 hover:bg-rose-600/40 hover:text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                                                title="Revert entire website (codebase + database) to this snapshot"
                                            >
                                                <span wire:loading.remove wire:target="rollbackTo('{{ $rp['id'] }}')">⚡ Rollback</span>
                                                <span wire:loading wire:target="rollbackTo('{{ $rp['id'] }}')">Restoring...</span>
                                            </button>

                                            <button 
                                                wire:click="deleteRestorePoint('{{ $rp['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:confirm="Permanently delete snapshot [{{ $rp['id'] }}] and remove its underlying backup archive from server storage?"
                                                class="p-1.5 rounded-lg bg-slate-900 border border-white/10 text-slate-400 hover:text-rose-400 hover:border-rose-500/40 transition-colors cursor-pointer"
                                                title="Delete Snapshot Archive"
                                            >
                                                <span wire:loading.remove wire:target="deleteRestorePoint('{{ $rp['id'] }}')">🗑️</span>
                                                <span wire:loading wire:target="deleteRestorePoint('{{ $rp['id'] }}')">⏳</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-500 font-mono text-xs">
                                        No restore snapshots recorded yet. Snapshots are created automatically before each update or on-demand.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>
    </div>

    <!-- TAB 2: DATABASE UPDATES & ROLLBACK -->
    <div x-show="activeTab === 'database'" class="space-y-6" style="display: none;">
        <!-- Database Control Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-glass.card variant="standard" class="p-6">
                <div class="text-xs font-semibold uppercase text-slate-400 mb-1">Active Engine</div>
                <div class="text-2xl font-black text-white font-mono">{{ $dbDetails['driver'] ?? 'SQLITE' }}</div>
                <div class="text-xs text-slate-400 mt-2 truncate">{{ $dbDetails['database'] ?? '' }}</div>
            </x-glass.card>

            <x-glass.card variant="standard" class="p-6">
                <div class="text-xs font-semibold uppercase text-slate-400 mb-1">Managed Schema Tables</div>
                <div class="text-2xl font-black text-indigo-400 font-mono">{{ $dbDetails['table_count'] ?? 0 }} Tables</div>
                <div class="text-xs text-slate-400 mt-2">Fully indexed & optimized</div>
            </x-glass.card>

            <x-glass.card variant="standard" class="p-6">
                <div class="text-xs font-semibold uppercase text-slate-400 mb-1">Schema Migrations</div>
                <div class="text-2xl font-black {{ ($migrationsData['pending_count'] ?? 0) > 0 ? 'text-amber-400' : 'text-emerald-400' }} font-mono">
                    {{ $migrationsData['applied_count'] ?? 0 }} / {{ $migrationsData['total_count'] ?? 0 }}
                </div>
                <div class="text-xs mt-2 {{ ($migrationsData['pending_count'] ?? 0) > 0 ? 'text-amber-300 font-semibold' : 'text-slate-400' }}">
                    {{ ($migrationsData['pending_count'] ?? 0) > 0 ? $migrationsData['pending_count'] . ' pending migration(s)' : 'All migrations executed' }}
                </div>
            </x-glass.card>

            <x-glass.card variant="standard" class="p-6">
                <div class="text-xs font-semibold uppercase text-slate-400 mb-1">Saved DB Snapshots</div>
                <div class="text-2xl font-black text-violet-400 font-mono">{{ count($dbSnapshots) }} Backups</div>
                <div class="text-xs text-slate-400 mt-2">Point-in-time recovery points</div>
            </x-glass.card>
        </div>

        <!-- Database Action Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl bg-slate-900/60 border border-white/10">
            <div class="flex flex-wrap items-center gap-3">
                <button 
                    wire:click="createDbSnapshot" 
                    wire:loading.attr="disabled"
                    class="px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-lg transition-all flex items-center gap-2 cursor-pointer"
                >
                    <span>📸</span>
                    <span wire:loading.remove wire:target="createDbSnapshot">Create DB Snapshot</span>
                    <span wire:loading wire:target="createDbSnapshot">Dumping Tables...</span>
                </button>

                <button 
                    wire:click="runDbMigrations" 
                    wire:loading.attr="disabled"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition-all flex items-center gap-2 cursor-pointer"
                >
                    <span wire:loading.remove wire:target="runDbMigrations">⚡</span>
                    <span wire:loading wire:target="runDbMigrations" class="animate-spin">⏳</span>
                    <span wire:loading.remove wire:target="runDbMigrations">Run Database Migrations</span>
                    <span wire:loading wire:target="runDbMigrations">Migrating Schema...</span>
                </button>

                <button 
                    wire:click="rollbackMigrationStep" 
                    wire:loading.attr="disabled"
                    wire:confirm="Roll back the last migration batch?"
                    class="px-4 py-2.5 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-500/40 font-bold text-xs shadow-lg transition-all flex items-center gap-2 cursor-pointer"
                >
                    <span>⏪</span>
                    <span wire:loading.remove wire:target="rollbackMigrationStep">Rollback Last Batch</span>
                    <span wire:loading wire:target="rollbackMigrationStep">Rolling Back...</span>
                </button>
            </div>

            <div class="text-xs text-slate-400 font-mono">
                Auto-Snapshot Before Migration: <span class="text-emerald-400 font-bold">ENABLED</span>
            </div>
        </div>

        <!-- Database Terminal Output Console (Live Real-Time Telemetry) -->
        <x-glass.card variant="elevated" class="p-6 border border-white/10 space-y-3">
            <div 
                x-data="{ 
                    copied: false,
                    autoScroll: true,
                    scrollToBottom() {
                        if (this.autoScroll && this.$refs.dbTerminalBody) {
                            this.$refs.dbTerminalBody.scrollTop = this.$refs.dbTerminalBody.scrollHeight;
                        }
                    }
                }"
                x-init="$watch('$wire.updateLogs', () => { $nextTick(() => scrollToBottom()); })"
                class="space-y-3"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500/90 shadow-[0_0_6px_#f43f5e]"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500/90 shadow-[0_0_6px_#f59e0b]"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/90 shadow-[0_0_6px_#10b981]"></span>
                        </div>
                        <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-200 ml-1 flex items-center gap-2">
                            <span>hoa-database@terminal: ~</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-normal">
                                {{ count($updateLogs) }} lines
                            </span>
                        </h4>
                    </div>

                    <div class="flex items-center gap-2">
                        <div wire:loading wire:target="runDbMigrations,rollbackMigrationStep,createDbSnapshot,restoreDbSnapshot" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[10px] font-mono font-bold animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            <span>DATABASE ENGINE ACTIVE</span>
                        </div>

                        <div wire:loading.remove wire:target="runDbMigrations,rollbackMigrationStep,createDbSnapshot,restoreDbSnapshot" class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-mono font-medium border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>DB READY</span>
                        </div>

                        @if(!empty($updateLogs))
                            <button 
                                type="button" 
                                wire:click="clearTerminalLogs"
                                class="px-2 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-rose-500/40 text-[10px] font-mono text-slate-400 hover:text-rose-300 transition-colors cursor-pointer"
                                title="Clear all logs"
                            >
                                🧹 Clear
                            </button>

                            <button 
                                type="button" 
                                @click="navigator.clipboard.writeText(JSON.stringify(@js($updateLogs), null, 2)); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-2 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-violet-500/40 text-[10px] font-mono text-slate-400 hover:text-white transition-colors cursor-pointer flex items-center gap-1"
                            >
                                <span x-show="!copied">📋 Copy</span>
                                <span x-show="copied" class="text-emerald-400 font-bold">✓ Copied</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- DB Terminal Output Window -->
                <div 
                    x-ref="dbTerminalBody"
                    class="p-4 rounded-xl bg-slate-950/95 border border-white/15 font-mono text-xs text-slate-300 space-y-2 max-h-56 overflow-y-auto custom-scrollbar shadow-inner select-text"
                >
                    @if(empty($updateLogs))
                        <div class="text-slate-500 italic flex items-center gap-2 py-2">
                            <span class="text-indigo-400 font-bold">&gt;</span>
                            <span>Database console idle. Click "Run Database Migrations", "Rollback Last Batch", or "Create DB Snapshot" to stream SQL & migration events live.</span>
                        </div>
                    @else
                        @foreach($updateLogs as $logEntry)
                            @php
                                $type = is_array($logEntry) ? ($logEntry['type'] ?? 'info') : 'info';
                                $msg = is_array($logEntry) ? ($logEntry['message'] ?? '') : $logEntry;
                                $time = is_array($logEntry) ? ($logEntry['time'] ?? date('H:i:s')) : date('H:i:s');
                            @endphp
                            <div class="flex items-start gap-2.5 leading-relaxed font-mono">
                                <span class="text-[10px] text-slate-500 select-none shrink-0 pt-0.5">[{{ $time }}]</span>
                                @if($type === 'command')
                                    <span class="text-cyan-400 font-black shrink-0">&gt;&gt;</span>
                                    <span class="text-cyan-200 font-semibold">{{ $msg }}</span>
                                @elseif($type === 'success')
                                    <span class="text-emerald-400 font-bold shrink-0">✔</span>
                                    <span class="text-emerald-300 font-medium">{{ $msg }}</span>
                                @elseif($type === 'warning')
                                    <span class="text-amber-400 font-bold shrink-0">⚠</span>
                                    <span class="text-amber-300">{{ $msg }}</span>
                                @elseif($type === 'error')
                                    <span class="text-rose-400 font-bold shrink-0">✖</span>
                                    <span class="text-rose-300 font-bold">{{ $msg }}</span>
                                @else
                                    <span class="text-indigo-400 font-bold shrink-0">&bull;</span>
                                    <span class="text-slate-300">{{ $msg }}</span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </x-glass.card>

        <!-- Database Schema Migrations Table -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Database Migrations State</span>
                    <span class="text-xs font-mono text-slate-400">({{ $migrationsData['total_count'] ?? 0 }} total files)</span>
                </h3>
            </div>

            <x-glass.card variant="standard" class="p-0 overflow-hidden">
                <div class="overflow-x-auto max-h-80 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/90 sticky top-0 z-10 text-slate-400 border-b border-white/5 uppercase text-[10px]">
                            <tr>
                                <th class="p-4">Migration File</th>
                                <th class="p-4">Batch</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Execution Target</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-200 font-mono">
                            @forelse(($migrationsData['all'] ?? []) as $mig)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="p-4 text-white font-medium">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $mig['applied'] ? '📄' : '⏳' }}</span>
                                            <span class="{{ $mig['applied'] ? 'text-slate-200' : 'text-amber-300 font-bold' }}">{{ $mig['file'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-slate-400">
                                        {{ $mig['batch'] ? 'Batch #' . $mig['batch'] : '—' }}
                                    </td>
                                    <td class="p-4">
                                        @if($mig['applied'])
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                                <span>✔</span> APPLIED
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 animate-pulse">
                                                <span>⏳</span> PENDING
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right text-slate-400 text-[11px]">
                                        {{ $dbDetails['driver'] ?? 'SQLITE' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-500 font-mono text-xs">
                                        No migration files detected in database/migrations directory.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>

        <!-- Database Snapshot History Table -->
        <div class="space-y-4">
            <h3 class="text-base font-bold text-white tracking-tight">Isolated Database Snapshots</h3>

            <x-glass.card variant="standard" class="p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/80 text-slate-400 border-b border-white/5 uppercase text-[10px]">
                            <tr>
                                <th class="p-4">Snapshot ID</th>
                                <th class="p-4">Label</th>
                                <th class="p-4">Tables</th>
                                <th class="p-4">Size</th>
                                <th class="p-4">Timestamp</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-200">
                            @forelse($dbSnapshots as $snap)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="p-4 font-mono font-bold text-indigo-300">{{ $snap['id'] }}</td>
                                    <td class="p-4 font-medium text-white">{{ $snap['label'] ?? 'DB Backup' }}</td>
                                    <td class="p-4 font-mono text-slate-300">{{ $snap['tables_count'] ?? 0 }} tables</td>
                                    <td class="p-4 font-mono text-slate-400">{{ $snap['size_kb'] ?? 0 }} KB</td>
                                    <td class="p-4 text-slate-400 font-mono text-[11px]">{{ $snap['timestamp'] }}</td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                wire:click="restoreDbSnapshot('{{ $snap['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:confirm="Restore database state from this snapshot? Current database tables will be replaced."
                                                class="px-3 py-1.5 rounded-lg bg-rose-600/20 text-rose-300 border border-rose-500/40 hover:bg-rose-600/40 hover:text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                                                title="Restore database from this snapshot"
                                            >
                                                <span wire:loading.remove wire:target="restoreDbSnapshot('{{ $snap['id'] }}')">⚡ Restore</span>
                                                <span wire:loading wire:target="restoreDbSnapshot('{{ $snap['id'] }}')">Restoring...</span>
                                            </button>

                                            <button 
                                                wire:click="deleteDbSnapshot('{{ $snap['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:confirm="Permanently delete database snapshot [{{ $snap['id'] }}]?"
                                                class="p-1.5 rounded-lg bg-slate-900 border border-white/10 text-slate-400 hover:text-rose-400 hover:border-rose-500/40 transition-colors cursor-pointer"
                                                title="Delete DB Snapshot"
                                            >
                                                <span wire:loading.remove wire:target="deleteDbSnapshot('{{ $snap['id'] }}')">🗑️</span>
                                                <span wire:loading wire:target="deleteDbSnapshot('{{ $snap['id'] }}')">⏳</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-500 font-mono text-xs">
                                        No database snapshots created yet. Click "Create DB Snapshot" above to generate an immediate backup.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>
    </div>

    <!-- TAB 3: SYSTEM HEALTH DIAGNOSTICS -->
    <div x-show="activeTab === 'health'" class="space-y-6" style="display: none;">
        <x-glass.card variant="standard" class="p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                    <span>🛡️ Synthetic Diagnostic Health Prober</span>
                </h3>
                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold {{ ($healthReport['passed'] ?? false) ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-red-500/20 text-red-300 border border-red-500/40' }}">
                    {{ ($healthReport['passed'] ?? false) ? 'ALL SYSTEMS OPERATIONAL' : 'DEGRADED STATE' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(($healthReport['checks'] ?? []) as $key => $check)
                    <div class="p-4 rounded-xl bg-slate-900/80 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-sm text-white flex items-center gap-2">
                                <span>{{ $check['status'] === 'pass' ? '✅' : ($check['status'] === 'fail' ? '❌' : '⚠️') }}</span>
                                <span>{{ $check['name'] }}</span>
                            </div>
                            <span class="text-xs font-mono text-slate-400">{{ $check['duration_ms'] }}ms</span>
                        </div>
                        <p class="text-xs text-slate-300">{{ $check['message'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-glass.card>
    </div>
</div>
