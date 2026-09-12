{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: SSE AI Stream Engine
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

async triggerAiTransform(type, customInstruction = '', placementMode = 'auto', checkId = null) {
    this.closeContextMenu();
    this.showInlineAiPrompt = false;
    this.aiErrorMessage = '';
    const ed = this.editorInstance || window.hoaEditorInstance;

    let currentSelection = '';
    if (ed && typeof ed.getSelectedText === 'function') {
        currentSelection = ed.getSelectedText().trim();
    } else if (ed && ed.state && ed.state.selection && ed.state.selection.from !== ed.state.selection.to) {
        const { from, to } = ed.state.selection;
        currentSelection = ed.state.doc.textBetween(from, to, ' ').trim();
    }
    if (!currentSelection) {
        currentSelection = window.getSelection ? window.getSelection().toString().trim() : '';
    }

    if (currentSelection) {
        this.selectedText = currentSelection;
        this.subAgentOriginalText = currentSelection;
        this.hasSelection = true;
    } else if (placementMode === 'sub_content_sub_agent') {
        this.selectedText = this.selectedText || this.subAgentOriginalText || '';
        this.hasSelection = !!(this.selectedText && this.selectedText.length > 0);
    } else {
        this.selectedText = currentSelection;
        this.hasSelection = !!(currentSelection && currentSelection.length > 0);
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

    if (ed && ed.state && ed.state.doc) {
        try {
            const doc = ed.state.doc;
            const from = selRange ? selRange.from : ed.state.selection.from;
            const to = selRange ? selRange.to : ed.state.selection.to;

            const startPos = Math.max(0, from - 1200);
            precedingText = doc.textBetween(startPos, from, '\n', ' ');

            const endPos = Math.min(doc.content.size, to + 1200);
            followingText = doc.textBetween(to, endPos, '\n', ' ');
        } catch (e) {
            console.warn('[AI Engine] Failed to extract exact selection context window, using document fallbacks:', e);
        }
    }

    if (!precedingText && targetText) {
        const idx = fullDocumentContent.indexOf(targetText);
        if (idx !== -1) {
            precedingText = fullDocumentContent.substring(Math.max(0, idx - 1000), idx);
            followingText = fullDocumentContent.substring(idx + targetText.length, idx + targetText.length + 1000);
        }
    }

    // Initialize 15-stage pipeline modal state
    this.resetPipelinePopupData(promptToSend.substring(0, 80));
    this.showPipelinePopup = false;
    this.pipelinePopupKeywords = [];
    this.pipelinePopupOutline = [];
    this.pipelinePopupSchema = null;
    this.pipelinePopupStatus = 'Dispatching request to OmniRoute multi-model gateway...';

    // Update active swarm step telemetry
    if (type === 'custom' || type === 'seo_auto_heal') {
        this.swarmStatusMessage = 'Agent 1/5: Search Intent & RAG Researcher analyzing prompt...';
        this.updatePipelineStage({ key: 'search_intent', status: 'running', detail: 'Analyzing user search intent & audience requirements' });
    }

    try {
        const streamUrl = config.streamRoute || '/dashboard/api/ai/stream';

        const payload = {
            type: type,
            custom_instruction: promptToSend,
            placement_mode: effectivePlacement,
            had_selection: hadSelection,
            text: targetText,
            selected_text: this.selectedText || '',
            full_document: fullDocumentContent,
            full_document_html: fullDocumentHtml,
            preceding_context: precedingText,
            following_context: followingText,
            document_title: this.title || (this.$wire ? this.$wire.title : '') || 'Untitled Article',
            focus_keyword: this.targetKeyword || (this.$wire ? this.$wire.targetKeyword : '') || '',
            model: this.aiModel,
            check_id: checkId,
            pipeline_stages: selectedPipelineStages
        };

        const response = await fetch(streamUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'text/event-stream'
            },
            body: JSON.stringify(payload),
            signal: signal
        });

        if (!response.ok) {
            let errMessage = 'Server error (' + response.status + ')';
            try {
                const errData = await response.json();
                errMessage = errData.message || errData.error || errMessage;
            } catch (e) {}
            throw new Error(errMessage);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            buffer += chunk;

            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep last incomplete fragment in buffer

            for (const line of lines) {
                const trimmed = line.trim();
                if (!trimmed || trimmed.startsWith(':')) continue; // Ignore heartbeats/comments

                if (trimmed.startsWith('data:')) {
                    const dataStr = trimmed.substring(5).trim();
                    if (!dataStr) continue;

                    if (dataStr === '[DONE]') {
                        break;
                    }

                    try {
                        const parsed = JSON.parse(dataStr);

                        if (!firstTokenReceived) {
                            firstTokenReceived = true;
                            this.timeToFirstTokenMs = Math.round(performance.now() - startTime);
                            this.addLog('AI', 'First token received in ' + this.timeToFirstTokenMs + 'ms');
                        }

                        // HOA Studio Laravel SSE format (from server proxy)
                        if (parsed.chunk !== undefined) {
                            this.liveAiStreamText += parsed.chunk;
                            this.receivedTokens += Math.max(1, Math.round(parsed.chunk.length / 3.8));
                            this.totalTokens = this.sendTokens + this.receivedTokens;

                            const elapsedSec = (performance.now() - startTime) / 1000;
                            if (elapsedSec > 0) {
                                this.streamSpeedTokSec = Math.round(this.receivedTokens / elapsedSec);
                            }
                        }

                        // Capture telemetry logs
                        if (parsed.log) {
                            this.pipelineStageLog.push(parsed.log);
                            this.addLog(parsed.log.type || 'PIPELINE', parsed.log.message || '');
                        }

                        // Capture pipeline popup structured telemetry
                        if (parsed.pipeline_stage) {
                            this.updatePipelineStage(parsed.pipeline_stage);
                        }

                        if (parsed.swarm_status) {
                            this.swarmStatusMessage = parsed.swarm_status;
                        }

                        if (parsed.routed_model) {
                            this.routedModel = parsed.routed_model;
                        }

                        if (parsed.error) {
                            throw new Error(parsed.error);
                        }
                    } catch (e) {
                        // Raw string fallback stream
                        if (!dataStr.startsWith('{')) {
                            this.liveAiStreamText += dataStr;
                            this.receivedTokens += Math.max(1, Math.round(dataStr.length / 3.8));
                            this.totalTokens = this.sendTokens + this.receivedTokens;
                        }
                    }
                }
            }
        }

        // Processing rest buffer line
        if (buffer.trim().startsWith('data:')) {
            const dataStr = buffer.trim().substring(5).trim();
            if (dataStr && dataStr !== '[DONE]') {
                try {
                    const parsed = JSON.parse(dataStr);
                    if (parsed.chunk) this.liveAiStreamText += parsed.chunk;
                } catch (e) {}
            }
        }

        this.addLog('SUCCESS', 'AI Stream finished (' + this.receivedTokens + ' tokens received, ' + this.streamSpeedTokSec + ' tok/s)');

        // Extract clean final article text and strip raw pipeline directive dumps
        const cleanContent = this.extractCleanFinalArticle(this.liveAiStreamText);

        if (cleanContent) {
            if (effectivePlacement === 'sub_content_sub_agent') {
                this.subAgentProposedText = cleanContent;
                this.showSubAgentProposal = true;
                this.addLog('AI', '✦ Proposal generated for selected block. Click ACCEPT to apply changes.');
            } else {
                this.insertContentIntoCanvas(cleanContent, effectivePlacement, checkId);
            }
        }

    } catch (err) {
        if (err.name === 'AbortError') {
            this.addLog('WARN', 'AI stream aborted by user.');
        } else {
            console.error('[AI Engine Error]', err);
            this.aiErrorMessage = err.message || 'An error occurred during generation.';
            this.addLog('ERROR', 'AI Transform failed: ' + this.aiErrorMessage);
        }
    } finally {
        this.isTransforming = false;
        this.showAiStreamBanner = false;
        this.activeAction = null;
        this.abortController = null;

        // Auto-run SEO audit scan if document content changed
        if (this.$wire && typeof this.$wire.runSeoAudit === 'function') {
            setTimeout(() => {
                try { this.$wire.runSeoAudit(); } catch (e) {}
            }, 500);
        }
    }
},

