{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Bubble Menu Component
|--------------------------------------------------------------------------
*/
--}}

<div x-show="showBubbleMenu" 
     x-cloak
     class="absolute z-50 flex items-center gap-1 p-1 bg-slate-900 border border-white/10 rounded-xl shadow-2xl backdrop-blur-md"
     x-ref="bubbleMenu">
    
    <button @click="editor.chain().focus().toggleBold().run()" 
            class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 font-bold text-xs"
            :class="{ 'bg-slate-800 text-indigo-400': editor.isActive('bold') }">
        B
    </button>

    <button @click="editor.chain().focus().toggleItalic().run()" 
            class="p-2 rounded-lg hover:bg-slate-800 text-slate-300 italic font-serif text-xs"
            :class="{ 'bg-slate-800 text-indigo-400': editor.isActive('italic') }">
        I
    </button>

    <div class="w-px h-6 bg-white/10 mx-1"></div>

    <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" 
            class="p-2 rounded-lg hover:bg-slate-800 text-slate-300">
        H1
    </button>
</div>
