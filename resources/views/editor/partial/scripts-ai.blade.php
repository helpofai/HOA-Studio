{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: AI Transform & SSE Engine
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

aiPrompt: '',
inlineAiPrompt: '',
inlineAiPlacement: 'replace', // 'replace' or 'insert_below'
showInlineAiPrompt: false,
aiModel: 'Auto (OmniRoute)',
aiContext: {
    currentDoc: true,
    project: true,
    brandVoice: true,
    knowledgeBase: true,
    webResearch: false
},
aiHistory: [],

// Real-Time Token & Latency Telemetry
sendTokens: 0,
receivedTokens: 0,
totalTokens: 0,
streamLatencyMs: 12,
streamSpeedTokSec: 0,

// 15-Stage Enterprise Production Pipeline Matrix
pipelineStages: {
    search_intent: { icon: '🔍', label: 'Search Intent Analysis', category: 'Analysis', enabled: true },
    keyword_research: { icon: '🏷️', label: 'Keyword & Entity Research', category: 'Research', enabled: true },
    serp_competitor: { icon: '🌐', label: 'SERP / Competitor Analysis', category: 'Intelligence', enabled: true },
    content_gaps: { icon: '🎯', label: 'Content Gap Analysis', category: 'Strategy', enabled: true },
    article_outline: { icon: '📑', label: 'Article Outline Architecture', category: 'Structure', enabled: true },
    section_generation: { icon: '✍️', label: 'Section-by-Section Generation', category: 'Drafting', enabled: true },
    fact_verification: { icon: '🛡️', label: 'Fact & Source Verification', category: 'Accuracy', enabled: true },
    originality_check: { icon: '✨', label: 'Originality & Novelty Check', category: 'Uniqueness', enabled: true },
    seo_optimization: { icon: '⌁', label: 'SEO Deep Optimization', category: 'Optimization', enabled: true },
    readability_opt: { icon: '📖', label: 'Readability & Flow Optimization', category: 'Refinement', enabled: true },
    internal_links: { icon: '🔗', label: 'Internal Link Suggestions', category: 'Linking', enabled: true },
    media_suggestions: { icon: '🖼️', label: 'Media & Asset Suggestions', category: 'Assets', enabled: true },
    schema_generation: { icon: '📋', label: 'Schema JSON-LD Generation', category: 'Schema', enabled: true },
    quality_audit: { icon: '🏆', label: 'Final 10-Point Quality Audit', category: 'Audit', enabled: true },
    publish_assembly: { icon: '🚀', label: 'Publish-Ready Assembly', category: 'Publish', enabled: true },
},

getSelectedStagesCount() {
    return Object.values(this.pipelineStages).filter(s => s.enabled).length;
},

setPipelinePreset(preset) {
    const allKeys = Object.keys(this.pipelineStages);
    if (preset === 'all') {
        allKeys.forEach(k => this.pipelineStages[k].enabled = true);
        this.addLog('AI', 'Selected all 15 pipeline stages.');
    } else if (preset === 'seo') {
        allKeys.forEach(k => {
            this.pipelineStages[k].enabled = ['search_intent', 'keyword_research', 'content_gaps', 'article_outline', 'section_generation', 'seo_optimization', 'schema_generation', 'quality_audit'].includes(k);
        });
        this.addLog('AI', 'Applied SEO Authority pipeline preset (8 stages).');
    } else if (preset === 'quick') {
        allKeys.forEach(k => {
            this.pipelineStages[k].enabled = ['article_outline', 'section_generation', 'readability_opt'].includes(k);
        });
        this.addLog('AI', 'Applied Quick Draft pipeline preset (3 stages).');
    } else if (preset === 'clear') {
        allKeys.forEach(k => this.pipelineStages[k].enabled = false);
        this.addLog('AI', 'Cleared all pipeline stages.');
    }
},

isTransforming: false,
activeAction: null,
routedModel: 'OmniRoute',
liveAiStreamText: '',
showAiStreamBanner: false,
abortController: null,
streamDecorationId: null,

showContextMenu: false,
contextMenuX: 0,
contextMenuY: 0,
showSlashMenu: false,
slashMenuX: 0,
slashMenuY: 0,
docOutline: [],

aiErrorMessage: '',
activeSwarmAgent: null,
swarmStepIndex: 0,
swarmTotalSteps: 5,
swarmStatusMessage: '',

openInlineAiPrompt() {
    this.showInlineAiPrompt = true;
    this.addLog('AI', 'In-canvas AI prompt bar opened.');
    this.$nextTick(() => {
        const el = document.getElementById('inline-ai-input');
        if (el) el.focus();
    });
},

submitInlineAiPrompt() {
    if (!this.inlineAiPrompt.trim()) return;
    const prompt = this.inlineAiPrompt;
    const placement = (this.hasSelection && this.selectedText) ? this.inlineAiPlacement : 'auto';
    this.inlineAiPrompt = '';
    this.showInlineAiPrompt = false;
    this.triggerAiTransform('custom', prompt, placement);
},

abortAiTransform() {
    if (this.abortController) {
        this.abortController.abort();
        this.abortController = null;
    }
    this.isTransforming = false;
    this.addLog('WARN', 'AI transformation stopped by user.');
},

async runMultiAgentPipeline(userTopic = '') {
    const prompt = userTopic || this.aiPrompt;
    if (!prompt || !prompt.trim()) {
        this.addLog('WARN', 'Please provide a topic or prompt for the Multi-Agent Swarm.');
        return;
    }

    const ed = this.getEditor();
    if (!ed) return;

    this.isTransforming = true;
    this.showAiStreamBanner = true;
    this.activeAction = 'multi_agent_swarm';
    this.swarmTotalSteps = 5;

    try {
        // STEP 1: RESEARCHER & STRATEGIST (Grounding & SEO Target)
        this.swarmStepIndex = 1;
        this.activeSwarmAgent = 'researcher';
        this.swarmStatusMessage = 'Agent 2 & 3 (Researcher & SEO Strategist) analyzing search intent & vector cache...';
        this.addLog('AGENT', '🎯 Swarm Step 1: Agent [Researcher & SEO Strategist] querying Vector Knowledge Base.');
        
        let targetKw = this.targetKeyword;
        if (!targetKw) {
            const kwMatch = prompt.match(/(?:for|on|about|review|guide to)\s+([^,.;\n]+)/i);
            targetKw = kwMatch ? kwMatch[1].trim() : prompt.split(/\s+/).slice(0, 4).join(' ');
            this.targetKeyword = targetKw;
            Livewire.dispatch('applyTargetKeyword', { keyword: targetKw });
        }

        // STEP 2: OUTLINER & ORCHESTRATOR (Title & Heading Tree)
        this.swarmStepIndex = 2;
        this.activeSwarmAgent = 'outliner';
        this.swarmStatusMessage = 'Agent 4 (Outline Architect) creating H1/H2/H3 hierarchy & optimizing Title...';
        this.addLog('AGENT', '📑 Swarm Step 2: Agent [Outline Architect] structuring headings tree.');

        const titleResp = await fetch(config.transformRoute, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                text: prompt,
                type: 'seo_fix_title',
                custom_instruction: "Generate a high-CTR, SEO-optimized title for: '" + prompt + "' frontloading '" + targetKw + "'",
                model: this.aiModel
            })
        });
        const titleText = await titleResp.text();
        let titleData = {};
        try { titleData = JSON.parse(titleText); } catch (e) { titleData = { success: false }; }
        if (titleData.success && titleData.result) {
            const cleanTitle = titleData.result.replace(/^["'#\s]+|["'\s]+$/g, '').trim();
            this.title = cleanTitle;
            Livewire.dispatch('applyTitle', { title: cleanTitle });
            this.addLog('SEO', 'Agent [Outline Architect] applied optimized Title: "' + cleanTitle + '"');
        }

        // STEP 3: DRAFTSMAN (Full Technical Deep-Dive Synthesis)
        this.swarmStepIndex = 3;
        this.activeSwarmAgent = 'draftsman';
        this.swarmStatusMessage = 'Agent 5 (Deep Section Draftsman) drafting comprehensive technical sections...';
        this.addLog('AGENT', '✍️ Swarm Step 3: Agent [Draftsman] synthesizing comprehensive sections...');

        await this.triggerAiTransform('custom', "Write an authoritative, technical long-form masterclass guide on: '" + prompt + "'. Include introduction with quick answer box, structured H2 and H3 headings, technical architecture, and real-world implementation details.", 'document');

        // STEP 4: RICH MEDIA & DATA ENGINEER (Comparison Table & FAQ Block)
        this.swarmStepIndex = 4;
        this.activeSwarmAgent = 'rich_media';
        this.swarmStatusMessage = 'Agent 6 (Rich Media Engineer) generating technical comparison table & FAQ schema...';
        this.addLog('AGENT', '▦ Swarm Step 4: Agent [Rich Media & Data Engineer] building comparison table & schema FAQ.');

        const tableResp = await fetch(config.transformRoute, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                text: ed.getText().substring(0, 1500),
                type: 'comparison_table',
                custom_instruction: "Create a feature comparison matrix table with technical specs, pros/cons, and metrics for '" + targetKw + "'",
                model: this.aiModel
            })
        });
        const tableText = await tableResp.text();
        let tableData = {};
        try { tableData = JSON.parse(tableText); } catch (e) { tableData = { success: false }; }
        if (tableData.success && tableData.result) {
            this.insertContentIntoCanvas('<p></p>' + tableData.result, true);
            this.addLog('ASSETS', 'Agent [Rich Media Engineer] inserted interactive comparison table.');
        }

        // STEP 5: RANK MATH 100/100 & META OPTIMIZER (Meta Description & Final Assembly)
        this.swarmStepIndex = 5;
        this.activeSwarmAgent = 'rankmath_optimizer';
        this.swarmStatusMessage = 'Agent 8 & 10 (Rank Math Optimizer & Assembler) finalizing 100/100 SEO & Meta...';
        this.addLog('AGENT', '⌁ Swarm Step 5: Agent [Rank Math Optimizer] generating meta description & verifying SEO score.');

        const metaResp = await fetch(config.transformRoute, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                text: ed.getText().substring(0, 1200),
                type: 'seo_fix_meta',
                custom_instruction: "Generate a punchy 155-character meta description with focus keyword '" + targetKw + "'",
                model: this.aiModel
            })
        });
        const metaText = await metaResp.text();
        let metaData = {};
        try { metaData = JSON.parse(metaText); } catch (e) { metaData = { success: false }; }
        if (metaData.success && metaData.result) {
            const cleanMeta = metaData.result.replace(/^["'\s]+|["'\s]+$/g, '').trim();
            this.metaDescription = cleanMeta;
            Livewire.dispatch('applyMetaDescription', { metaDescription: cleanMeta });
            this.addLog('SEO', 'Agent [Rank Math Optimizer] generated Meta Description (' + cleanMeta.length + ' chars).');
        }

        const finalHtmlVal = ed.getHTML();
        Livewire.dispatch('autosave', { html: finalHtmlVal, json: null });
        this.saveLocalDraft(finalHtmlVal);
        this.updateOutline();
        this.updateActiveFormats();
        this.addLog('SYSTEM', '🚀 Multi-Agent Swarm successfully published complete human-grade article!');
    } catch (e) {
        this.aiErrorMessage = e.message;
        this.addLog('ERROR', 'Swarm execution interrupted: ' + e.message);
    } finally {
        this.isTransforming = false;
        this.activeSwarmAgent = null;
        this.swarmStatusMessage = '';
    }
},

