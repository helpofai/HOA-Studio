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
        Alpine.data('documentEditorComponent', (config) => {
            return Object.assign(
                { @include('editor.partial.scripts-core') },
                { @include('editor.partial.scripts-canvas') },
                { @include('editor.partial.scripts-ai') },
                { @include('editor.partial.scripts-diff') },
                { @include('editor.partial.scripts-feedback') },
                { @include('editor.partial.scripts-telemetry') }
            );
        });
}

    if (window.Alpine) {
        registerDocumentEditor();
    } else {
        document.addEventListener('alpine:init', registerDocumentEditor);
    }

    // Register Global Proposal Handlers for Tick / Cross inline actions
    window.acceptAiProposal = function(proposalId) {
        const ed = window.hoaEditorInstance ? (Alpine.raw ? Alpine.raw(window.hoaEditorInstance) : window.hoaEditorInstance) : null;
        const data = window._activeAiProposals ? window._activeAiProposals[proposalId] : null;
        const container = document.getElementById('tiptap-content-target');
        const boxEl = document.getElementById(proposalId) || (container ? container.querySelector('#' + proposalId) : null) || document.querySelector('.ai-proposal-green-box');
        
        if (data && data.proposalText) {
            const newText = data.proposalText;
            const originalText = data.originalText || '';
            const greenWrappedHtml = `<mark class="ai-replaced-green-highlight">${newText}</mark>`;

            if (ed && typeof ed.getHTML === 'function') {
                let currentHtml = ed.getHTML();
                let replaced = false;

                if (currentHtml.includes('ai-marked-yellow')) {
                    const markRegex = /<mark[^>]*class=["'][^"']*ai-marked-yellow[^"']*["'][^>]*>([\s\S]*?)<\/mark>/gi;
                    if (markRegex.test(currentHtml)) {
                        currentHtml = currentHtml.replace(markRegex, greenWrappedHtml);
                        replaced = true;
                    }
                }

                if (!replaced && originalText && currentHtml.includes(originalText)) {
                    currentHtml = currentHtml.replace(originalText, greenWrappedHtml);
                    replaced = true;
                }

                if (!replaced && originalText && originalText.trim().length > 10) {
                    const escaped = originalText.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/\s+/g, '\\s+');
                    const fuzzyRegex = new RegExp(escaped, 'i');
                    if (fuzzyRegex.test(currentHtml)) {
                        currentHtml = currentHtml.replace(fuzzyRegex, greenWrappedHtml);
                        replaced = true;
                    }
                }

                if (replaced) {
                    ed.setContent(currentHtml, true);
                } else if (typeof ed.replaceSelection === 'function') {
                    ed.replaceSelection(greenWrappedHtml);
                }

                // Auto-fade green highlight after 5s and clean HTML
                setTimeout(() => {
                    if (container) {
                        const greenMarks = container.querySelectorAll('.ai-replaced-green-highlight');
                        greenMarks.forEach(m => m.classList.add('fading'));
                    }
                    setTimeout(() => {
                        if (ed && typeof ed.getHTML === 'function') {
                            let cleanDoc = ed.getHTML();
                            if (cleanDoc.includes('ai-replaced-green-highlight')) {
                                cleanDoc = cleanDoc.replace(/<mark[^>]*class=["'][^"']*ai-replaced-green-highlight[^"']*["'][^>]*>([\s\S]*?)<\/mark>/gi, '$1');
                                ed.setContent(cleanDoc, false);
                                if (window.Livewire) {
                                    Livewire.dispatch('autosave', { html: cleanDoc, json: ed.getJSON ? ed.getJSON() : null });
                                }
                            }
                        }
                    }, 1500);
                }, 5000);
            }

            if (boxEl && boxEl.parentNode) {
                boxEl.remove();
            }
        } else if (boxEl) {
            boxEl.remove();
        }
    };

    window.rejectAiProposal = function(proposalId) {
        const ed = window.hoaEditorInstance ? (Alpine.raw ? Alpine.raw(window.hoaEditorInstance) : window.hoaEditorInstance) : null;
        const container = document.getElementById('tiptap-content-target');
        const boxEl = document.getElementById(proposalId) || (container ? container.querySelector('#' + proposalId) : null) || document.querySelector('.ai-proposal-green-box');
        
        if (boxEl && boxEl.parentNode) {
            boxEl.remove();
        }

        if (ed && typeof ed.getHTML === 'function') {
            let currentHtml = ed.getHTML();
            if (currentHtml.includes('ai-marked-yellow')) {
                currentHtml = currentHtml.replace(/<mark[^>]*class=["'][^"']*ai-marked-yellow[^"']*["'][^>]*>([\s\S]*?)<\/mark>/gi, '$1');
                ed.setContent(currentHtml, false);
            }
        }

        const yellowMarks = container ? container.querySelectorAll('.ai-marked-yellow') : document.querySelectorAll('.ai-marked-yellow');
        yellowMarks.forEach(m => {
            const parent = m.parentNode;
            if (parent) {
                while (m.firstChild) parent.insertBefore(m.firstChild, m);
                parent.removeChild(m);
            }
        });
    };
})();
</script>
