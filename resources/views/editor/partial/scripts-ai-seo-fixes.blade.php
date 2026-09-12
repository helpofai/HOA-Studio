{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Surgical SEO Auto-Healer
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
    if (targetType === 'meta_description' || checkId === 'meta_desc_has_kw' || checkId === 'meta_desc_length_optimal') {
        this.addLog('SEO', 'Surgically optimizing Meta Description for focus keyword: ' + this.targetKeyword);
        try {
            const transformUrl = config.transformRoute || '/dashboard/api/ai/transform';
            const resp = await fetch(transformUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    text: this.metaDescription || currentText.substring(0, 500) || 'Document summary',
                    type: 'seo_fix_meta_description',
                    custom_instruction: aiPrompt || ("Write a compelling meta description under 160 characters containing '" + this.targetKeyword + "'"),
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
                const cleanMeta = data.result.replace(/^["'\s]+|["'\s]+$/g, '').trim();
                this.metaDescription = cleanMeta;
                if (window.Livewire) {
                    Livewire.dispatch('applyMetaDescription', { metaDescription: cleanMeta });
                }
                this.addLog('SEO', 'Updated Meta Description to: ' + cleanMeta);
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
    if (targetType === 'intro' || checkId === 'kw_in_intro' || checkId === 'kw_at_beginning_of_content') {
        this.addLog('SEO', 'Surgically optimizing Introduction section for focus keyword: ' + this.targetKeyword);
        const editorRoot = document.getElementById('tiptap-content-target');
        const firstP = editorRoot ? editorRoot.querySelector('p') : null;
        const introText = firstP ? firstP.innerText.trim() : currentText.substring(0, 400);

        if (firstP) {
            firstP.style.transition = 'box-shadow 0.4s ease';
            firstP.style.boxShadow = '0 0 0 3px #6366f1, 0 10px 25px -5px rgba(99, 102, 241, 0.5)';
            firstP.style.borderRadius = '8px';
            setTimeout(() => { firstP.style.boxShadow = ''; }, 3500);
        }

        const customInstruction = aiPrompt || ("Rewrite this introduction paragraph to naturally weave in the primary focus keyword '" + this.targetKeyword + "' within the first sentence.");
        await this.triggerAiTransform('surgical_intro', customInstruction, 'replace_selection', checkId);
        return;
    }

    // 4. SURGICAL TARGET: INSERT COMPONENT / BLOCK ONLY (Tables, FAQ, Citations, Callouts)
    if (targetType === 'insert_table' || checkId === 'geo_structured_synthesis' || checkId === 'comparison_table_present') {
        this.addLog('SEO', 'Surgically generating structured comparison table for GEO AI Overviews...');
        const customInstruction = aiPrompt || ("Generate an authoritative, responsive HTML comparison table synthesizing metrics, features, and trade-offs related to '" + (this.targetKeyword || 'this topic') + "'.");
        await this.triggerAiTransform('comparison_table', customInstruction, 'insert_below', checkId);
        return;
    }

    if (targetType === 'insert_faq' || checkId === 'geo_direct_answer' || checkId === 'faq_schema_present') {
        this.addLog('SEO', 'Surgically generating Direct Answer / FAQ block for GEO AI Overviews...');
        const customInstruction = aiPrompt || ("Generate a crisp 40-50 word direct definition box and FAQ block addressing search intent for '" + (this.targetKeyword || 'this topic') + "'.");
        await this.triggerAiTransform('generate_faq', customInstruction, 'insert_below', checkId);
        return;
    }

    if (targetType === 'insert_citation' || checkId === 'external_links_present' || checkId === 'authoritative_sources') {
        this.addLog('SEO', 'Surgically injecting authoritative source citations and studies...');
        const customInstruction = aiPrompt || ("Inject 2-3 contextual hyper-relevant study citations and external source references into this section.");
        await this.triggerAiTransform('insert_citations', customInstruction, 'insert_below', checkId);
        return;
    }

    // 5. DEFAULT SURGICAL FALLBACK
    const defaultInstruction = aiPrompt || ("Surgically optimize this text to pass the '" + title + "' SEO audit check.");
    await this.triggerAiTransform(checkId, defaultInstruction, 'auto', checkId);
},

toggleSeoHeatmap(forceState = null) {
    this.showSeoHeatmap = (forceState !== null) ? forceState : !this.showSeoHeatmap;
    if (this.showSeoHeatmap) {
        this.fetchSeoHeatmap();
    } else {
        const target = document.getElementById('tiptap-content-target');
        if (target) {
            const marks = target.querySelectorAll('mark.seo-heatmap-highlight');
            marks.forEach(m => {
                const parent = m.parentNode;
                if (parent) {
                    while (m.firstChild) parent.insertBefore(m.firstChild, m);
                    parent.removeChild(m);
                }
            });
        }
        this.addLog('SEO', 'Offline SEO Heatmap deactivated.');
    }
},

async fetchSeoHeatmap() {
    const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : (this.editorInstance || window.hoaEditorInstance));
    if (!ed) return;

    this.isAnalyzingHeatmap = true;
    const currentHtml = ed.getHTML ? ed.getHTML() : '';
    const kw = (this.$wire ? this.$wire.targetKeyword : '') || '';

    try {
        const url = config.seoHeatmapRoute || '/dashboard/api/seo/heatmap';
        const resp = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify({
                html: currentHtml,
                keyword: kw
            })
        });

        const respText = await resp.text();
        let data = {};
        try { data = JSON.parse(respText); } catch (e) { data = { success: false }; }

        if (data.success && data.heatmapHtml) {
            this.seoHeatmapHtml = data.heatmapHtml;
            this.showSeoHeatmap = true;

            const target = document.getElementById('tiptap-content-target');
            if (target) {
                target.innerHTML = data.heatmapHtml;
                this.addLog('SEO', 'Applied color-coded SEO Heatmap overlay (Density: ' + (data.density || 'N/A') + '%)');
            }
        }
    } catch (err) {
        console.error('Heatmap analysis failed:', err);
    } finally {
        this.isAnalyzingHeatmap = false;
    }
},

refreshSeoHeatmap() {
    return this.fetchSeoHeatmap();
},

handleHeatmapClick(event) {
    if (!event || !event.target) return;
    const highlight = event.target.closest('mark.seo-heatmap-highlight');
    if (highlight) {
        const type = highlight.getAttribute('data-seo-type') || 'Keyword';
        const tip = highlight.getAttribute('title') || 'SEO Element';
        this.addLog('SEO', `[Heatmap Element: ${type}] ${tip}`);
    }
},

locateSeoTarget(targetId, checkId = '') {
    requestAnimationFrame(() => {
        setTimeout(() => {
            const container = document.getElementById('tiptap-content-target');
            if (!container) return;

            let el = null;

            if (targetId) {
                el = container.querySelector('#' + targetId) || container.querySelector('.' + targetId) || container.querySelector('[data-seo-id="' + targetId + '"]');
            }

            const paragraphs = Array.from(container.querySelectorAll('p')).filter(p => p.innerText.trim().length > 10);
            const h1Element = container.querySelector('h1');
            const h2List = Array.from(container.querySelectorAll('h2'));
            const subheadings = Array.from(container.querySelectorAll('h2, h3, h4'));
            const tables = Array.from(container.querySelectorAll('table'));

            if (!el) {
                if (checkId === 'kw_in_intro' || checkId === 'kw_at_beginning_of_content' || targetId === 'seo-loc-kw_in_intro') {
                    el = paragraphs[0] || (h1Element && h1Element.nextElementSibling ? h1Element.nextElementSibling : null) || container.firstElementChild;
                } else if (checkId === 'kw_in_subheadings' || targetId === 'seo-loc-kw_in_subheadings') {
                    el = h2List[0] || subheadings[0] || paragraphs[1] || paragraphs[0];
                } else if (checkId === 'external_links_present' || checkId === 'outbound_citations' || targetId === 'seo-loc-external_links') {
                    el = paragraphs.length > 1 ? paragraphs[paragraphs.length - 2] : paragraphs[0];
                } else if (checkId === 'geo_direct_answer' || checkId === 'direct_definition_present' || targetId === 'seo-loc-geo_direct_answer') {
                    el = (h2List[0] && h2List[0].nextElementSibling) || h2List[0] || paragraphs[0];
                } else if (checkId === 'geo_structured_synthesis' || checkId === 'comparison_table_present' || targetId === 'seo-loc-geo_structured_synthesis') {
                    el = tables[0] || (h2List.length > 1 ? h2List[1] : paragraphs[0]);
                } else if (checkId === 'title_length_optimal' || checkId === 'title_sentiment_positive' || checkId === 'kw_in_title' || checkId === 'kw_at_beginning_of_title') {
                    el = h1Element || container.firstElementChild;
                } else if (checkId === 'meta_desc_has_kw' || checkId === 'meta_desc_length_optimal') {
                    el = paragraphs[0] || h1Element || container.firstElementChild;
                } else if (checkId === 'readability_flesch_kincaid' || checkId === 'paragraph_length_optimal') {
                    const bulkyP = paragraphs.find(p => p.innerText.split(/\s+/).length > 80);
                    el = bulkyP || paragraphs[Math.floor(paragraphs.length / 2)] || paragraphs[0];
                } else if (checkId === 'sentence_length_optimal' || checkId === 'passive_voice_minimal') {
                    el = paragraphs[Math.floor(paragraphs.length / 3)] || paragraphs[0];
                } else if (checkId === 'key_takeaways_present' || checkId === 'actionable_bullet_list') {
                    const ulList = container.querySelector('ul, ol');
                    el = ulList || (h2List[0] && h2List[0].nextElementSibling ? h2List[0].nextElementSibling : paragraphs[0]);
                } else if (checkId === 'faq_schema_present' || checkId === 'faq_accordion_present') {
                    const qHeading = h2List.find(h => h.innerText.includes('?') || h.innerText.toLowerCase().includes('faq'));
                    el = qHeading || (paragraphs.length > 1 ? paragraphs[paragraphs.length - 1] : paragraphs[0]);
                } else if (checkId === 'featured_snippet_potential') {
                    el = (h2List[0] && h2List[0].nextElementSibling) || paragraphs[0];
                } else if (checkId === 'content_freshness') {
                    el = paragraphs[0] || subheadings[0] || container.firstElementChild;
                } else if (checkId === 'competitive_gap_analysis') {
                    el = (h2List.length > 0 ? h2List[h2List.length - 1] : null) || paragraphs[paragraphs.length - 1] || paragraphs[0];
                } else if (checkId === 'semantic_depth') {
                    el = paragraphs.length > 1 ? paragraphs[1] : paragraphs[0];
                }
            }

            if (!el) {
                if (targetId === 'seo-loc-kw_in_intro') {
                    el = paragraphs[0] || (h1Element && h1Element.nextElementSibling ? h1Element.nextElementSibling : null) || container.firstElementChild;
                } else if (targetId === 'seo-loc-kw_in_subheadings') {
                    el = h2List[0] || subheadings[0] || paragraphs[1] || paragraphs[0];
                } else if (targetId === 'seo-loc-external_links') {
                    el = paragraphs.length > 1 ? paragraphs[paragraphs.length - 2] : paragraphs[0];
                } else if (targetId === 'seo-loc-geo_direct_answer') {
                    el = (h2List[0] && h2List[0].nextElementSibling) || h2List[0] || paragraphs[0];
                } else if (targetId === 'seo-loc-geo_structured_synthesis') {
                    el = tables[0] || (h2List.length > 1 ? h2List[1] : paragraphs[0]);
                } else {
                    el = paragraphs[0] || container.firstElementChild;
                }
            }

            if (el) {
                const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : (window.hoaEditorInstance || null));
                const tiptap = (ed && ed.editor) ? ed.editor : (ed && ed.commands ? ed : null);

                if (tiptap && tiptap.view) {
                    try {
                        const pos = tiptap.view.posAtDOM(el, 0);
                        if (typeof pos === 'number' && pos >= 0) {
                            if (typeof tiptap.commands?.setTextSelection === 'function') {
                                tiptap.commands.setTextSelection(pos);
                            }
                        }
                    } catch (posErr) {
                        console.warn('posAtDOM resolution error:', posErr);
                    }
                }

                el.scrollIntoView({ behavior: 'smooth', block: 'center' });

                el.style.transition = 'box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.2s ease';
                el.style.boxShadow = '0 0 0 3px #6366f1, 0 10px 25px -5px rgba(99, 102, 241, 0.5)';
                el.style.borderRadius = '8px';
                setTimeout(() => {
                    if (el) {
                        el.style.boxShadow = '';
                        el.style.transition = '';
                        el.style.borderRadius = '';
                    }
                }, 3000);

                if (tiptap && tiptap.view && typeof tiptap.view.focus === 'function') {
                    try {
                        tiptap.view.focus();
                    } catch (e) {}
                }

                this.addLog('SEO', 'Located [' + (checkId || targetId) + '] surgically in content canvas.');
            } else {
                this.addLog('SEO', 'Target position highlighted in editor.');
            }
        }, 80);
    });
},

async autoHealDocumentSeo() {
    const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : null);
    if (!ed) return;

    if (this.showSeoHeatmap) {
        this.toggleSeoHeatmap(false);
    }

    const kw = (this.$wire ? this.$wire.targetKeyword : '') || '';
    const pillars = (this.$wire && this.$wire.seoData) ? (this.$wire.seoData.rank_math || {}) : {};

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

    await this.triggerAiTransform('seo_auto_heal', holisticPrompt, 'document');
},