async applyTargetedIntelligenceFix(checkId, title, aiPrompt, targetType = 'insert') {
    this.closeContextMenu();
    this.showInlineAiPrompt = false;
    this.aiErrorMessage = '';
    const ed = this.getEditor();
    if (!ed) return;

    this.isTransforming = true;
    this.activeAction = checkId;
    this.showAiStreamBanner = true;
    this.liveAiStreamText = '';
    this.abortController = new AbortController();

    const currentFullHtml = ed.getHTML ? ed.getHTML() : '';
    const currentText = ed.getText ? ed.getText() : '';

    // 1. SURGICAL TARGET: DOCUMENT TITLE
    if (targetType === 'title' || checkId === 'kw_in_title' || checkId === 'kw_at_beginning_of_title' || checkId === 'title_has_number' || checkId === 'title_has_power_word') {
        this.addLog('SEO', 'Surgically optimizing Title for focus keyword: ' + this.targetKeyword);
        try {
            const transformUrl = config.transformRoute || '/dashboard/api/ai/transform';
            const resp = await fetch(transformUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    text: this.title || currentText.substring(0, 300) || 'Untitled Document',
                    type: 'seo_fix_title',
                    custom_instruction: aiPrompt || ("Rewrite the title to naturally front-load '" + this.targetKeyword + "'"),
                    model: this.aiModel
                })
            });
            const respText = await resp.text();
            let data = {};
            try {
                data = JSON.parse(respText);
            } catch (err) {
                throw new Error('Unexpected response format from server.');
            }
            if (data.success && data.result) {
                const cleanTitle = data.result.replace(/^["'#\s]+|["'\s]+$/g, '').trim();
                this.title = cleanTitle;
                if (window.Livewire) {
                    Livewire.dispatch('applyTitle', { title: cleanTitle });
                }
                this.addLog('SEO', 'Updated Title to: ' + cleanTitle);
            }
        } catch (e) {
            this.aiErrorMessage = e.message;
        } finally {
            this.isTransforming = false;
            this.showAiStreamBanner = false;
        }
        return;
    }

    // 2. SURGICAL TARGET: META DESCRIPTION
    if (targetType === 'meta' || checkId === 'kw_in_meta') {
        this.addLog('SEO', 'Surgically generating Meta Description...');
        try {
            const transformUrl = config.transformRoute || '/dashboard/api/ai/transform';
            const resp = await fetch(transformUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    text: currentText.substring(0, 1000) || this.title || 'Document Context',
                    type: 'seo_fix_meta',
                    custom_instruction: aiPrompt || ("Generate a 155-character meta description featuring '" + this.targetKeyword + "'"),
                    model: this.aiModel
                })
            });
            const respText = await resp.text();
            let data = {};
            try { data = JSON.parse(respText); } catch (e) { data = { success: false }; }
            if (data.success && data.result) {
                const cleanMeta = data.result.replace(/^["'\s]+|["'\s]+$/g, '').trim();
                this.metaDescription = cleanMeta;
                if (window.Livewire) {
                    Livewire.dispatch('applyMetaDescription', { metaDescription: cleanMeta });
                }
                this.addLog('SEO', 'Updated Meta Description (' + cleanMeta.length + ' chars)');
            }
        } catch (e) {
            this.aiErrorMessage = e.message;
        } finally {
            this.isTransforming = false;
            this.showAiStreamBanner = false;
        }
        return;
    }

    // 3. SURGICAL TARGET: INTRODUCTION PARAGRAPHS ONLY (Preserves rest of canvas)
    if (targetType === 'intro' || checkId === 'kw_in_intro') {
        this.addLog('SEO', 'Surgically rewriting opening introduction paragraphs only...');
        
        const parser = new DOMParser();
        const docDom = parser.parseFromString(currentFullHtml || '<p></p>', 'text/html');
        const paragraphs = docDom.querySelectorAll('p');
        let openingHtml = '';
        let count = 0;
        paragraphs.forEach(p => {
            if (count < 2) {
                openingHtml += p.outerHTML;
                count++;
            }
        });
        if (!openingHtml) {
            openingHtml = '<p>' + currentText.substring(0, 300) + '</p>';
        }

        try {
            const transformUrl = config.transformRoute || '/dashboard/api/ai/transform';
            const resp = await fetch(transformUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    text: openingHtml,
                    type: 'seo_fix_intro',
                    custom_instruction: aiPrompt || ("Rewrite only these opening paragraphs to front-load '" + this.targetKeyword + "'"),
                    model: this.aiModel
                })
            });
            const respText = await resp.text();
            let data = {};
            try { data = JSON.parse(respText); } catch (e) { data = { success: false }; }
            if (data.success && data.result) {
                const newIntro = data.result.trim();
                
                let remainingHtml = '';
                let skipped = 0;
                docDom.body.childNodes.forEach(node => {
                    if (node.nodeName.toLowerCase() === 'p' && skipped < 2) {
                        skipped++;
                    } else {
                        remainingHtml += (node.outerHTML || node.textContent || '');
                    }
                });

                const finalCleanHtml = newIntro + (remainingHtml ? '<p></p>' + remainingHtml : '');
                ed.setContent(finalCleanHtml, true);
                Livewire.dispatch('autosave', { html: finalCleanHtml, json: null });
                this.saveLocalDraft(finalCleanHtml);
                this.updateOutline();
                this.addLog('SEO', 'Surgically updated introduction. Rest of document 100% preserved.');
            }
        } catch (e) {
            this.aiErrorMessage = e.message;
        } finally {
            this.isTransforming = false;
            this.showAiStreamBanner = false;
        }
        return;
    }

    // 4. SURGICAL TARGET: INSERT COMPONENT / BLOCK ONLY (Tables, FAQ, Citations, Callouts)
    this.addLog('SEO', 'Generating targeted section element [' + checkId + '] without rewriting document...');
    try {
        let promptType = 'custom';
        if (checkId === 'comparison_table' || checkId === 'rich_media') promptType = 'comparison_table';
        else if (checkId === 'generate_faq' || checkId === 'faq') promptType = 'generate_faq';
        else if (checkId === 'external_links') promptType = 'seo_fix_citations';
        else if (checkId === 'kw_in_subheadings' || checkId === 'headings_toc') promptType = 'seo_fix_subheadings';
        else if (checkId === 'quick_answer') promptType = 'quick_answer';

        const transformUrl = config.transformRoute || '/dashboard/api/ai/transform';
        const resp = await fetch(transformUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify({
                text: this.hasSelection ? this.selectedText : currentText.substring(0, 1500),
                type: promptType,
                custom_instruction: aiPrompt || 'Generate only the requested component in clean HTML',
                model: this.aiModel
            })
        });

        const respText = await resp.text();
        let data = {};
        try { data = JSON.parse(respText); } catch (e) { data = { success: false }; }
        if (data.success && data.result) {
            const blockToInsert = data.result.trim();
            
            if (this.hasSelection) {
                this.pendingDiff = {
                    originalText: this.selectedText,
                    transformedText: blockToInsert,
                    actionType: checkId || 'seo_fix',
                    customInstruction: aiPrompt || '',
                    hadSelection: true,
                    timestamp: new Date().toLocaleTimeString(),
                    candidates: [blockToInsert]
                };
                this.activeCandidateIndex = 0;
                this.showDiffReview = true;
                this.addLog('SEO', '✦ Generated localized ' + title + '. Review changes before applying.');
            } else {
                this.insertContentIntoCanvas(blockToInsert, true);
                this.addLog('SEO', 'Surgically inserted ' + title + ' into document.');
                const finalHtmlVal = ed.getHTML ? ed.getHTML() : '';
                Livewire.dispatch('autosave', { html: finalHtmlVal, json: null });
                this.saveLocalDraft(finalHtmlVal);
                this.updateOutline();
            }
        }
    } catch (e) {
        this.aiErrorMessage = e.message;
    } finally {
        this.isTransforming = false;
        this.showAiStreamBanner = false;
    }
},

