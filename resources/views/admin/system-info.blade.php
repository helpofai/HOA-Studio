{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin System Info & Docs View
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

@vite(['resources/css/markdown.css'])

<div class="space-y-8" x-data="{ activeTab: @entangle('activeTab'), otherDocKey: @entangle('otherDocKey') }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>System Info & Documentation</span>
                <x-glass.badge variant="violet">v{{ $versionMeta['version'] ?? '2.5.0' }}</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Comprehensive server diagnostics, environment requirements, architecture specs, and live project documentation.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 text-xs font-mono text-slate-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ ($diagnostics['all_extensions_met'] ?? false) && ($diagnostics['all_permissions_writable'] ?? false) ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                <span>PHP {{ phpversion() }} ({{ $diagnostics['database']['driver'] ?? 'SQL' }})</span>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-white/10 pb-3">
        <button 
            type="button"
            @click="activeTab = 'server'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'server' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>🖥️</span>
            <span>Server & Requirements</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'readme'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'readme' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>📖</span>
            <span>README.md</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'changelog'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'changelog' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>📜</span>
            <span>CHANGELOG.md</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'documents'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'documents' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>📑</span>
            <span>DOCUMENTS.md</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'others'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
            :class="activeTab === 'others' ? 'bg-violet-600/25 text-violet-200 border border-violet-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'"
        >
            <span>📚</span>
            <span>Other Docs & Guides</span>
        </button>
    </div>

    <!-- TAB 1: SERVER & REQUIREMENTS -->
    <div x-show="activeTab === 'server'" class="space-y-6">
        <!-- Overview Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-glass.card variant="standard" class="p-5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">PHP Engine</div>
                <div class="text-xl font-black text-white font-mono flex items-center gap-2">
                    <span>PHP {{ $diagnostics['server']['php_version'] ?? phpversion() }}</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1 font-mono">SAPI: {{ $diagnostics['server']['php_sapi'] ?? '' }}</div>
            </x-glass.card>

            <x-glass.card variant="standard" class="p-5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Database System</div>
                <div class="text-xl font-black text-indigo-300 font-mono">
                    {{ $diagnostics['database']['driver'] ?? 'SQL' }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 truncate">{{ $diagnostics['database']['version'] ?? '' }} ({{ $diagnostics['database']['table_count'] ?? 0 }} tables)</div>
            </x-glass.card>

            <x-glass.card variant="standard" class="p-5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Framework Core</div>
                <div class="text-xl font-black text-violet-300 font-mono">
                    Laravel {{ $diagnostics['server']['laravel_version'] ?? '' }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Env: <span class="uppercase text-emerald-400 font-bold font-mono">{{ $diagnostics['server']['environment'] ?? 'production' }}</span></div>
            </x-glass.card>

            <x-glass.card variant="standard" class="p-5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Memory & Timeout</div>
                <div class="text-xl font-black text-emerald-300 font-mono">
                    {{ $diagnostics['php_limits']['memory_limit'] ?? '512M' }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 font-mono">Max Exec: {{ $diagnostics['php_limits']['max_execution_time'] ?? '60s' }}</div>
            </x-glass.card>
        </div>

        <!-- Detailed Environment Specs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Server Specs Table -->
            <x-glass.card variant="standard" class="p-6 space-y-4">
                <h3 class="text-sm font-bold text-white tracking-tight pb-3 border-b border-white/10 flex items-center gap-2">
                    <span>⚙️ Server & Environment Details</span>
                </h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                        <span class="text-slate-400">Operating System</span>
                        <span class="font-mono text-slate-200 text-right truncate max-w-[220px]">{{ $diagnostics['server']['os'] ?? '' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                        <span class="text-slate-400">Web Server Software</span>
                        <span class="font-mono text-slate-200 text-right truncate max-w-[220px]">{{ $diagnostics['server']['server_software'] ?? '' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                        <span class="text-slate-400">App Timezone</span>
                        <span class="font-mono text-slate-200">{{ $diagnostics['server']['timezone'] ?? 'UTC' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                        <span class="text-slate-400">App URL</span>
                        <span class="font-mono text-indigo-300">{{ $diagnostics['server']['url'] ?? 'http://127.0.0.1:8000' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                        <span class="text-slate-400">Max Upload File Size</span>
                        <span class="font-mono text-slate-200">{{ $diagnostics['php_limits']['upload_max_filesize'] ?? '' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                        <span class="text-slate-400">Max POST Payload Size</span>
                        <span class="font-mono text-slate-200">{{ $diagnostics['php_limits']['post_max_size'] ?? '' }}</span>
                    </div>
                </div>
            </x-glass.card>

            <!-- Storage & Directory Permissions -->
            <x-glass.card variant="standard" class="p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                        <span>📁 Storage Directory Permissions</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ ($diagnostics['all_permissions_writable'] ?? false) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                        {{ ($diagnostics['all_permissions_writable'] ?? false) ? 'ALL WRITABLE' : 'ATTENTION REQUIRED' }}
                    </span>
                </div>
                <div class="space-y-2 text-xs font-mono">
                    @foreach(($diagnostics['permissions'] ?? []) as $label => $perm)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/70 border border-white/5">
                            <span class="text-slate-300">{{ $label }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-500">({{ $perm['perms'] }})</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $perm['writable'] ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    {{ $perm['writable'] ? 'WRITABLE' : 'READ-ONLY' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-glass.card>
        </div>

        <!-- Required PHP Extensions Grid -->
        <x-glass.card variant="standard" class="p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                    <span>🧩 Required PHP Extensions Matrix</span>
                </h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ ($diagnostics['all_extensions_met'] ?? false) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                    {{ ($diagnostics['all_extensions_met'] ?? false) ? '100% COMPATIBLE' : 'MISSING EXTENSIONS' }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach(($diagnostics['extensions'] ?? []) as $ext => $info)
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <div class="font-mono font-bold text-xs {{ $info['loaded'] ? 'text-white' : 'text-rose-400' }}">{{ $info['name'] }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[130px]">{{ $info['description'] }}</div>
                        </div>
                        <span class="text-sm shrink-0">{{ $info['loaded'] ? '✅' : '❌' }}</span>
                    </div>
                @endforeach
            </div>
        </x-glass.card>
    </div>

    <!-- TAB 2: README.md -->
    <div x-show="activeTab === 'readme'" class="space-y-6" style="display: none;">
        <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📖</span>
                    <h2 class="text-lg font-bold text-white">README.md — Project Documentation</h2>
                </div>
                <span class="text-xs font-mono text-slate-400">{{ $docs['readme']['raw_size_kb'] ?? 0 }} KB</span>
            </div>
            <div class="markdown-body max-w-none overflow-x-auto">
                {!! $docs['readme']['content_html'] ?? '<p class="text-slate-500">README.md not found.</p>' !!}
            </div>
        </x-glass.card>
    </div>

    <!-- TAB 3: CHANGELOG.md -->
    <div x-show="activeTab === 'changelog'" class="space-y-6" style="display: none;">
        <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📜</span>
                    <h2 class="text-lg font-bold text-white">CHANGELOG.md — Release Timeline</h2>
                </div>
                <span class="text-xs font-mono text-slate-400">{{ $docs['changelog']['raw_size_kb'] ?? 0 }} KB</span>
            </div>
            <div class="markdown-body max-w-none overflow-x-auto">
                {!! $docs['changelog']['content_html'] ?? '<p class="text-slate-500">CHANGELOG.md not found.</p>' !!}
            </div>
        </x-glass.card>
    </div>

    <!-- TAB 4: DOCUMENTS.md -->
    <div x-show="activeTab === 'documents'" class="space-y-6" style="display: none;">
        <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📑</span>
                    <h2 class="text-lg font-bold text-white">DOCUMENTS.md — Developer Guidelines</h2>
                </div>
                <span class="text-xs font-mono text-slate-400">{{ $docs['documents']['raw_size_kb'] ?? 0 }} KB</span>
            </div>
            <div class="markdown-body max-w-none overflow-x-auto">
                {!! $docs['documents']['content_html'] ?? '<p class="text-slate-500">DOCUMENTS.md not found.</p>' !!}
            </div>
        </x-glass.card>
    </div>

    <!-- TAB 5: OTHER DOCS & GUIDES -->
    <div x-show="activeTab === 'others'" class="space-y-6" style="display: none;">
        <div class="flex items-center gap-2">
            <button 
                type="button"
                @click="otherDocKey = 'production'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="otherDocKey === 'production' ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-white/10 text-slate-400 hover:text-white'"
            >
                PRODUCTION-GUIDE.md
            </button>
            <button 
                type="button"
                @click="otherDocKey = 'multieditor'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="otherDocKey === 'multieditor' ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-white/10 text-slate-400 hover:text-white'"
            >
                ADVANCED MULTI-EDITOR.md
            </button>
            <button 
                type="button"
                @click="otherDocKey = 'license'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="otherDocKey === 'license' ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-white/10 text-slate-400 hover:text-white'"
            >
                LICENSE.md
            </button>
        </div>

        @foreach(['production' => 'PRODUCTION-GUIDE.md', 'multieditor' => 'ADVANCED MULTI-EDITOR.md', 'license' => 'LICENSE.md'] as $subKey => $subFile)
            <div x-show="otherDocKey === '{{ $subKey }}'" style="display: none;">
                <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-4">
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📚</span>
                            <h2 class="text-lg font-bold text-white">{{ $docs[$subKey]['title'] ?? $subFile }}</h2>
                        </div>
                        <span class="text-xs font-mono text-slate-400">{{ $docs[$subKey]['raw_size_kb'] ?? 0 }} KB</span>
                    </div>
                    <div class="markdown-body max-w-none overflow-x-auto">
                        {!! $docs[$subKey]['content_html'] ?? '<p class="text-slate-500">Document content not found.</p>' !!}
                    </div>
                </x-glass.card>
            </div>
        @endforeach
    </div>
</div>