applyLocalSeoHealer() {
    const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : (window.hoaEditorInstance || null));
    if (!ed) return;

    this.addLog('SEO', '⚡ Launching Zero-Token Local Algorithmic Healer...');

    const currentHtml = ed.getHTML ? ed.getHTML() : '';
    const kw = (this.$wire ? this.$wire.targetKeyword : '') || '';
    const title = (this.$wire ? this.$wire.title : '') || '';
    const capKw = kw ? (kw.charAt(0).toUpperCase() + kw.slice(1)) : '';

    const parser = new DOMParser();
    const doc = parser.parseFromString(currentHtml, 'text/html');
    let modified = false;

    // 1. Ensure primary keyword in first H2 subheading if missing
    if (kw) {
        const h2List = doc.querySelectorAll('h2');
        let kwInH2 = false;
        h2List.forEach(h2 => {
            if (h2.innerText.toLowerCase().includes(kw.toLowerCase())) {
                kwInH2 = true;
            }
        });

        if (!kwInH2 && h2List.length > 0) {
            h2List[0].innerHTML = `${h2List[0].innerHTML}: Comprehensive Guide to ${capKw}`;
            modified = true;
            this.addLog('SEO', `✦ [Local Algorithm] Front-loaded keyword '${kw}' into primary H2 subheading.`);
        }
    }

    // 2. Break bulky paragraphs (>100 words) locally
    const paragraphs = doc.querySelectorAll('p');
    paragraphs.forEach(p => {
        const pText = p.innerText.trim();
        const pWords = pText.split(/\s+/);
        if (pWords.length > 95) {
            const midIndex = Math.floor(pWords.length / 2);
            const sentenceEnds = [];
            for (let i = midIndex - 15; i < midIndex + 15; i++) {
                if (pWords[i] && (pWords[i].endsWith('.') || pWords[i].endsWith('!') || pWords[i].endsWith('?'))) {
                    sentenceEnds.push(i);
                }
            }

            const splitAt = sentenceEnds.length > 0 ? sentenceEnds[0] + 1 : midIndex;
            const part1 = pWords.slice(0, splitAt).join(' ');
            const part2 = pWords.slice(splitAt).join(' ');

            if (part1 && part2) {
                p.innerHTML = part1;
                const newP = doc.createElement('p');
                newP.innerHTML = part2;
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
