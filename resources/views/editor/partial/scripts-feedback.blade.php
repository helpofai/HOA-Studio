{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Feedback Management
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
|--------------------------------------------------------------------------
*/
--}}

    acceptAiDiff() {
        const ed = this.getEditor();
        const newText = this.getCurrentTransformedText();
        if (!ed || !newText) return;
        
        this.insertContentIntoCanvas(newText, true, 'ai-accept-feedback');
        
        const finalHtmlVal = ed.getHTML ? ed.getHTML() : '';
        Livewire.dispatch('autosave', { html: finalHtmlVal, json: null });
        this.saveLocalDraft(finalHtmlVal);
        this.updateOutline();
        this.updateActiveFormats();
        this.addLog('AI', '✓ Accepted AI variation #' + (this.activeCandidateIndex + 1));
        this.dismissDiffReview();
    },

    rejectAiDiff() {
        const ed = this.getEditor();
        if (!ed) return;
        
        this.insertContentIntoCanvas(this.getCurrentTransformedText(), true, 'ai-decline-feedback');
        
        this.addLog('WARN', '✕ Discarded AI proposed changes.');
        this.dismissDiffReview();
    },

    clearAiFeedback() {
        const ed = this.getEditor();
        if (!ed) return;
        
        const elements = ed.view.dom.querySelectorAll('.ai-marked-yellow, .ai-proposal-green-box, .ai-accept-feedback, .ai-decline-feedback, .hoa-feedback-node');
        elements.forEach(el => {
            if (el.classList.contains('ai-proposal-green-box') || el.classList.contains('hoa-feedback-node')) {
                el.remove();
            } else {
                const parent = el.parentNode;
                while (el.firstChild) parent.insertBefore(el.firstChild, el);
                parent.removeChild(el);
            }
        });
        
        this.addLog('AI', '✦ All proposal highlights cleared.');
    },

    getFeedbackNodeHtml(status) {
        return `<div class="hoa-feedback-node p-4 rounded-2xl bg-slate-950/98 border border-indigo-500/40 shadow-2xl backdrop-blur-2xl space-y-3 text-xs my-4">
    <div class="font-bold text-indigo-300 text-xs uppercase tracking-wider">Editor Feedback</div>
    <div class="text-slate-300 leading-relaxed">${status}</div>
</div>`;
    }
