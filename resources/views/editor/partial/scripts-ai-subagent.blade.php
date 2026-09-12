{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Sub-Content Sub-Agent Engine
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

    this.subAgentMode = mode;
    this.subAgentProposedText = '';
    this.showSubAgentProposal = false;

    let selectedText = '';
    let selectionRange = null;

    if (ed && ed.editor) {
        const { from, to } = ed.editor.state.selection;
        if (from !== to) {
            selectedText = ed.editor.state.doc.textBetween(from, to, ' ').trim();
            selectionRange = { from, to };
        }
    } else if (ed && typeof ed.getSelectedText === 'function') {
        selectedText = ed.getSelectedText().trim();
    }

    if (!selectedText) {
        selectedText = window.getSelection ? window.getSelection().toString().trim() : '';
    }

    if (!selectedText && ed && ed.editor) {
        const { $head } = ed.editor.state.selection;
        const node = $head.parent;
        if (node && node.type.name === 'paragraph') {
            selectedText = node.textContent.trim();
            const from = $head.before();
            const to = $head.after();
            selectionRange = { from, to };
        }
    }

    if (!selectedText) {
        this.addLog('WARN', '⚠️ Please highlight a paragraph first to trigger writing sub-agent.');
        return;
    }

    this.subAgentOriginalText = selectedText;
    this.subAgentSelectionRange = selectionRange;

    // Visually highlight selection on TipTap Editor Canvas with neon indicator
    if (ed && ed.editor && selectionRange && selectionRange.from !== undefined) {
        try {
            const { from, to } = selectionRange;
            ed.editor.chain().focus().setTextSelection({ from, to }).setHighlight({ color: '#fef08a' }).run();
        } catch (e) {
            console.warn('[sub-content-sub-agent] Failed to set TipTap highlight:', e);
        }
    }

    this.addLog('AI', `⚡ Dispatching Writing Sub-Agent [${mode.toUpperCase()}] on target block...`);

    // 1. Instant Algorithmic Transform Fallback (0ms offline result)
    const localProposal = this.applyLocalParagraphAction(mode, selectedText, {
        keyword: this.targetKeyword || (this.$wire ? this.$wire.targetKeyword : ''),
        title: this.title || (this.$wire ? this.$wire.title : '')
    });

    if (localProposal && localProposal !== selectedText) {
        this.subAgentProposedText = localProposal;
        this.showSubAgentProposal = true;
    }

    // 2. Dispatch background AI SSE stream transform for server intelligence
    this.triggerAiTransform(
        mode,
        customInstruction || `Recreate and transform this paragraph according to ${mode} mode.`,
        'sub_content_sub_agent'
    );
},

acceptSubAgentProposal() {
    const ed = this.getEditor ? this.getEditor() : (this.editorInstance || window.hoaEditorInstance);
    if (!this.subAgentProposedText) return;

    const newText = this.subAgentProposedText;

    if (ed && ed.editor) {
        const tiptap = ed.editor;

        if (this.subAgentSelectionRange && this.subAgentSelectionRange.from !== undefined) {
            const { from, to } = this.subAgentSelectionRange;
            try {
                tiptap.chain()
                    .focus()
                    .setTextSelection({ from, to })
                    .unsetHighlight()
                    .insertContent(newText)
                    .run();
            } catch (e) {
                tiptap.chain().focus().unsetHighlight().insertContent(newText).run();
            }
        } else {
            try {
                tiptap.chain().focus().unsetHighlight().insertContent(newText).run();
            } catch (e) {
                ed.insertHTML(newText);
            }
        }
    } else if (ed && typeof ed.insertHTML === 'function') {
        ed.insertHTML(newText);
    }

    // Visual Success Feedback on Canvas
    const container = document.getElementById('tiptap-content-target');
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

    this.addLog('SUCCESS', '✦ [sub-content-sub-agent] Accepted and applied proposal to canvas.');

    if (this.$wire && typeof this.$wire.runSeoAudit === 'function') {
        setTimeout(() => {
            try { this.$wire.runSeoAudit(); } catch (e) {}
        }, 300);
    }
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
