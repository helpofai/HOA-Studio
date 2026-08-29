{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Slash/Suggestion Menu Component
|--------------------------------------------------------------------------
*/
--}}

<div x-show="showSlashMenu" 
     x-cloak
     x-ref="slashMenu"
     class="absolute z-50 w-64 p-2 bg-slate-900/95 border border-white/10 rounded-2xl shadow-2xl backdrop-blur-2xl"
     :style="{ left: slashMenuX + 'px', top: slashMenuY + 'px' }"
     @click.away="showSlashMenu = false">
    
    <div class="px-2 py-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">AI Commands</div>
    
    <button @click="triggerAiAction('rewrite')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300 text-xs flex items-center gap-2">
        <span>✨</span> Rewrite & Polish
    </button>
    <button @click="triggerAiAction('summarize')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300 text-xs flex items-center gap-2">
        <span>📝</span> Summarize
    </button>
    <button @click="triggerAiAction('expand')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300 text-xs flex items-center gap-2">
        <span>🚀</span> Expand
    </button>
    
    <div class="my-2 border-t border-white/5"></div>
    
    <div class="px-2 py-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Formatting</div>
    
    <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run(); showSlashMenu = false" class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300 text-xs">
        Heading 1
    </button>
</div>
