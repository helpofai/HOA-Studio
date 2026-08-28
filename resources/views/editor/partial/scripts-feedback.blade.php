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
        
        // Wrap accepted content in highlight
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
        
        // Mark as declined (red)
        this.insertContentIntoCanvas(this.getCurrentTransformedText(), true, 'ai-decline-feedback');
        
        this.addLog('WARN', '✕ Discarded AI proposed changes.');
        this.dismissDiffReview();
    },

    clearAiFeedback() {
        const ed = this.getEditor();
        if (!ed) return;
        
        const marks = ed.view.dom.querySelectorAll('.ai-accept-feedback, .ai-decline-feedback, .hoa-feedback-node');
        marks.forEach(m => m.remove());
        
        this.addLog('AI', '✦ Feedback colors cleared.');
    },

    getFeedbackNodeHtml(status) {
        return `<div class="hoa-feedback-node p-4 rounded-2xl bg-slate-950/98 border border-indigo-500/40 shadow-2xl backdrop-blur-2xl space-y-3 text-xs my-4">
    <div class="font-bold text-indigo-300 text-xs uppercase tracking-wider">Editor Feedback</div>
    <div class="text-slate-300 leading-relaxed">${status}</div>
</div>`;
    }
