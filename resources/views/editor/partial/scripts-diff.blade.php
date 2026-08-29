{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Diff Review & Autocomplete
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

// Pending AI Diff Review State for In-Context Changes with Multi-Candidate Variations
showDiffReview: false,
activeCandidateIndex: 0,
isRegeneratingCandidate: false,
transformIntensity: 'balanced', // 'conservative' | 'balanced' | 'creative'
transformTone: 'inherit', // 'inherit' | 'professional' | 'casual' | 'persuasive' | 'academic'
transformLength: 'same', // 'shorter' | 'same' | 'longer'
showControlsDrawer: false,
pendingDiff: {
    originalText: '',
    transformedText: '',
    actionType: '',
    customInstruction: '',
    hadSelection: false,
    timestamp: null,
    candidates: []
},

diffViewMode: 'split', // 'split' | 'unified'

computeWordDiff(oldStr = '', newStr = '') {
    const cleanOld = (oldStr || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    const cleanNew = (newStr || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    
    if (!cleanOld && !cleanNew) return { oldHtml: '', newHtml: '', unifiedHtml: '' };
    if (!cleanOld) return { oldHtml: '', newHtml: `<ins class="bg-emerald-500/30 text-emerald-300 font-semibold px-1 py-0.5 rounded border border-emerald-500/40">${cleanNew}</ins>`, unifiedHtml: `<ins class="bg-emerald-500/30 text-emerald-300 font-semibold px-1 py-0.5 rounded border border-emerald-500/40">${cleanNew}</ins>` };
    if (!cleanNew) return { oldHtml: `<del class="bg-rose-500/30 text-rose-300 line-through px-1 py-0.5 rounded border border-rose-500/40">${cleanOld}</del>`, newHtml: '', unifiedHtml: `<del class="bg-rose-500/30 text-rose-300 line-through px-1 py-0.5 rounded border border-rose-500/40">${cleanOld}</del>` };

    const oldWords = cleanOld.split(/\s+/);
    const newWords = cleanNew.split(/\s+/);
    const m = oldWords.length;
    const n = newWords.length;

    // Longest Common Subsequence DP Table
    const dp = Array.from({ length: m + 1 }, () => new Uint16Array(n + 1));
    for (let i = 0; i < m; i++) {
        for (let j = 0; j < n; j++) {
            if (oldWords[i].toLowerCase() === newWords[j].toLowerCase()) {
                dp[i + 1][j + 1] = dp[i][j] + 1;
            } else {
                dp[i + 1][j + 1] = Math.max(dp[i + 1][j], dp[i][j + 1]);
            }
        }
    }

    // Backtrack to find exact insertions and deletions
    let i = m, j = n;
    const ops = [];
    while (i > 0 || j > 0) {
        if (i > 0 && j > 0 && oldWords[i - 1].toLowerCase() === newWords[j - 1].toLowerCase()) {
            ops.unshift({ type: 'equal', text: newWords[j - 1] });
            i--;
            j--;
        } else if (j > 0 && (i === 0 || dp[i][j - 1] >= dp[i - 1][j])) {
            ops.unshift({ type: 'insert', text: newWords[j - 1] });
            j--;
        } else if (i > 0 && (j === 0 || dp[i][j - 1] < dp[i - 1][j])) {
            ops.unshift({ type: 'delete', text: oldWords[i - 1] });
            i--;
        }
    }

    let oldHtml = '';
    let newHtml = '';
    let unifiedHtml = '';

    ops.forEach(op => {
        const escaped = op.text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        if (op.type === 'equal') {
            oldHtml += escaped + ' ';
            newHtml += escaped + ' ';
            unifiedHtml += `<span class="text-slate-300">${escaped}</span> `;
        } else if (op.type === 'delete') {
            oldHtml += `<del class="bg-rose-500/30 text-rose-300 line-through px-1 py-0.5 rounded border border-rose-500/40">${escaped}</del> `;
            unifiedHtml += `<del class="bg-rose-500/30 text-rose-300 line-through px-1 py-0.5 rounded border border-rose-500/40">${escaped}</del> `;
        } else if (op.type === 'insert') {
            newHtml += `<ins class="bg-emerald-500/30 text-emerald-300 font-semibold px-1 py-0.5 rounded border border-emerald-500/40">${escaped}</ins> `;
            unifiedHtml += `<ins class="bg-emerald-500/30 text-emerald-300 font-semibold px-1 py-0.5 rounded border border-emerald-500/40">${escaped}</ins> `;
        }
    });

    return { oldHtml: oldHtml.trim(), newHtml: newHtml.trim(), unifiedHtml: unifiedHtml.trim() };
},

getGranularDiff() {
    const oldT = this.pendingDiff.originalText || '';
    const newT = this.getCurrentTransformedText() || '';
    return this.computeWordDiff(oldT, newT);
},

// Live Before-vs-After SEO, Readability & Metrics Delta Computation
computeMetricsDelta() {
    const oldStr = (this.pendingDiff.originalText || '').replace(/<[^>]*>/g, ' ').trim();
    const newStr = (this.getCurrentTransformedText() || '').replace(/<[^>]*>/g, ' ').trim();
    const targetKw = (this.targetKeyword || '').trim().toLowerCase();

    // Word Counts
    const oldWords = oldStr ? oldStr.split(/\s+/).filter(Boolean).length : 0;
    const newWords = newStr ? newStr.split(/\s+/).filter(Boolean).length : 0;
    const wordDelta = newWords - oldWords;

    // Simple Flesch-Kincaid & Scannability Heuristic
    const getReadabilityScore = (text) => {
        if (!text || text.length === 0) return { score: 70, label: 'Standard' };
        const words = text.split(/\s+/).filter(Boolean);
        const sentences = text.split(/[.!?]+/).filter(Boolean);
        const wordCount = words.length || 1;
        const sentenceCount = sentences.length || 1;
        const syllables = words.reduce((acc, w) => acc + Math.max(1, Math.floor(w.length / 3)), 0);
        
        const flesch = 206.835 - (1.015 * (wordCount / sentenceCount)) - (84.6 * (syllables / wordCount));
        const score = Math.max(0, Math.min(100, Math.round(flesch)));
        let label = 'Standard';
        if (score >= 80) label = 'Very Easy';
        else if (score >= 65) label = 'Good (8th Grade)';
        else if (score >= 50) label = 'Moderate';
        else label = 'Complex';
        return { score, label };
    };

    const oldReadability = getReadabilityScore(oldStr);
    const newReadability = getReadabilityScore(newStr);
    const readabilityDelta = newReadability.score - oldReadability.score;

    // Target Keyword Match Count
    let oldKwCount = 0;
    let newKwCount = 0;
    if (targetKw) {
        const kwRegex = new RegExp('\\b' + targetKw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'gi');
        oldKwCount = (oldStr.match(kwRegex) || []).length;
        newKwCount = (newStr.match(kwRegex) || []).length;
    }
    const kwDelta = newKwCount - oldKwCount;

    // Action Verbs & Power Words Count
    const powerWordsRegex = /\b(proven|essential|master|ultimate|definitive|accelerate|boost|streamline|eliminate|unleash|empower|architect|guarantee|critical|breakthrough)\b/gi;
    const oldPowerCount = (oldStr.match(powerWordsRegex) || []).length;
    const newPowerCount = (newStr.match(powerWordsRegex) || []).length;
    const powerDelta = newPowerCount - oldPowerCount;

    return {
        oldWords,
        newWords,
        wordDelta,
        oldReadability,
        newReadability,
        readabilityDelta,
        oldKwCount,
        newKwCount,
        kwDelta,
        targetKeyword: this.targetKeyword || 'None',
        oldPowerCount,
        newPowerCount,
        powerDelta
    };
},

getCurrentTransformedText() {
    if (this.pendingDiff.candidates && this.pendingDiff.candidates.length > 0) {
        return this.pendingDiff.candidates[this.activeCandidateIndex] || this.pendingDiff.transformedText;
    }
    return this.pendingDiff.transformedText;
},

selectCandidate(idx) {
    if (this.pendingDiff.candidates && this.pendingDiff.candidates[idx]) {
        this.activeCandidateIndex = idx;
        this.pendingDiff.transformedText = this.pendingDiff.candidates[idx];
        this.addLog('AI', 'Selected AI variation #' + (idx + 1));
    }
},

async regenerateVariation(stylePreset = null) {
    const ed = this.getEditor();
    if (!this.pendingDiff.originalText || this.isRegeneratingCandidate) return;

    this.isRegeneratingCandidate = true;

    let intensity = this.transformIntensity;
    let tone = this.transformTone;
    let lengthMod = this.transformLength;

    if (stylePreset === 'persuasive') { tone = 'persuasive'; }
    else if (stylePreset === 'concise') { lengthMod = 'shorter'; intensity = 'conservative'; }
    else if (stylePreset === 'technical') { tone = 'academic'; intensity = 'conservative'; lengthMod = 'longer'; }

    this.addLog('AI', `Generating candidate variation [Intensity: ${intensity}, Tone: ${tone}, Length: ${lengthMod}]...`);

    let customPrompt = this.pendingDiff.customInstruction || this.pendingDiff.actionType;

    // Apply Tone directive
    if (tone === 'professional') customPrompt += ' - Tone: Executive, formal, and corporate-ready.';
    else if (tone === 'casual') customPrompt += ' - Tone: Warm, conversational, human, and relatable.';
    else if (tone === 'persuasive') customPrompt += ' - Tone: High-impact copywriting, strong active verbs, and compelling hooks.';
    else if (tone === 'academic') customPrompt += ' - Tone: Scholarly, analytical, authoritative, with nuanced domain precision.';

    // Apply Length directive
    if (lengthMod === 'shorter') customPrompt += ' - Length: 30% more compact and concise. Strip all fluff.';
    else if (lengthMod === 'longer') customPrompt += ' - Length: 40% more elaborate, adding concrete examples and supporting rationale.';

    // Compute Temperature based on Intensity
    let temp = 0.7;
    if (intensity === 'conservative') temp = 0.3;
    else if (intensity === 'creative') temp = 1.0;

    const fullDocumentContent = ed ? (ed.getText ? ed.getText() : '') : '';
    const fullDocumentHtml = ed ? (ed.getHTML ? ed.getHTML() : '') : '';

    try {
        const resp = await fetch(config.transformRoute, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                text: this.pendingDiff.originalText,
                type: this.pendingDiff.actionType || 'rewrite',
                custom_instruction: customPrompt,
                model: this.aiModel,
                temperature: temp,
                context: {
                    ...this.aiContext,
                    has_selection: true,
                    selected_text: this.pendingDiff.originalText,
                    full_document_text: fullDocumentContent,
                    full_document_html: fullDocumentHtml,
                    target_keyword: this.targetKeyword || '',
                    document_title: this.title || ''
                }
            })
        });
        const data = await resp.json();
        if (data.success && data.result) {
            const newVar = data.result.trim();
            if (!this.pendingDiff.candidates) this.pendingDiff.candidates = [];
            this.pendingDiff.candidates.push(newVar);
            this.activeCandidateIndex = this.pendingDiff.candidates.length - 1;
            this.pendingDiff.transformedText = newVar;
            this.addLog('AI', '✦ Added Variation #' + this.pendingDiff.candidates.length + ` (${tone}/${intensity})`);
        }
    } catch (err) {
        this.addLog('ERROR', 'Failed to generate variation: ' + err.message);
    } finally {
        this.isRegeneratingCandidate = false;
    }
},


    keepBothDiff() {
        const ed = this.getEditor();
        const newText = this.getCurrentTransformedText();
        if (!ed || !newText) return;
        const combined = (this.pendingDiff.originalText ? `<p>${this.pendingDiff.originalText}</p>` : '') + `<p></p>${newText}`;
        this.insertContentIntoCanvas(combined, true);
        const finalHtmlVal = ed.getHTML ? ed.getHTML() : '';
        Livewire.dispatch('autosave', { html: finalHtmlVal, json: null });
        this.saveLocalDraft(finalHtmlVal);
        this.updateOutline();
        this.updateActiveFormats();
        this.addLog('AI', '✓ Kept both original and AI variation #' + (this.activeCandidateIndex + 1));
        this.dismissDiffReview();
    },

dismissDiffReview() {
    this.showDiffReview = false;
    this.activeCandidateIndex = 0;
    this.isRegeneratingCandidate = false;
    this.pendingDiff = { originalText: '', transformedText: '', actionType: '', customInstruction: '', hadSelection: false, timestamp: null, candidates: [] };
}
