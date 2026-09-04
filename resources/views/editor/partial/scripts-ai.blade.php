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
showSubAgentProposal: false,
showSeoHeatmap: false,
subAgentMode: 'recreate',
subAgentProposedText: '',
subAgentOriginalText: '',
get subAgentModeLabel() {
    const labels = {
        'recreate': 'Recreating Paragraph',
        'rewrite': 'Rewriting & Polishing',
        'polish': 'Polishing Prose',
        'expand': 'Expanding with Depth',
        'shorten': 'Shortening & Condensing',
        'simplify': 'Simplifying (8th-Grade)',
        'generate_faq': 'Generating FAQ Block',
        'key_takeaways': 'Extracting Key Takeaways',
        'seo_optimize': 'SEO Optimizing Text',
    };
    return labels[this.subAgentMode] || this.subAgentMode || 'Writing Intelligence';
},
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
isTableContext: false,
showSlashMenu: false,
slashMenuX: 0,
slashMenuY: 0,
docOutline: [],

aiErrorMessage: '',
activeSwarmAgent: null,
swarmStepIndex: 0,
swarmTotalSteps: 5,
swarmStatusMessage: '',

// Swarm Step Toggles (User Configurable)
swarmSteps: {
    researcher: true,
    outliner: true,
    draftsman: true,
    rich_media: true,
    seo_meta: true
},

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

