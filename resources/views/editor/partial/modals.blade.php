{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Modals Partial
|--------------------------------------------------------------------------
*/
--}}

<!-- Public & Protected Sharing Modal -->
@if($showShareModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-3xl glass-elevated border border-white/15 p-6 sm:p-8 space-y-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between pb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-lg text-indigo-300">🔗</div>
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight">Share & Publish Document</h3>
                        <p class="text-xs text-slate-400">Create an encrypted public view link with custom access controls.</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-white p-2">✕</button>
            </div>

            @if(session('share_status'))
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 font-medium">
                    {{ session('share_status') }}
                </div>
            @endif

            @if($isShareActive)
                <div class="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-500/30 space-y-3">
                    <label class="text-xs font-bold text-indigo-300 block">Active Public Share Link</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $shareUrl }}" class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white font-mono select-all focus:outline-none" />
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); alert('Link copied to clipboard!');" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs">Copy</button>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold">Close</button>
                <button type="button" wire:click="createOrUpdateShare" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30">
                    {{ $isShareActive ? 'Update Share Settings' : 'Generate Public Link' }}
                </button>
            </div>
        </div>
    </div>
@endif

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
