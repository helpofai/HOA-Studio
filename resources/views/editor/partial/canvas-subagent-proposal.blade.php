{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas Sub-Agent Proposal Inspector
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

    <div
        x-show="showSubAgentProposal"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="ai-proposal-green-box mx-3 sm:mx-6 my-2 p-4 rounded-2xl bg-slate-950/98 border border-emerald-500/50 shadow-[0_20px_50px_rgba(0,0,0,0.9)] backdrop-blur-2xl text-xs space-y-3 shrink-0"
        style="display: none;"
    >
        <div class="ai-proposal-header flex items-center justify-between pb-2.5 border-b border-emerald-500/30 font-mono">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span class="text-emerald-400 font-bold tracking-wider text-xs" x-text="'✦ SUB-CONTENT-SUB-AGENT (' + (subAgentModeLabel || 'RECREATING').toUpperCase() + ')'">✦ SUB-CONTENT-SUB-AGENT (RECREATING)</span>
                <span class="text-[10px] text-slate-400 font-mono" x-text="streamSpeedTokSec > 0 ? (streamSpeedTokSec + ' tok/s') : ''"></span>
                <span class="text-[10px] text-indigo-300 font-mono px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30" x-text="routedModel"></span>
            </div>

            <div class="ai-proposal-actions flex items-center gap-2">
                <button
                    type="button"
                    x-show="!isTransforming && subAgentProposedText"
                    x-on:click="acceptSubAgentProposal()"
                    class="ai-btn-tick px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/40 transition-all cursor-pointer active:scale-95"
                    title="Accept AI recreation and replace yellow selection in document"
                >
                    ✓ Accept & Replace
                </button>
                <button
                    type="button"
                    x-on:click="discardSubAgentProposal()"
                    class="ai-btn-cross px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white font-bold text-xs border border-rose-500/30 transition-all cursor-pointer active:scale-95"
                    title="Discard AI proposal and restore original text"
                >
                    ✕ Discard
                </button>
            </div>
        </div>

        <!-- Live Real-Time Token Content Body with Syntax and Typography -->
        <div class="ai-proposal-content max-h-72 overflow-y-auto hoa-custom-scrollbar p-3.5 rounded-xl bg-emerald-950/25 border border-emerald-500/25 text-emerald-100 text-sm leading-relaxed font-sans select-text shadow-inner break-words">
            <template x-if="isTransforming && !subAgentProposedText">
                <div class="flex items-center gap-2 text-emerald-400 animate-pulse font-mono text-xs py-2">
                    <span class="animate-spin text-sm">⟳</span> <span>sub-content-sub-agent is writing...</span>
                </div>
            </template>
            <div x-html="subAgentProposedText" class="prose prose-invert max-w-none text-slate-100 whitespace-pre-wrap break-words"></div>
            <span x-show="isTransforming && subAgentProposedText" class="inline-block w-2 h-4 bg-emerald-400 animate-pulse ml-0.5 align-middle"></span>
        </div>
    </div>

