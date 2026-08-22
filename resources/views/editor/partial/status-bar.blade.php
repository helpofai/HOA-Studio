{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Sticky Bottom Status Bar Partial
|--------------------------------------------------------------------------
*/
--}}

<div class="sticky bottom-0 z-20 mt-4 p-3 rounded-2xl glass-elevated border border-white/10 shadow-2xl flex flex-wrap items-center justify-between gap-3 text-xs font-mono text-slate-400">
    <div class="flex flex-wrap items-center gap-4">
        <span>Words: <strong class="text-white" x-text="wordCount">0</strong></span>
        <span>&bull;</span>
        <span>Chars: <strong class="text-white" x-text="characterCount">0</strong></span>
        <span>&bull;</span>
        <span>Reading: <strong class="text-indigo-300" x-text="readingTime + ' min'">1 min</strong></span>
        <span>&bull;</span>
        <span>SEO: <strong class="{{ ($seoData['score'] ?? 0) >= 80 ? 'text-emerald-400' : 'text-yellow-400' }}">{{ $seoData['score'] ?? 0 }}/100</strong></span>
        <span>&bull;</span>
        <span>Readability: <strong class="text-cyan-400">{{ ($seoData['readability_score'] ?? 0) >= 60 ? 'Good' : 'Standard' }}</strong></span>
        <span class="hidden md:inline">&bull;</span>
        <span class="hidden md:inline text-slate-300">Goal: <strong class="text-emerald-400" x-text="wordCount + '/' + targetWordGoal"></strong> (<span x-text="Math.min(100, Math.round((wordCount/targetWordGoal)*100)) + '%'"></span>)</span>
    </div>

    <div class="flex items-center gap-4">
        <span class="text-indigo-400">Model: <strong class="text-white" x-text="aiModel">Auto</strong></span>
        <span>&bull;</span>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full {{ $isSaving ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' }}"></span>
            <span class="text-slate-300">{{ $saveStatusText }}</span>
        </div>
    </div>
</div>