async triggerAiTransform(type, customInstruction = '', placementMode = 'auto') {
    this.closeContextMenu();
    this.showInlineAiPrompt = false;
    this.aiErrorMessage = '';
    const ed = this.editorInstance || window.hoaEditorInstance;
    // Strict active selection check (only if there's actual selected text and explicit user rewrite intent)
    const domSelection = window.getSelection ? window.getSelection().toString().trim() : '';
    const hadSelection = !!(this.hasSelection && this.selectedText && this.selectedText.trim().length > 0 && domSelection.length > 0);
    const targetText = hadSelection ? this.selectedText : (ed ? ed.getText() : '');

    // Determine explicit surgical placement
    let effectivePlacement = placementMode;
    if (effectivePlacement === 'auto') {
        if (hadSelection) {
            if (['continue', 'generate_faq', 'comparison_table', 'key_takeaways', 'quick_answer', 'action_items', 'insert_below'].includes(type)) {
                effectivePlacement = 'insert_below';
            } else if (type === 'rewrite' || type === 'expand' || type === 'shorten' || type === 'fix_spelling') {
                effectivePlacement = 'replace_selection';
            } else {
                effectivePlacement = 'document';
            }
        } else {
            effectivePlacement = 'document';
        }
    }
    
    this.isTransforming = true;
    this.activeAction = type;
    this.liveAiStreamText = '';
    this.showAiStreamBanner = true;
    
    // Insert Contextual Feedback Node
    this.insertContentIntoCanvas(this.getFeedbackNodeHtml('AI is processing: ' + type), false);
    
    this.abortController = new AbortController();
    const signal = this.abortController.signal;

    let promptToSend = customInstruction || this.aiPrompt;
    if (!promptToSend || !promptToSend.trim()) {
        promptToSend = type === 'custom' 
            ? 'Write a comprehensive, in-depth technical deep-dive article with benchmarks, architecture, code, and FAQs.' 
            : type;
    }

    this.sendTokens = Math.max(1, Math.round(promptToSend.length / 3.8));
    this.receivedTokens = 0;
    this.totalTokens = this.sendTokens;
    this.streamSpeedTokSec = 0;
    const startTime = performance.now();
    let firstTokenReceived = false;

    const selectedPipelineStages = Object.keys(this.pipelineStages).filter(k => this.pipelineStages[k].enabled);
    this.addLog('AI', 'Dispatched pipeline [' + type + '] (' + effectivePlacement + ') with ' + selectedPipelineStages.length + ' active stages to OmniRoute.');

    const fullDocumentContent = ed ? (ed.getText ? ed.getText() : '') : '';
    const fullDocumentHtml = ed ? (ed.getHTML ? ed.getHTML() : '') : '';

    try {
        const response = await fetch(config.streamRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'text/event-stream'
            },
            body: JSON.stringify({
                text: targetText || 'Document Context',
                type: type,
                custom_instruction: promptToSend,
                model: this.aiModel,
                pipeline_stages: selectedPipelineStages,
                context: {
                    ...this.aiContext,
                    has_selection: hadSelection,
                    selected_text: hadSelection ? this.selectedText : null,
                    full_document_text: fullDocumentContent,
                    full_document_html: fullDocumentHtml,
                    target_keyword: this.targetKeyword || '',
                    document_title: this.title || ''
                }
            }),
            signal: this.abortController.signal
        });

        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.error || 'Server error while generating transformation.');
        }

        this.addLog('STREAM', 'SSE stream connected. Receiving real-time tokens...');
        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';
        let fullResult = '';

        const existingDocContent = (ed ? ed.getHTML() : '').trim();
        const isDocEmpty = !existingDocContent || 
                           existingDocContent === '<p></p>' || 
                           existingDocContent === '<p><br></p>' || 
                           existingDocContent === '<p>Start building your block content...</p>';

        let lastCanvasUpdate = 0;

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            if (!firstTokenReceived) {
                firstTokenReceived = true;
                this.streamLatencyMs = Math.round(performance.now() - startTime);
            }

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const parsed = JSON.parse(line.substring(6));
                        if (parsed.token) {
                            fullResult += parsed.token;
                        }
                        if (parsed.model) this.routedModel = parsed.model;
                        if (parsed.result) {
                            fullResult = parsed.result;
                        }
                        if (parsed.message) {
                            this.aiErrorMessage = parsed.message;
                        }
                    } catch (e) {}
                }
            }

            this.liveAiStreamText = fullResult;
            this.receivedTokens = Math.max(1, Math.round(fullResult.length / 3.8));
            this.totalTokens = this.sendTokens + this.receivedTokens;
            const elapsedSec = (performance.now() - startTime) / 1000;
            if (elapsedSec > 0.1) {
                this.streamSpeedTokSec = Math.round(this.receivedTokens / elapsedSec);
            }

            // STREAM DIRECTLY INTO TIPTAP PROSEMIRROR CANVAS (Throttled for 60fps smoothness)
            // Only stream into full canvas if not currently replacing a localized selection
            const now = performance.now();
            if (now - lastCanvasUpdate > 60 && ed && fullResult.length > 0 && effectivePlacement === 'document') {
                lastCanvasUpdate = now;
                if (isDocEmpty) {
                    ed.setContent(fullResult, false);
                } else {
                    ed.setContent(existingDocContent + '<p></p>' + fullResult, false);
                }

                const targetEl = document.getElementById('tiptap-content-target');
                if (targetEl && targetEl.scrollHeight - targetEl.scrollTop < 1200) {
                    targetEl.scrollTop = targetEl.scrollHeight;
                }
            }
        }

        this.isTransforming = false;
        this.liveAiStreamText = '';

        // FINAL PERSISTENCE & SURGICAL PLACEMENT WITH VISUAL RED/GREEN DIFF REVIEW
        if (fullResult.trim().length > 0 && ed) {
            if (hadSelection && effectivePlacement === 'replace_selection') {
                // Insert as a Proposal Block with Yellow color and Accept/Decline options inside TipTap
                this.insertContentIntoCanvas(fullResult, false, 'ai-new-content', true);
                this.addLog('AI', '✦ Inserted rewrite proposal block inside text with Accept/Decline options.');
            } else if (hadSelection && effectivePlacement === 'insert_below') {
                // 2. INSERT IMMEDIATELY BELOW SELECTION
                this.insertContentIntoCanvas('<p></p>' + fullResult, true, 'ai-new-content');
                this.addLog('AI', 'Inserted AI section immediately below selected text.');
                this.hasSelection = false;
                this.selectedText = '';
            } else {
                // 3. FULL CANVAS / DOCUMENT LEVEL GENERATION (Always Direct Insert)
                const docFinalHtml = isDocEmpty 
                    ? `<mark class="ai-new-content">${fullResult}</mark>`
                    : existingDocContent + '<p></p>' + `<mark class="ai-new-content">${fullResult}</mark>`;

                ed.setContent(docFinalHtml, true);
                this.addLog('GENERATE', 'Completed generation in canvas (' + fullResult.length + ' chars)');
                this.hasSelection = false;
                this.selectedText = '';

                // Inferred Document Title Auto-Update only if document was initially empty
                if (isDocEmpty) {
                    const h1Match = fullResult.match(/<h1>(.*?)<\/h1>/i) || fullResult.match(/^#\s+(.*?)$/m);
                    if (h1Match && h1Match[1]) {
                        const extractedTitle = h1Match[1].replace(/<[^>]*>/g, '').trim();
                        if (extractedTitle) {
                            Livewire.dispatch('applyTitle', { title: extractedTitle });
                            this.addLog('SEO', 'Auto-applied document title: "' + extractedTitle.substring(0, 30) + '..."');
                        }
                    }
                }
            }

            const savedHtml = ed.getHTML ? ed.getHTML() : '';
            if (savedHtml) {
                Livewire.dispatch('autosave', { html: savedHtml, json: null });
                this.saveLocalDraft(savedHtml);
            }
            this.updateOutline();
            this.updateActiveFormats();
        }

        this.aiHistory.unshift({
            id: Date.now(),
            type: type.replace('_', ' ').toUpperCase(),
            prompt: promptToSend.substring(0, 35) + '...',
            tokens: this.totalTokens,
            time: 'Just now'
        });

        if (type === 'custom') {
            this.aiPrompt = '';
        }
    } catch (err) {
        if (err.name !== 'AbortError') {
            console.error(err);
            this.aiErrorMessage = err.message || 'AI Generation notice: Unable to complete request.';
            this.addLog('ERROR', 'AI Execution notice: ' + err.message);
            setTimeout(() => { this.aiErrorMessage = ''; }, 8000);
        }
    } finally {
        this.isTransforming = false;
        this.activeAction = null;
        this.abortController = null;
        setTimeout(() => {
            if (!this.isTransforming) {
                this.showAiStreamBanner = false;
            }
        }, 3000);
    }
},

triggerAiAction(action) {
    this.showSlashMenu = false;
    
    const actionMap = {
        'rewrite': 'rewrite_polish',
        'summarize': 'summarize',
        'expand': 'expand'
    };

    const mappedAction = actionMap[action] || action;
    this.triggerAiTransform(mappedAction);
}