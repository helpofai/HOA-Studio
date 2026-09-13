<!-- Public & Protected Sharing Modal -->
<div x-show="showShareModalLocal || $wire.showShareModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-3xl glass-elevated border border-white/15 p-6 sm:p-8 space-y-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-lg text-indigo-300">🔗</div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Share & Publish Document</h3>
                    <p class="text-xs text-slate-400">Create an encrypted public view link with custom access controls.</p>
                </div>
            </div>
            <button type="button" @click="closeShareModalInstant()" class="text-slate-400 hover:text-white p-2 cursor-pointer">✕</button>
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
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); alert('Link copied to clipboard!');" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer">Copy</button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
            <button type="button" @click="closeShareModalInstant()" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold cursor-pointer">Close</button>
            <button
                type="button"
                wire:click="createOrUpdateShare"
                wire:loading.attr="disabled"
                wire:target="createOrUpdateShare"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 cursor-pointer disabled:opacity-50 flex items-center gap-2"
            >
                {{ $isShareActive ? 'Update Share Settings' : 'Generate Public Link' }}
            </button>
        </div>
    </div>
</div>