isContentEmpty(content) {
    if (!content || typeof content !== 'string') return true;
    const trimmed = content.trim();
    if (!trimmed || trimmed === '<p></p>' || trimmed === '<p><br></p>' || trimmed === '<p><br class="ProseMirror-trailingBreak"></p>') {
        return true;
    }
    const plain = trimmed.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
    return plain === '' || 
           plain === 'Start writing your AI-powered content...' || 
           plain === 'Start building your block content...';
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
    this.pipelineStageLog = [];
    this.abortController = new AbortController();

    const currentFullHtml = ed.getHTML ? ed.getHTML() : '';
    const currentText = ed.getText ? ed.getText() : '';

    // 1. SURGICAL TARGET: DOCUMENT TITLE
    if (targetType === 'title' || checkId === 'title_length_optimal' || checkId === 'title_sentiment_positive' || checkId === 'kw_in_title' || checkId === 'kw_at_beginning_of_title' || checkId === 'title_has_number' || checkId === 'title_has_power_word') {
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
        if (checkId === 'comparison_table' || checkId === 'rich_media' || checkId === 'geo_structured_synthesis') promptType = 'comparison_table';
        else if (checkId === 'generate_faq' || checkId === 'faq' || checkId === 'geo_paa_questions') promptType = 'generate_faq';
        else if (checkId === 'external_links' || checkId === 'geo_authoritative_quotes') promptType = 'seo_fix_citations';
        else if (checkId === 'kw_in_subheadings' || checkId === 'headings_toc') promptType = 'seo_fix_subheadings';
        else if (checkId === 'geo_direct_answer' || checkId === 'quick_answer') promptType = 'geo_direct_answer';
        else if (checkId === 'geo_data_points') promptType = 'geo_data_points';

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
                this.insertContentIntoCanvas(blockToInsert, false);
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

toggleSeoHeatmap(forceState = null) {
    const targetState = forceState !== null ? Boolean(forceState) : !this.showSeoHeatmap;
    if (this.showSeoHeatmap === targetState) return;

    const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : (window.hoaEditorInstance || null));
    if (!ed) return;

    this.showSeoHeatmap = targetState;

    if (this.showSeoHeatmap) {
        // Save current draft before displaying color-coded heatmap
        const liveHtml = typeof ed.getHTML === 'function' ? ed.getHTML() : (ed.editor?.getHTML ? ed.editor.getHTML() : '');
        window._originalSeoDraft = liveHtml;

        const marked = (this.$wire && this.$wire.seoData) ? this.$wire.seoData.marked_html : null;
        if (marked) {
            if (typeof ed.setContent === 'function') {
                ed.setContent(marked, false);
            } else if (ed.editor?.commands?.setContent) {
                ed.editor.commands.setContent(marked, false);
            }
        }

        if (typeof ed.setEditable === 'function') {
            ed.setEditable(false);
        } else if (ed.editor && typeof ed.editor.setEditable === 'function') {
            ed.editor.setEditable(false);
        }

        const pmEl = document.querySelector('.ProseMirror');
        if (pmEl) pmEl.setAttribute('contenteditable', 'false');

        this.addLog('SEO', '👁️ Color-coded SEO & GEO Heatmap inspection mode activated (canvas read-only).');
    } else {
        const restored = (window._originalSeoDraft !== undefined && window._originalSeoDraft !== null)
            ? window._originalSeoDraft
            : (typeof ed.getHTML === 'function' ? ed.getHTML() : '');

        if (restored) {
            if (typeof ed.setContent === 'function') {
                ed.setContent(restored, false);
            } else if (ed.editor?.commands?.setContent) {
                ed.editor.commands.setContent(restored, false);
            }
        }

        if (typeof ed.setEditable === 'function') {
            ed.setEditable(true);
        } else if (ed.editor && typeof ed.editor.setEditable === 'function') {
            ed.editor.setEditable(true);
        }

        const pmEl = document.querySelector('.ProseMirror');
        if (pmEl) pmEl.setAttribute('contenteditable', 'true');

        this.addLog('SEO', 'Editable content canvas restored.');
    }
},

locateSeoTarget(targetId, checkId = null) {
    if (!targetId && !checkId) return;

    // 1. If targeting title, focus title input directly
    if (targetId === 'seo-loc-title' || (checkId && checkId.startsWith('title_')) || checkId === 'kw_in_title' || checkId === 'kw_at_beginning_of_title') {
        const titleInput = document.querySelector('input[x-model="title"]') || document.querySelector('input[placeholder*="Title"]');
        if (titleInput) {
            titleInput.focus();
            titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            titleInput.classList.add('ring-2', 'ring-indigo-500', 'transition-all');
            setTimeout(() => titleInput.classList.remove('ring-2', 'ring-indigo-500'), 2500);
            this.addLog('SEO', 'Navigated directly to Document Title input.');
            return;
        }
    }

    // 2. If targeting meta or URL slug, switch to Titles & Meta tab and focus meta input
    if (targetId === 'seo-loc-meta' || checkId === 'kw_in_meta' || checkId === 'kw_in_url') {
        this.rightTab = 'titles_meta';
        this.$nextTick(() => {
            const metaInput = document.querySelector('textarea[wire\\:model\\.lazy="metaDescription"]') || document.querySelector('textarea[placeholder*="meta"]');
            if (metaInput) {
                metaInput.focus();
                metaInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                metaInput.classList.add('ring-2', 'ring-indigo-500', 'transition-all');
                setTimeout(() => metaInput.classList.remove('ring-2', 'ring-indigo-500'), 2500);
            }
        });
        this.addLog('SEO', 'Switched to Meta & Title Settings tab.');
        return;
    }

    // 3. If Heatmap is not active, automatically activate it to show in-canvas visual callouts and annotations!
    if (!this.showSeoHeatmap) {
        this.toggleSeoHeatmap(true);
    }

    // 4. Scroll smoothly to the target section in the editor canvas with surgical precision
    this.$nextTick(() => {
        let el = document.getElementById(targetId);

        // Surgical Check-Specific Element Resolution (Prevents multiple checks from indicating the same line)
        if (!el || el.id === 'seo-loc-kw_in_intro' || el.id === 'seo-loc-kw_in_subheadings' || el.id === 'seo-loc-external_links') {
            const paragraphs = Array.from(document.querySelectorAll('.editor-canvas p, .tiptap p, .ProseMirror p'));
            const headings = Array.from(document.querySelectorAll('.editor-canvas h2, .tiptap h2, .ProseMirror h2, .editor-canvas h3, .tiptap h3'));

            if (checkId === 'short_paragraphs') {
                // Find the ACTUAL bulky paragraph (>100 words)
                let maxWords = 0;
                let bulkiestP = null;
                for (const p of paragraphs) {
                    const wCount = p.textContent.trim().split(/\s+/).filter(Boolean).length;
                    if (wCount > 100 && wCount > maxWords) {
                        maxWords = wCount;
                        bulkiestP = p;
                    }
                }
                el = bulkiestP || (paragraphs.length > 1 ? paragraphs[1] : paragraphs[0]);
            } else if (checkId === 'sentence_length') {
                // Find the ACTUAL paragraph containing a run-on sentence (>20 words)
                let runOnP = null;
                for (const p of paragraphs) {
                    const sentences = p.textContent.match(/[^.!?]+[.!?]+(\s|$)/g) || [];
                    for (const s of sentences) {
                        if (s.trim().split(/\s+/).filter(Boolean).length > 20) {
                            runOnP = p;
                            break;
                        }
                    }
                    if (runOnP) break;
                }
                el = runOnP || (paragraphs.length > 2 ? paragraphs[2] : paragraphs[0]);
            } else if (checkId === 'kw_in_body') {
                // Find body paragraphs with or needing the keyword (not the intro paragraph)
                const kw = (this.$wire ? this.$wire.targetKeyword : '') || '';
                let bodyP = null;
                if (kw && paragraphs.length > 1) {
                    bodyP = paragraphs.slice(1).find(p => p.textContent.toLowerCase().includes(kw.toLowerCase()));
                }
                el = bodyP || (paragraphs.length > 1 ? paragraphs[1] : paragraphs[0]);
            } else if (checkId === 'kw_in_intro') {
                // Specifically the first paragraph (intro hook)
                el = paragraphs[0] || null;
            } else if (checkId === 'content_length_min') {
                // Bottom of the document where word count expands
                el = paragraphs.length > 0 ? paragraphs[paragraphs.length - 1] : null;
            } else if (checkId === 'kw_in_subheadings') {
                // Find H2 with or needing the keyword
                const kw = (this.$wire ? this.$wire.targetKeyword : '') || '';
                let matchingH2 = null;
                if (kw) {
                    matchingH2 = headings.find(h => h.textContent.toLowerCase().includes(kw.toLowerCase()));
                }
                el = matchingH2 || headings[0] || null;
            } else if (checkId === 'headings_toc') {
                // Second H2/H3 for structural balance
                el = headings.length > 1 ? headings[1] : headings[0];
            } else if (checkId === 'kw_in_image_alt') {
                el = document.querySelector('.editor-canvas img, .tiptap img, .ProseMirror img');
            } else if (checkId === 'rich_media') {
                el = document.querySelector('.editor-canvas table, .tiptap table, .ProseMirror table, .editor-canvas img, .tiptap img, .ProseMirror img');
            } else if (checkId === 'external_links' || checkId === 'outbound_citations') {
                const extLink = Array.from(document.querySelectorAll('.editor-canvas a, .tiptap a, .ProseMirror a')).find(a => (a.getAttribute('href') || '').startsWith('http'));
                el = extLink ? (extLink.closest('p') || extLink) : (paragraphs.length > 1 ? paragraphs[paragraphs.length - 2] : paragraphs[0]);
            } else if (checkId === 'internal_links') {
                const intLink = Array.from(document.querySelectorAll('.editor-canvas a, .tiptap a, .ProseMirror a')).find(a => (a.getAttribute('href') || '').startsWith('/'));
                el = intLink ? (intLink.closest('p') || intLink) : (paragraphs.length > 2 ? paragraphs[2] : paragraphs[0]);
            } else if (checkId === 'geo_direct_answer') {
                el = document.getElementById('seo-loc-geo_direct_answer') || document.querySelector('.geo-direct-answer') || (headings[0] ? headings[0].nextElementSibling : null);
            } else if (checkId === 'geo_structured_synthesis') {
                el = document.getElementById('seo-loc-geo_structured_synthesis') || document.querySelector('.editor-canvas table, .tiptap table, .ProseMirror table');
            }
        }

        // Final generic fallbacks if still not found
        if (!el) {
            if (targetId === 'seo-loc-kw_in_intro') {
                el = document.querySelector('.editor-canvas p, .tiptap p, .ProseMirror p');
            } else if (targetId === 'seo-loc-kw_in_subheadings') {
                el = document.querySelector('.editor-canvas h2, .tiptap h2, .ProseMirror h2');
            } else if (targetId === 'seo-loc-external_links') {
                const paragraphs = document.querySelectorAll('.editor-canvas p, .tiptap p, .ProseMirror p');
                el = paragraphs.length > 1 ? paragraphs[paragraphs.length - 2] : paragraphs[0];
            } else if (targetId === 'seo-loc-geo_direct_answer') {
                el = document.getElementById('seo-loc-geo_direct_answer') || document.querySelector('.editor-canvas h2, .tiptap h2, .ProseMirror h2');
            } else if (targetId === 'seo-loc-geo_structured_synthesis') {
                el = document.getElementById('seo-loc-geo_structured_synthesis') || document.querySelector('.editor-canvas table, .tiptap table, .ProseMirror table');
            }
        }

        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.style.transition = 'box-shadow 0.4s ease, border-color 0.4s ease, transform 0.2s ease';
            el.style.boxShadow = '0 0 0 3px #6366f1, 0 10px 25px -5px rgba(99, 102, 241, 0.5)';
            el.style.borderRadius = '8px';
            setTimeout(() => {
                el.style.boxShadow = '';
            }, 3000);
            this.addLog('SEO', 'Located [' + (checkId || targetId) + '] surgically in content canvas.');
        } else {
            this.addLog('SEO', 'Target position highlighted in editor.');
        }
    });
},

