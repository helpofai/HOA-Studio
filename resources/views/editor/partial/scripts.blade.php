{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Alpine JS Engine Master
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
|
| Architecture:
| Thin Master Script aggregating modular sub-script components:
| 1. scripts-core.blade.php      - Lifecycle, state, model fetching, autosave, draft recovery
| 2. scripts-canvas.blade.php    - Editor mounting, formatting, context menus, outline navigation
| 3. scripts-ai.blade.php        - AI transform, streaming SSE loop, Swarm pipeline, targeted fixes
| 4. scripts-diff.blade.php      - Red/Green diff review, candidate variations, ghost completion
| 5. scripts-telemetry.blade.php - Draggable telemetry modal, drag bubble, system logger
|
|--------------------------------------------------------------------------
*/
--}}

<script>
(function() {
    function registerDocumentEditor() {
        if (!window.Alpine) return;
        Alpine.data('documentEditorComponent', (config) => ({
            {{-- 1. Core State, Autosave, Draft Recovery & Lifecycle --}}
            @include('editor.partial.scripts-core')

            {{-- 2. Canvas Operations, Formats, Context Menu & Clipboard --}}
            @include('editor.partial.scripts-canvas')

            {{-- 3. AI Generation, Streaming SSE Pipeline & Multi-Agent Swarm --}}
            @include('editor.partial.scripts-ai')

            {{-- 4. Diff Review Inspector, Ghost Autocomplete & Variations --}}
            @include('editor.partial.scripts-diff')

            {{-- 5. Terminal Telemetry, Floating Draggable HUD & Logging --}}
            @include('editor.partial.scripts-telemetry')
        }));
    }

    if (window.Alpine) {
        registerDocumentEditor();
    } else {
        document.addEventListener('alpine:init', registerDocumentEditor);
    }
})();
</script>
