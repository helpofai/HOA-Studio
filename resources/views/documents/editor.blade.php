{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Enterprise Document Editor Suite
|--------------------------------------------------------------------------
|
| Features:
| 1. High-Performance Fully Responsive Grid (Mobile, Tablet, Laptop, Ultra-wide)
| 2. Mobile Drawer Overlays for Left (AI Router) and Right (Content Intelligence)
| 3. Ambient Error & Streaming Notifications
| 4. Sticky Status Bar with Live Stats
|
*/
--}}

<div 
    class="space-y-4 min-h-screen flex flex-col justify-between relative"
    x-data="documentEditorComponent({
        documentId: {{ $documentId }},
        editorType: '{{ $editorType }}',
        streamRoute: '{{ route('ai.stream-transform') }}',
        transformRoute: '{{ route('ai.transform') }}',
        csrfToken: '{{ csrf_token() }}',
        initialContent: @js($contentHtml),
        providers: @js($availableProviders),
        initialModels: @js($availableAiModels->map(fn($m) => ['id' => $m->model_id, 'name' => $m->name, 'provider_slug' => $m->provider?->slug ?? 'omniroute'])),
        modelsUrl: '{{ route('ai.providers.models') }}'
    })"
    x-init="init()"
>
    <!-- Ambient Error Notification Toast -->
    <div 
        x-show="aiErrorMessage" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="fixed top-5 right-5 max-w-md p-4 editor-floating-panel !shadow-red-500/10"
        style="display: none; z-index: var(--z-index-overlay);"
    >
        <div class="flex items-start gap-2.5">
            <span class="text-base text-red-400">⚠️</span>
            <div>
                <div class="font-bold text-red-200 text-xs">AI Generation Notice</div>
                <div class="text-[11px] text-slate-300 mt-0.5 leading-relaxed" x-text="aiErrorMessage"></div>
            </div>
        </div>
        <button type="button" x-on:click="aiErrorMessage = ''" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕</button>
    </div>

    <!-- TOP TOOLBAR & CONTROLS -->
    @include('editor.partial.toolbar')

    <!-- RESPONSIVE WORKSPACE LAYOUT (3-Column Grid) -->
    <div class="editor-grid layout-three-panel" 
         :class="{
              'layout-three-panel': showLeftPanel && showRightPanel,
              'layout-left-only': showLeftPanel && !showRightPanel,
              'layout-right-only': !showLeftPanel && showRightPanel,
              'layout-center-only': !showLeftPanel && !showRightPanel
         }"
    >
        <!-- COLUMN 1: AI COMMAND CENTER (Desktop inline / Mobile Drawer) -->
        <div 
            x-show="showLeftPanel"
            x-transition
            class="order-2 lg:order-1 h-full flex flex-col"
        >
            @include('editor.partial.ai-command-center')
        </div>

        <!-- COLUMN 2: MAIN WRITING WORKSPACE (Central Focus) -->
        <div class="space-y-4 order-1 lg:order-2 w-full min-w-0 h-full flex flex-col">
            @include('editor.partial.canvas')
        </div>

        <!-- COLUMN 3: CONTENT INTELLIGENCE & SEO AUDIT (Desktop inline / Mobile Drawer) -->
        <div 
            x-show="showRightPanel"
            x-transition
            class="order-3 h-full flex flex-col"
        >
            @include('editor.partial.content-intelligence')
        </div>
    </div>

    <!-- STICKY BOTTOM STATUS BAR -->
    @include('editor.partial.status-bar')

    <!-- MODALS -->
    @include('editor.partial.modals')

    <!-- CLIENT SCRIPT LOGIC -->
    @include('editor.partial.scripts')
</div>