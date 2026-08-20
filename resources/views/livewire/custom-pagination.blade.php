@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between text-xs">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-slate-900 border border-white/5 text-slate-500 cursor-not-allowed">Previous</span>
            @else
                <button type="button" wire:click="previousPage" class="px-3 py-1.5 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white">Previous</button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" class="px-3 py-1.5 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white">Next</button>
            @else
                <span class="px-3 py-1.5 rounded-lg bg-slate-900 border border-white/5 text-slate-500 cursor-not-allowed">Next</span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-400 font-mono">
                    Showing <span class="font-bold text-white">{{ $paginator->firstItem() }}</span> to <span class="font-bold text-white">{{ $paginator->lastItem() }}</span> of <span class="font-bold text-white">{{ $paginator->total() }}</span> models
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl shadow-sm gap-1">
                    @if ($paginator->onFirstPage())
                        <span class="px-3 py-1 rounded-lg bg-slate-900/60 border border-white/5 text-slate-600 cursor-not-allowed">‹</span>
                    @else
                        <button type="button" wire:click="previousPage" class="px-3 py-1 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white hover:border-indigo-500/50">‹</button>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-3 py-1 text-slate-500">...</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="px-3 py-1 rounded-lg bg-indigo-600 text-white font-bold">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" class="px-3 py-1 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white hover:border-indigo-500/50">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage" class="px-3 py-1 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white hover:border-indigo-500/50">›</button>
                    @else
                        <span class="px-3 py-1 rounded-lg bg-slate-900/60 border border-white/5 text-slate-600 cursor-not-allowed">›</span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