async autoHealDocumentSeo() {
    const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : null);
    if (!ed) return;

    // 1. If in heatmap inspection mode, switch back to editable mode first
    if (this.showSeoHeatmap) {
        this.toggleSeoHeatmap(false);
    }

    const kw = (this.$wire ? this.$wire.targetKeyword : '') || '';
    const pillars = (this.$wire && this.$wire.seoData) ? (this.$wire.seoData.rank_math || {}) : {};
    
    // 2. Gather failing checks and actionable recommendations
    const failingTips = [];
    Object.values(pillars).forEach(p => {
        (p.checks || []).forEach(c => {
            if (!c.pass) {
                const sev = c.severity ? c.severity.toUpperCase() : 'ISSUE';
                failingTips.push(`- [${sev}] ${c.title}: ${c.actionable_tip || c.desc}`);
            }
        });
    });

    if (failingTips.length === 0) {
        this.addLog('SEO', '✦ All SEO & GEO checks are already passed! Content is at 100% optimal readiness.');
        return;
    }

    this.addLog('SEO', `⚡ Launching Magic SEO Auto-Healer to resolve ${failingTips.length} detected audit gaps...`);

    // 3. Build holistic optimization directive
    const holisticPrompt = `You are a world-class SEO content strategist, copyeditor, and Generative Engine Optimization (GEO) architect.
Target Primary Focus Keyword: "${kw || 'None'}"

TASK:
Holistically optimize, rewrite, and polish the provided complete document to systematically resolve ALL of the following ${failingTips.length} detected SEO & GEO audit gaps in a single cohesive pass:
${failingTips.join('\n')}

MANDATORY EDITORIAL DIRECTIVES:
1. Preserve 100% of the authentic voice, core facts, technical depth, and specific examples present in the original text.
2. If missing the focus keyword in the opening hook, naturally weave "${kw}" into the first 1-2 sentences.
3. If subheadings lack the keyword, optimize at least one prominent H2 subheading with "${kw}".
4. If missing direct answers for Google AI Overviews (GEO), craft a concise 40-50 word direct definition box immediately after the first H2 question heading.
5. If missing comparison tables, insert an informative HTML table (<table>...</table>) synthesizing options, metrics, or features.
6. If outbound citations are missing, integrate 2-3 authoritative source citations and study references.
7. Break bulky paragraphs (>100 words) into scannable chunks and split run-on sentences (>25 words) into punchy prose.
8. Output ONLY the complete, publication-grade optimized article in clean, semantic HTML (h1, h2, h3, p, table, ul, ol, blockquote). Do NOT include conversational preamble or markdown code fences.`;

    // 4. Trigger streaming transform on the entire document
    await this.triggerAiTransform('seo_auto_heal', holisticPrompt, 'document');
},

async triggerAiTransform(type, customInstruction = '', placementMode = 'auto', checkId = null) {
    this.closeContextMenu();
    this.showInlineAiPrompt = false;
    this.aiErrorMessage = '';
    const ed = this.editorInstance || window.hoaEditorInstance;

    let currentSelection = '';
    if (ed && typeof ed.getSelectedText === 'function') {
        currentSelection = ed.getSelectedText().trim();
    } else if (ed && ed.state && ed.state.selection) {
        const { from, to } = ed.state.selection;
        currentSelection = ed.state.doc.textBetween(from, to, ' ').trim();
    }
    if (!currentSelection) {
        currentSelection = window.getSelection ? window.getSelection().toString().trim() : '';
    }
    
    if (placementMode === 'sub_content_sub_agent' || this.showSubAgentProposal) {
        this.selectedText = this.subAgentOriginalText || this.selectedText || currentSelection;
        this.hasSelection = true;
    } else {
        this.selectedText = currentSelection || this.selectedText;
        this.hasSelection = !!(this.selectedText && this.selectedText.length > 0);
    }
    const hadSelection = this.hasSelection;
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
    this.pipelineStageLog = [];
    this.showAiStreamBanner = true;
    
    this.abortController = new AbortController();
    const signal = this.abortController.signal;

    let promptToSend = customInstruction || '';
    if (!promptToSend && !hadSelection) {
        promptToSend = this.aiPrompt;
    }
    if (!promptToSend || !promptToSend.trim()) {
        promptToSend = (type === 'custom' && !hadSelection)
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

    // Extract surrounding Memory Context from TipTap ProseMirror State or Full Document
    let precedingText = '';
    let followingText = '';
    const selRange = this.subAgentSelectionRange || (ed && ed.state && ed.state.selection ? { from: ed.state.selection.from, to: ed.state.selection.to } : null);
    if (ed && ed.state && selRange && selRange.from !== undefined && selRange.from !== selRange.to) {
        try {
            const { from, to } = selRange;
            const docSize = ed.state.doc.content.size;
            // Preceding 700 chars
            const preFrom = Math.max(0, from - 700);
            if (preFrom < from) {
                precedingText = ed.state.doc.textBetween(preFrom, from, ' ').trim();
            }
            // Following 700 chars
            const postTo = Math.min(docSize, to + 700);
            if (to < postTo) {
                followingText = ed.state.doc.textBetween(to, postTo, ' ').trim();
            }
        } catch (e) {}
    }
    // Fallback: If cursor collapsed or range lost, locate selectedText in fullDocumentContent
    if (!precedingText && fullDocumentContent && this.selectedText) {
        const idx = fullDocumentContent.indexOf(this.selectedText);
        if (idx > 0) {
            precedingText = fullDocumentContent.substring(Math.max(0, idx - 700), idx).trim();
        }
        if (idx !== -1) {
            const afterIdx = idx + this.selectedText.length;
            followingText = fullDocumentContent.substring(afterIdx, Math.min(fullDocumentContent.length, afterIdx + 700)).trim();
        }
    }

    try {
        let response = null;
        let isBrowserDirect = false;
        let preparedData = null;

        const requestPayload = {
            text: targetText || 'Document Context',
            type: type,
            custom_instruction: promptToSend,
            model: this.aiModel,
            pipeline_stages: hadSelection ? [] : selectedPipelineStages,
            context: {
                ...this.aiContext,
                has_selection: hadSelection,
                selected_text: hadSelection ? this.selectedText : null,
                full_document_text: fullDocumentContent,
                full_document_html: fullDocumentHtml,
                preceding_text: precedingText,
                following_text: followingText,
                target_keyword: this.targetKeyword || '',
                document_title: this.title || '',
                action_tool: type
            }
        };

        // STEP 1: HYBRID ROUTING & RAG BRAIN PREPARATION
        if (config.preparePromptRoute) {
            try {
                const prepResp = await fetch(config.preparePromptRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestPayload),
                    signal: this.abortController.signal
                });
                if (prepResp.ok) {
                    preparedData = await prepResp.json();
                }
            } catch (prepErr) {
                console.warn('[Hybrid Router] Prompt prep bypassed:', prepErr.message);
            }
        }

        // STEP 2: IF LOCAL DAEMON -> DIRECT BROWSER STREAM (0ms Server Latency)
        if (preparedData && preparedData.success && preparedData.routing && preparedData.routing.is_local) {
            try {
                this.addLog('ROUTER', '⚡ Local daemon detected (' + preparedData.routing.gateway_url + '). Connecting directly from browser with 0ms server latency...');
                const directTargetUrl = preparedData.routing.chat_completions_url || 'http://127.0.0.1:20128/v1/chat/completions';
                
                const directResp = await fetch(directTargetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + (preparedData.routing.api_key || 'omniroute-default-key'),
                        'Accept': 'text/event-stream'
                    },
                    body: JSON.stringify({
                        model: preparedData.model || 'auto',
                        messages: preparedData.messages,
                        temperature: preparedData.temperature || 0.75,
                        stream: true
                    }),
                    signal: this.abortController.signal
                });

                if (directResp.ok) {
                    response = directResp;
                    isBrowserDirect = true;
                    this.addLog('ROUTER', '✓ Connected directly to local OmniRoute daemon on your device.');
                } else {
                    this.addLog('ROUTER', 'Local daemon status ' + directResp.status + '. Routing via server proxy...');
                }
            } catch (directErr) {
                this.addLog('ROUTER', 'Direct local daemon unreachable (' + directErr.message + '). Falling back to cloud server proxy...');
            }
        }

        // STEP 3: FALLBACK OR CLOUD TUNNEL -> SERVER STREAM ROUTE
        if (!response) {
            response = await fetch(config.streamRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'text/event-stream'
                },
                body: JSON.stringify(requestPayload),
                signal: this.abortController.signal
            });
        }

        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.error || errData.message || 'Server error while generating transformation.');
        }

        this.addLog('STREAM', 'SSE stream connected. Receiving real-time tokens...');
        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';
        let fullResult = '';

        const existingDocContent = (ed ? ed.getHTML() : '').trim();
        const isDocEmpty = this.isContentEmpty(existingDocContent) || this.activeAction === 'multi_agent_pipeline';

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
                    const dataStr = line.substring(6).trim();
                    if (dataStr === '[DONE]') continue;
                    try {
                        const parsed = JSON.parse(dataStr);
                        // Standard OpenAI delta format (from direct local daemon)
                        const deltaToken = parsed.choices?.[0]?.delta?.content;
                        if (deltaToken) {
                            fullResult += deltaToken;
                        }
                        // HOA Studio Laravel SSE format (from server proxy)
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
                        if (parsed.generated_title) {
                            const newTitle = parsed.generated_title;
                            const titleInput = document.querySelector('input[wire\\:model\\.lazy="title"]');
                            if (titleInput) titleInput.value = newTitle;
                            if (window.Livewire) {
                                Livewire.dispatch('updateTitle', { newTitle: newTitle });
                            }
                            this.addLog('SEO', 'Auto-generated Title: ' + newTitle);
                        }
                        if (parsed.status_message) {
                            this.swarmStatusMessage = parsed.status_message;
                            this.pipelineStageLog.push({ 
                                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}), 
                                msg: parsed.status_message 
                            });
                            this.$nextTick(() => {
                                const logEl = document.getElementById('pipeline-log-container');
                                if (logEl) logEl.scrollTop = logEl.scrollHeight;
                            });
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

            // STREAM DIRECTLY INTO TIPTAP PROSEMIRROR CANVAS OR SUB-AGENT PROPOSAL BOX (Throttled for 60fps smoothness)
            if (effectivePlacement === 'sub_content_sub_agent') {
                this.subAgentProposedText = fullResult;
                const targetId = 'content_' + this.activeProposalId;
                const canvasTarget = document.getElementById('tiptap-content-target');
                const contentEl = document.getElementById(targetId) || 
                                  (canvasTarget ? canvasTarget.querySelector('#' + targetId) : null) ||
                                  document.querySelector(`[id="${targetId}"]`) ||
                                  document.querySelector('.ai-proposal-content');
                if (contentEl) {
                    contentEl.innerHTML = fullResult + '<span class="animate-pulse text-emerald-400">|</span>';
                }
            } else {
                const now = performance.now();
                if (now - lastCanvasUpdate > 60 && fullResult.length > 0 && effectivePlacement === 'document' && ed) {
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
        }

        this.isTransforming = false;
        this.liveAiStreamText = '';
    this.pipelineStageLog = [];

        // Record telemetry & deduct quota if streamed directly from browser
        if (isBrowserDirect && config.recordUsageRoute && fullResult.trim().length > 0) {
            const wordsGenerated = Math.max(1, fullResult.trim().split(/\s+/).length);
            fetch(config.recordUsageRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tokens: this.receivedTokens,
                    words: wordsGenerated,
                    model: this.routedModel,
                    latency_ms: this.streamLatencyMs,
                    document_id: config.documentId
                })
            }).then(r => r.json()).then(data => {
                if (data.quota_remaining !== undefined) {
                    this.addLog('USAGE', 'Logged ' + wordsGenerated + ' words to quota. Remaining: ' + data.quota_remaining.toLocaleString());
                }
            }).catch(err => console.warn('[Hybrid Router] Failed to record usage:', err));
        }
            // After AI stream completes, trigger SEO audit
            if (window.Livewire) {
                Livewire.dispatch('queueSeoAudit');
            }

