<!-- Lossy Engine Switch Warning Modal -->
<div x-show="showLossyWarning" x-cloak class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div x-show="showLossyWarning" class="w-full max-w-md mx-4 rounded-2xl glass-elevated border border-amber-500/30 shadow-2xl p-6 space-y-5">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-xl">⚠️</div>
            <div>
                <h2 class="text-base font-bold text-white">Lossy Engine Switch</h2>
                <p class="text-xs text-slate-400 mt-0.5">Switching may permanently strip rich-text formatting.</p>
            </div>
        </div>
        <div class="rounded-xl bg-amber-900/10 border border-amber-500/20 p-4 text-xs text-slate-300 space-y-2">
            <div class="flex items-center gap-2"><span class="text-amber-400">✦</span><span><strong class="text-white">Headings, bold, italic, lists</strong> become plain text.</span></div>
            <div class="flex items-center gap-2"><span class="text-amber-400">✦</span><span>Images and tables <strong class="text-white">will be removed</strong>.</span></div>
            <div class="flex items-center gap-2"><span class="text-amber-400">✦</span><span>Action <strong class="text-white">cannot be undone</strong> without a snapshot.</span></div>
        </div>
        <p class="text-xs text-slate-500">Tip: use <strong class="text-slate-300">Save Snapshot</strong> first to preserve formatting.</p>
        <div class="flex items-center justify-end gap-3 pt-1">
            <button type="button" x-on:click="cancelLossySwitch()" class="px-4 py-2 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-xs font-semibold transition-all">Cancel</button>
            <button type="button" x-on:click="confirmLossySwitch()" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-lg transition-all">Continue Anyway</button>
        </div>
    </div>
</div>