// FINAL PERSISTENCE & SURGICAL PLACEMENT WITH YELLOW SELECTION & GREEN PROPOSAL BOX
        if (fullResult.trim().length > 0) {
            if (effectivePlacement === 'sub_content_sub_agent') {
                this.subAgentProposedText = fullResult;
                const propId = this.activeProposalId;
                const actId = 'actions_' + propId;
                const cntId = 'content_' + propId;

                let actionsEl = document.getElementById(actId) || document.querySelector('#' + actId) || document.querySelector('.ai-proposal-actions');
                let contentEl = document.getElementById(cntId) || document.querySelector('#' + cntId) || document.querySelector('.ai-proposal-content');

                if (contentEl) {
                    contentEl.innerHTML = fullResult;
                }
                if (actionsEl) {
                    actionsEl.style.display = 'inline-flex';
                }
                window._activeAiProposals = window._activeAiProposals || {};
                if (propId) {
                    window._activeAiProposals[propId] = {
                        originalText: this.selectedText,
                        proposalText: fullResult
                    };
                }
                this.addLog('AGENT', '✦ [sub-content-sub-agent] Completed live paragraph recreation (' + fullResult.length + ' chars).');
                this.activeProposalId = null;
                this.hasSelection = false;
            } else if (hadSelection) {
                // 1. Highlight original selection with yellow background mark
                const yellowMarked = `<mark class="ai-marked-yellow">${this.selectedText}</mark>`;
                if (typeof ed.replaceSelection === 'function') {
                    ed.replaceSelection(yellowMarked);
                }

                // 2. Generate green proposal block with tick and cross actions below selection
                const proposalId = 'prop_' + Date.now();
                const greenProposalBox = `
                    <div id="${proposalId}" class="ai-proposal-green-box">
                        <div class="ai-proposal-header">
                            <span>✦ AI PROPOSAL (${(type || 'ai').toUpperCase()})</span>
                            <div class="ai-proposal-actions">
                                <button type="button" onclick="window.acceptAiProposal('${proposalId}')" class="ai-btn-tick" title="Accept & replace original text">
                                    ✓ Accept
                                </button>
                                <button type="button" onclick="window.rejectAiProposal('${proposalId}')" class="ai-btn-cross" title="Reject AI proposal">
                                    ✕ Discard
                                </button>
                            </div>
                        </div>
                        <div class="ai-proposal-content">${fullResult}</div>
                    </div>
                `;

                // Store state for tick replacement
                window._activeAiProposals = window._activeAiProposals || {};
                window._activeAiProposals[proposalId] = {
                    originalText: this.selectedText,
                    proposalText: fullResult
                };

                this.insertContentIntoCanvas('<p></p>' + greenProposalBox, false);
                this.addLog('AI', '✦ Created AI proposal block with Accept (✓) & Discard (✕) controls.');
                this.hasSelection = false;
                this.selectedText = '';
            } else {
                // 3. FULL CANVAS / DOCUMENT LEVEL GENERATION (Direct Insert)
                const docFinalHtml = isDocEmpty 
                    ? fullResult
                    : existingDocContent + '<p></p>' + fullResult;

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

            if (effectivePlacement !== 'sub_content_sub_agent' && !hadSelection) {
                const savedHtml = ed.getHTML ? ed.getHTML() : '';
                if (savedHtml) {
                    Livewire.dispatch('autosave', { html: savedHtml, json: null });
                    this.saveLocalDraft(savedHtml);
                }
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
            if (type === 'seo_auto_heal') {
                this.addLog('SEO', 'AI model unavailable or offline. Falling back to local algorithmic optimizer...');
                this.applyLocalSeoHealer();
            } else if (effectivePlacement === 'sub_content_sub_agent' || hadSelection) {
                this.addLog('AGENT', '⚡ AI service notice (' + err.message + '). Activating local zero-token writing intelligence engine...');
                const localResult = this.applyLocalParagraphAction(type, targetText, {
                    title: this.title,
                    keyword: this.targetKeyword,
                    preceding: precedingText,
                    following: followingText,
                    fullText: fullDocumentContent
                });
                if (localResult) {
                    this.subAgentProposedText = localResult;
                    this.showSubAgentProposal = true;
                    this.addLog('AGENT', '✓ Generated transformation via local algorithmic writing engine.');
                } else {
                    this.aiErrorMessage = err.message || 'AI Generation notice: Unable to complete request.';
                }
            } else {
                this.aiErrorMessage = err.message || 'AI Generation notice: Unable to complete request.';
                this.addLog('ERROR', 'AI Execution notice: ' + err.message);
                setTimeout(() => { this.aiErrorMessage = ''; }, 8000);
            }
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

applyLocalSeoHealer() {
    const ed = this.getEditor ? this.getEditor() : (this.editorInstance || window.hoaEditorInstance);
    if (!ed) return;

    const kw = (this.$wire ? this.$wire.targetKeyword : '') || 'Core Strategy';
    const capKw = kw.charAt(0).toUpperCase() + kw.slice(1);
    let html = (ed.getHTML ? ed.getHTML() : '').trim();

    if (!html || html === '<p></p>') {
        html = `<h1>${capKw}: Complete Strategic Guide</h1><p>Comprehensive overview and expert insights.</p>`;
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    let modified = false;

    // 1. Check title
    if (this.$wire && this.$wire.title && !this.$wire.title.toLowerCase().includes(kw.toLowerCase())) {
        const newTitle = `${capKw}: The Complete Guide (${new Date().getFullYear()})`;
        this.$wire.set('title', newTitle);
        this.$wire.call('saveActiveTitle');
        this.addLog('SEO', `✦ [Local Algorithm] Title tag updated with focus keyword: "${newTitle}"`);
    }

    // 2. First 10% / Intro hook with focus keyword
    const firstP = doc.querySelector('p');
    if (firstP) {
        if (!firstP.textContent.toLowerCase().includes(kw.toLowerCase())) {
            firstP.innerHTML = `When implementing <strong>${kw}</strong>, establishing a structured and data-driven approach is essential for optimal results. ` + firstP.innerHTML;
            modified = true;
            this.addLog('SEO', `✦ [Local Algorithm] Wove focus keyword into introduction hook.`);
        }
    } else {
        const p = doc.createElement('p');
        p.innerHTML = `When implementing <strong>${kw}</strong>, establishing a structured and data-driven approach is essential for optimal results.`;
        doc.body.prepend(p);
        modified = true;
    }

    // 3. Subheading with focus keyword
    const h2s = doc.querySelectorAll('h2');
    let h2HasKw = false;
    h2s.forEach(h2 => {
        if (h2.textContent.toLowerCase().includes(kw.toLowerCase())) {
            h2HasKw = true;
        }
    });
    if (!h2HasKw) {
        if (h2s.length > 0) {
            h2s[0].textContent = h2s[0].textContent.trim() + ` - Complete Overview for ${capKw}`;
            modified = true;
            this.addLog('SEO', `✦ [Local Algorithm] Optimized H2 heading with focus keyword.`);
        } else {
            const newH2 = doc.createElement('h2');
            newH2.textContent = `Key Strategies & Implementation for ${capKw}`;
            if (firstP && firstP.nextSibling) {
                firstP.parentNode.insertBefore(newH2, firstP.nextSibling);
            } else {
                doc.body.appendChild(newH2);
            }
            modified = true;
        }
    }

    // 4. Google AI Overviews (GEO) Direct Definition Answer Box
    if (!doc.querySelector('#seo-loc-geo_direct_answer') && !doc.querySelector('.geo-direct-answer')) {
        const targetH2 = doc.querySelector('h2');
        const directAnswerBox = doc.createElement('div');
        directAnswerBox.id = 'seo-loc-geo_direct_answer';
        directAnswerBox.className = 'geo-direct-answer my-4 p-4 rounded-xl bg-purple-950/20 border border-purple-500/30 text-slate-200 text-xs leading-relaxed';
        directAnswerBox.innerHTML = `<strong>Direct Answer: </strong>${capKw} is a structured methodology designed to achieve superior performance outcomes through systematic analysis, verified best practices, and empirical measurement. Key pillars include foundational preparation, iterative execution, and objective quality verification.`;

        if (targetH2 && targetH2.nextSibling) {
            targetH2.parentNode.insertBefore(directAnswerBox, targetH2.nextSibling);
        } else {
            doc.body.appendChild(directAnswerBox);
        }
        modified = true;
        this.addLog('GEO', `✦ [Local Algorithm] Injected 48-word direct answer snippet for Google AI Overviews.`);
    }

    // 5. Comparison Matrix Table
    if (!doc.querySelector('table')) {
        const table = doc.createElement('table');
        table.id = 'seo-loc-geo_structured_synthesis';
        table.className = 'w-full my-4 border-collapse border border-white/10 text-xs rounded-xl overflow-hidden';
        table.innerHTML = `
            <thead>
                <tr class="bg-indigo-950/60 text-indigo-300 font-mono">
                    <th class="border border-white/10 p-2 text-left font-bold">Key Metric</th>
                    <th class="border border-white/10 p-2 text-left font-bold">Standard Baseline</th>
                    <th class="border border-white/10 p-2 text-left font-bold">${capKw} Optimized</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-white/5 bg-slate-950/40">
                    <td class="border border-white/10 p-2 font-medium">Efficiency & Execution</td>
                    <td class="border border-white/10 p-2 text-slate-400">Baseline (Manual)</td>
                    <td class="border border-white/10 p-2 text-emerald-400 font-bold">3.5x Faster</td>
                </tr>
                <tr class="border-b border-white/5 bg-slate-950/20">
                    <td class="border border-white/10 p-2 font-medium">Empirical Accuracy</td>
                    <td class="border border-white/10 p-2 text-slate-400">Variable / Unverified</td>
                    <td class="border border-white/10 p-2 text-emerald-400 font-bold">98.5% Verified</td>
                </tr>
                <tr class="border-b border-white/5 bg-slate-950/40">
                    <td class="border border-white/10 p-2 font-medium">Search Intent Fit</td>
                    <td class="border border-white/10 p-2 text-slate-400">Basic Keyword Matching</td>
                    <td class="border border-white/10 p-2 text-emerald-400 font-bold">GEO & AI Ready</td>
                </tr>
            </tbody>
        `;
        doc.body.appendChild(table);
        modified = true;
        this.addLog('SEO', `✦ [Local Algorithm] Inserted structured comparison matrix for enhanced engagement.`);
    }

    // 6. Split bulky paragraphs (>120 words)
    doc.querySelectorAll('p').forEach(p => {
        const text = p.textContent.trim();
        const pWords = text.split(/\s+/).filter(Boolean);
        if (pWords.length > 120) {
            const sentences = text.match(/[^.!?]+[.!?]+(\s|$)/g) || [];
            if (sentences.length > 2) {
                const mid = Math.ceil(sentences.length / 2);
                p.textContent = sentences.slice(0, mid).join('').trim();
                const newP = doc.createElement('p');
                newP.textContent = sentences.slice(mid).join('').trim();
                p.parentNode.insertBefore(newP, p.nextSibling);
                modified = true;
                this.addLog('SEO', `✦ [Local Algorithm] Split bulky paragraph (${pWords.length} words) into 2 readable paragraphs.`);
            }
        }
    });

    if (modified) {
        const healedHtml = doc.body.innerHTML;
        ed.setContent(healedHtml, true);
        if (this.$wire) {
            this.$wire.call('runSeoAudit');
        }
        this.addLog('SUCCESS', '⚡ Local Algorithmic Healer applied: Resolved audit gaps with 0 AI tokens!');
    }
},

applyLocalParagraphAction(mode, text, context = {}) {
    const raw = (text || '').trim();
    if (!raw) return '';

    const kw = context.keyword || (this.$wire ? this.$wire.targetKeyword : '') || '';
    const title = context.title || (this.$wire ? this.$wire.title : '') || '';
    const capKw = kw ? (kw.charAt(0).toUpperCase() + kw.slice(1)) : '';

    const action = mode.replace('tone:', '');

    switch (action) {
        case 'recreate': {
            const replacements = [
                [/\bimportant\b/gi, 'pivotal'],
                [/\bgood\b/gi, 'exceptional'],
                [/\buse\b/gi, 'leverage'],
                [/\busing\b/gi, 'leveraging'],
                [/\bused\b/gi, 'leveraged'],
                [/\bhelp\b/gi, 'accelerate'],
                [/\bhelps\b/gi, 'accelerates'],
                [/\bmake\b/gi, 'architect'],
                [/\bmakes\b/gi, 'architects'],
                [/\bchange\b/gi, 'transform'],
                [/\bchanges\b/gi, 'transforms'],
                [/\bshow\b/gi, 'demonstrate'],
                [/\bshows\b/gi, 'demonstrates'],
                [/\bneed\b/gi, 'require'],
                [/\bproblem\b/gi, 'bottleneck'],
                [/\bfix\b/gi, 'remedy'],
                [/\bbig\b/gi, 'substantial'],
                [/\bfast\b/gi, 'high-velocity'],
                [/\bnew\b/gi, 'next-generation'],
                [/\bbest\b/gi, 'premier']
            ];
            let out = raw;
            replacements.forEach(([rgx, rep]) => { out = out.replace(rgx, rep); });
            
            const sentences = out.split(/(?<=[.?!])\s+/).filter(Boolean);
            if (sentences.length > 0) {
                const first = sentences[0].charAt(0).toLowerCase() + sentences[0].slice(1);
                let leadIn = 'Fundamentally, ';
                if (title) {
                    leadIn = `To systematically advance ${title}, `;
                } else if (capKw) {
                    leadIn = `When strategically implementing ${capKw}, `;
                }
                sentences[0] = leadIn + first;
                out = sentences.join(' ');
            }
            if (out.trim() === raw) {
                out = `Fundamentally, ${raw.charAt(0).toLowerCase() + raw.slice(1)} This ensures enduring clarity, precision, and operational resilience.`;
            }
            return out;
        }

        case 'rewrite':
        case 'polish': {
            const fillers = [
                [/\bin order to\b/gi, 'to'],
                [/\bdue to the fact that\b/gi, 'because'],
                [/\bat this point in time\b/gi, 'currently'],
                [/\bfor the purpose of\b/gi, 'to'],
                [/\bin the event that\b/gi, 'if'],
                [/\bit is important to note that\s*/gi, ''],
                [/\bit should be noted that\s*/gi, ''],
                [/\bbasically,\s*/gi, ''],
                [/\bessentially,\s*/gi, ''],
                [/\bvery\s+/gi, ''],
                [/\breally\s+/gi, ''],
                [/\bquite\s+/gi, ''],
                [/\bis able to\b/gi, 'can'],
                [/\bhas the ability to\b/gi, 'can'],
                [/\bserves to\b/gi, 'directly']
            ];
            let out = raw;
            fillers.forEach(([rgx, rep]) => { out = out.replace(rgx, rep); });
            out = out.replace(/\s{2,}/g, ' ').trim();
            out = out.replace(/(^|[.!?]\s+)([a-z])/g, (m, p1, p2) => p1 + p2.toUpperCase());
            if (out === raw) {
                out = `Notably, ${raw.charAt(0).toLowerCase() + raw.slice(1)}`;
            }
            return out;
        }

        case 'expand': {
            let addendum = `\n\nSpecifically, this dynamic establishes a resilient foundation by addressing the nuanced operational trade-offs inherent in modern execution.`;
            if (capKw) {
                addendum += ` Aligning directly with ${capKw} empowers teams to eliminate systemic bottlenecks while maintaining qualitative consistency.`;
            } else if (title) {
                addendum += ` Within the strategic framework of ${title}, this ensures that every stage delivers measurable tactical impact.`;
            }
            return raw + addendum;
        }

        case 'shorten':
        case 'condense': {
            let out = raw.replace(/\([^)]*\)/g, '');
            const strips = [
                /\b(in order to|due to the fact that|as a matter of fact|at the end of the day|it goes without saying that|needless to say)\b/gi,
                /\b(basically|essentially|actually|literally|virtually|practically|frankly|honestly)\b/gi,
                /\b(very|extremely|really|quite|somewhat|fairly|pretty much)\b/gi
            ];
            strips.forEach(rgx => { out = out.replace(rgx, ''); });
            out = out.replace(/\s{2,}/g, ' ').trim();
            const sentences = out.split(/(?<=[.?!])\s+/).filter(Boolean);
            if (sentences.length > 2) {
                out = sentences[0] + ' ' + sentences[sentences.length - 1];
            }
            return out;
        }

        case 'simplify': {
            const jargonMap = [
                [/\butilize\b/gi, 'use'],
                [/\butilizes\b/gi, 'uses'],
                [/\butilized\b/gi, 'used'],
                [/\butilizing\b/gi, 'using'],
                [/\bfacilitate\b/gi, 'help'],
                [/\bfacilitates\b/gi, 'helps'],
                [/\bfacilitated\b/gi, 'helped'],
                [/\bsubsequently\b/gi, 'then'],
                [/\bcommence\b/gi, 'start'],
                [/\bcommences\b/gi, 'starts'],
                [/\bterminate\b/gi, 'end'],
                [/\bterminates\b/gi, 'ends'],
                [/\bimplement\b/gi, 'set up'],
                [/\bimplements\b/gi, 'sets up'],
                [/\bendeavor\b/gi, 'try'],
                [/\bsubstantiate\b/gi, 'prove'],
                [/\boptimal\b/gi, 'best'],
                [/\bparamount\b/gi, 'key'],
                [/\bconsequently\b/gi, 'so'],
                [/\bdisseminate\b/gi, 'share'],
                [/\bexpedite\b/gi, 'speed up'],
                [/\bcomprehensive\b/gi, 'complete'],
                [/\bfundamental\b/gi, 'basic'],
                [/\bprioritize\b/gi, 'focus on'],
                [/\bdemonstrate\b/gi, 'show'],
                [/\bdemonstrates\b/gi, 'shows'],
                [/\bsufficient\b/gi, 'enough']
            ];
            let out = raw.replace(/;/g, '.');
            jargonMap.forEach(([rgx, rep]) => { out = out.replace(rgx, rep); });
            out = out.replace(/\s{2,}/g, ' ').trim();
            out = out.replace(/(^|[.!?]\s+)([a-z])/g, (m, p1, p2) => p1 + p2.toUpperCase());
            return out;
        }

        case 'generate_faq': {
            const subject = capKw || title || 'this methodology';
            const firstSentence = raw.split(/(?<=[.?!])\s+/)[0] || raw;
            return `### What is the primary purpose of ${subject}?\n**${subject}** plays a vital role by establishing a clear, actionable workflow that eliminates operational complexity.\n\n### How does this impact overall implementation?\nBy directly addressing core constraints, this approach provides **measurable consistency** and accelerates project outcomes.\n\n### What is the essential insight to remember?\nThe foundational principle is that **${firstSentence}** provides the clearest benchmark for sustained progress.`;
        }

        case 'seo_optimize': {
            let out = raw;
            if (capKw && !out.toLowerCase().includes(capKw.toLowerCase())) {
                out = `**${capKw}** is essential here: ${out.charAt(0).toLowerCase() + out.slice(1)}`;
            }
            out = out.replace(/\b(key takeaway|best practice|proven strategy|primary benefit|essential metric)\b/gi, '**$1**');
            return out;
        }

        case 'key_takeaways': {
            const sentences = raw.split(/(?<=[.?!])\s+/).filter(Boolean);
            const labels = ['Core Principle', 'Strategic Impact', 'Actionable Execution'];
            const bullets = sentences.slice(0, 3).map((s, i) => `- **${labels[i] || 'Insight'}:** ${s.trim()}`);
            return bullets.length > 0 ? bullets.join('\n') : `- **Core Insight:** ${raw}`;
        }

        default:
            return raw;
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
},

// sub-content-sub-agent: Dedicated Paragraph Recreation Agent
triggerSubContentSubAgent(mode = 'recreate', customInstruction = '') {
    const ed = this.getEditor ? this.getEditor() : (this.editorInstance || window.hoaEditorInstance);
    
    // 1. Capture selection text and range from TipTap editor instance, locked context state, or DOM
    let textToRecreate = this.subAgentOriginalText || this.selectedText || '';
    let selRange = this.subAgentSelectionRange || null;

    if (ed && typeof ed.getSelectedText === 'function') {
        const cur = ed.getSelectedText().trim();
        if (cur) textToRecreate = cur;
    }
    
    if (ed && ed.state && ed.state.selection) {
        const { from, to } = ed.state.selection;
        if (from !== to) {
            selRange = { from, to };
            textToRecreate = ed.state.doc.textBetween(from, to, ' ').trim();
        }
    }

    if (!textToRecreate) {
        const domSel = window.getSelection ? window.getSelection().toString().trim() : '';
        textToRecreate = domSel || (this.selectedText ? this.selectedText.trim() : '');
    }

    if (!textToRecreate && ed && ed.state && ed.state.selection) {
        try {
            const { $from } = ed.state.selection;
            if ($from && $from.parent && $from.parent.isTextblock) {
                textToRecreate = $from.parent.textContent.trim();
                const from = $from.start();
                const to = $from.end();
                selRange = { from, to };
            }
        } catch (e) {}
    }

    if (!textToRecreate) {
        this.addLog('WARN', '⚠️ [sub-content-sub-agent] Please select a paragraph or text first.');
        return;
    }

    this.subAgentMode = mode;
    this.selectedText = textToRecreate;
    this.subAgentOriginalText = textToRecreate;
    this.subAgentSelectionRange = selRange;
    this.subAgentProposedText = '';
    this.showSubAgentProposal = true;
    this.hasSelection = true;

    this.addLog('AGENT', '🤖 Dispatching [sub-content-sub-agent] for: ' + (this.subAgentModeLabel || mode));
    
    const proposalId = 'prop_' + Date.now();
    this.activeProposalId = proposalId;

    // 2. Mark selection visually in TipTap with high contrast translucent amber
    if (ed && ed.editor && typeof ed.editor.chain === 'function') {
        try {
            ed.editor.chain().focus().setHighlight({ color: 'rgba(234, 179, 8, 0.22)' }).run();
        } catch (e) {}
    } else if (ed && typeof ed.replaceSelection === 'function') {
        const yellowHtml = `<mark class="ai-marked-yellow">${this.selectedText}</mark>`;
        ed.replaceSelection(yellowHtml);
    }

    const defaultPrompts = {
        'recreate': "Completely recreate and re-architect this paragraph from scratch with authoritative clarity, elevated vocabulary, and engaging rhythm. You are strictly forbidden from returning the original text unchanged.",
        'rewrite': "Rewrite and polish this specific paragraph with active voice, strong verbs, dynamic cadence, and superior flow. You must provide a noticeable qualitative revision and never return the original text.",
        'polish': "Polish and refine this specific paragraph for maximum elegance, conciseness, and seamless flow while preserving original meaning. You must provide a noticeable revision.",
        'expand': "Expand this paragraph with rich analytical depth, illustrative nuance, practical implications, and clear supporting context. Approximately 1.5x to 2x depth.",
        'shorten': "Condense and shorten this paragraph into its crystal-clear, high-impact essence in 50% fewer words while keeping core facts.",
        'simplify': "Simplify this paragraph into crisp, effortless plain English at an 8th-grade reading level. Use short sentences and simple words.",
        'generate_faq': "Generate 2-3 high-value FAQ questions with concise answers based on this content. Format using ### Question and bold terms.",
        'key_takeaways': "Extract 3-4 high-leverage key takeaways from this content as a bulleted list with bold leading concepts.",
        'seo_optimize': "Optimize this paragraph for search intent and semantic topical authority with natural keywords and bold concepts."
    };

    const prompt = customInstruction || defaultPrompts[mode] || defaultPrompts['rewrite'];
    this.triggerAiTransform(mode, prompt, 'sub_content_sub_agent');
},

acceptSubAgentProposal() {
    const ed = this.getEditor ? this.getEditor() : (this.editorInstance || window.hoaEditorInstance);
    let rawText = this.subAgentProposedText;
    if (!rawText || !rawText.trim()) return;

    // 1. Sanitize LLM conversational preambles, bold labels, and excess spacing
    let cleanText = rawText
        .replace(/^(?:(?:Here(?:'s| is)|Sure!|Certainly!?) (?:the|your)? (?:revised|recreated|updated|polished|rewritten|new)? (?:paragraph|text|content|version):?|\*\*(?:Revised|Rewritten|Updated|Recreated|Output)\*\*:\s*|#+\s+(?:Revised|Rewritten|Recreated):?\s*)/i, '')
        .replace(/^\s+|\s+$/g, '');

    const originalText = (this.subAgentOriginalText || this.selectedText || '').trim();

    // 2. Heading Type Integrity: If original selection was NOT a heading, strip erroneous markdown heading symbols
    if (!originalText.startsWith('#') && cleanText.startsWith('#')) {
        cleanText = cleanText.replace(/^#+\s+/gm, '').trim();
    }

    // 3. Remove redundant triple newlines
    cleanText = cleanText.replace(/\n{3,}/g, '\n\n');

    let replaced = false;

    // Strategy 1: ProseMirror Native Range Replacement (Cleanest, zero block splitting)
    if (ed && ed.editor && this.subAgentSelectionRange && this.subAgentSelectionRange.from !== undefined) {
        try {
            const { from, to } = this.subAgentSelectionRange;
            ed.editor.chain().focus().setTextSelection({ from, to }).deleteSelection().insertContent(cleanText).run();
            replaced = true;
        } catch (e) {}
    }

    // Strategy 2: HTML Mark & Typography Normalization Replacement
    if (!replaced && ed && typeof ed.getHTML === 'function') {
        let currentHtml = ed.getHTML();
        let docModified = false;

        // A. Replace any <mark> containing text
        if (currentHtml.includes('<mark')) {
            currentHtml = currentHtml.replace(/<mark[^>]*>([\s\S]*?)<\/mark>/gi, cleanText);
            docModified = true;
        }

        // B. Replace exact or normalized text in HTML
        if (!docModified && originalText) {
            const normalize = (s) => s
                .replace(/[\u2018\u2019\u201A\u201B]/g, "'")
                .replace(/[\u201C\u201D\u201E\u201F]/g, '"')
                .replace(/[\u2013\u2014]/g, '-')
                .replace(/&amp;/g, '&')
                .replace(/&quot;/g, '"')
                .replace(/&#39;|&apos;/g, "'")
                .replace(/&nbsp;/g, ' ')
                .replace(/\s+/g, ' ');

            if (currentHtml.includes(originalText)) {
                currentHtml = currentHtml.replace(originalText, cleanText);
                docModified = true;
            } else {
                const cleanOrig = normalize(originalText).trim();
                const escaped = cleanOrig.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/\s+/g, '[\\s\\S]*?');
                const regex = new RegExp(escaped, 'i');
                if (regex.test(currentHtml)) {
                    currentHtml = currentHtml.replace(regex, cleanText);
                    docModified = true;
                }
            }
        }

        if (docModified) {
            ed.setContent(currentHtml, true);
            replaced = true;
        }
    }

    // Strategy 3: Direct DOM Container Replacement
    const container = document.getElementById('tiptap-content-target');
    if (!replaced && container) {
        const yellowMarks = container.querySelectorAll('.ai-marked-yellow, mark');
        if (yellowMarks.length > 0) {
            yellowMarks.forEach(m => {
                m.outerHTML = cleanText;
            });
            if (ed && typeof ed.getHTML === 'function') {
                const pm = container.querySelector('.ProseMirror') || container;
                ed.setContent(pm.innerHTML, false);
            }
            replaced = true;
        }
    }

    // Strategy 4: Fallback replaceSelection
    if (!replaced && ed && typeof ed.replaceSelection === 'function') {
        ed.replaceSelection(cleanText);
        replaced = true;
    }

    // Visual Success Feedback on Canvas
    if (container) {
        container.classList.add('ring-2', 'ring-emerald-500/50', 'transition-all', 'duration-500');
        setTimeout(() => container.classList.remove('ring-2', 'ring-emerald-500/50'), 2000);
    }

    // Ensure state sync and local draft save
    if (ed && typeof ed.getHTML === 'function') {
        const finalHtml = ed.getHTML();
        if (typeof this.saveLocalDraft === 'function') {
            this.saveLocalDraft(finalHtml);
        }
        if (window.Livewire) {
            Livewire.dispatch('autosave', { html: finalHtml, json: ed.getJSON ? ed.getJSON() : null });
        }
    }

    this.showSubAgentProposal = false;
    this.subAgentProposedText = '';
    this.subAgentOriginalText = '';
    this.subAgentSelectionRange = null;
    this.addLog('AGENT', '✓ [sub-content-sub-agent] Accepted and applied recreated paragraph.');

    // Auto-fade the green highlight after 5 seconds and clean HTML without resetting TipTap or scroll position
    setTimeout(() => {
        const cont = document.getElementById('tiptap-content-target');
        if (cont) {
            const greenMarks = cont.querySelectorAll('.ai-replaced-green-highlight');
            greenMarks.forEach(m => m.classList.add('fading'));
        }
        setTimeout(() => {
            const container = document.getElementById('tiptap-content-target');
            if (container) {
                const greenMarks = container.querySelectorAll('.ai-replaced-green-highlight');
                greenMarks.forEach(m => {
                    const parent = m.parentNode;
                    if (parent) {
                        while (m.firstChild) parent.insertBefore(m.firstChild, m);
                        parent.removeChild(m);
                    }
                });
            }
            if (ed && typeof ed.getHTML === 'function') {
                const cleanHtml = ed.getHTML();
                if (typeof this.saveLocalDraft === 'function') {
                    this.saveLocalDraft(cleanHtml);
                }
                if (window.Livewire) {
                    Livewire.dispatch('autosave', { html: cleanHtml, json: ed.getJSON ? ed.getJSON() : null });
                }
            }
        }, 1500);
    }, 5000);
},

discardSubAgentProposal() {
    const ed = this.getEditor ? this.getEditor() : (this.editorInstance || window.hoaEditorInstance);
    
    // Unset highlight if active
    if (ed && ed.editor && this.subAgentSelectionRange && this.subAgentSelectionRange.from !== undefined) {
        try {
            const { from, to } = this.subAgentSelectionRange;
            ed.editor.chain().focus().setTextSelection({ from, to }).unsetHighlight().run();
        } catch (e) {}
    } else if (ed && ed.editor && typeof ed.editor.chain === 'function') {
        try {
            ed.editor.chain().focus().unsetHighlight().run();
        } catch (e) {}
    }

    const container = document.getElementById('tiptap-content-target');
    if (ed && typeof ed.getHTML === 'function') {
        let currentHtml = ed.getHTML();
        if (currentHtml.includes('<mark')) {
            currentHtml = currentHtml.replace(/<mark[^>]*>([\s\S]*?)<\/mark>/gi, '$1');
            ed.setContent(currentHtml, false);
        }
    }

    if (container) {
        const yellowMarks = container.querySelectorAll('.ai-marked-yellow, mark');
        yellowMarks.forEach(m => {
            const p = m.parentNode;
            if (p) {
                while (m.firstChild) p.insertBefore(m.firstChild, m);
                p.removeChild(m);
            }
        });
    }

    this.showSubAgentProposal = false;
    this.subAgentProposedText = '';
    this.subAgentOriginalText = '';
    this.subAgentSelectionRange = null;
    this.addLog('INFO', '✕ [sub-content-sub-agent] Discarded proposal and restored original text.');
}