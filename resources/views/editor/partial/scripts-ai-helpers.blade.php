{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: AI Engine State & Helpers
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

// AI Transform & Swarm Execution State
isTransforming: false,
activeAction: '',
aiErrorMessage: '',
showSwarmSteps: false,
swarmSteps: {
    researcher: true,
    outliner: true,
    draftsman: true,
    rich_media: true,
    seo_meta: true
},
showAiStreamBanner: false,
liveAiStreamText: '',
pipelineStageLog: [],
abortController: null,
timeToFirstTokenMs: 0,
activeProposalId: null,
subAgentSelectionRange: null,
swarmStatusMessage: '',
pipelinePopupKeywords: [],
pipelinePopupOutline: [],
pipelinePopupSchema: null,
pipelinePopupStatus: '',

showSubAgentProposal: false,
showSeoHeatmap: false,
seoHeatmapHtml: '',
isAnalyzingHeatmap: false,
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
        'inject_data_points': 'Injecting Data Points & Metrics',
        'add_code_snippet': 'Generating Code Implementation',
        'inject_counter_arguments': 'Injecting Trade-offs & Nuance',
        'surgical_micro_repair': 'Surgical Micro-Repair',
        'verify_lineage': 'Verifying Lineage & Evidence',
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

// 15-Stage Production Pipeline Modal & Intelligence State
showPipelinePopup: false,
pipelineActiveTab: 'stages',
pipelinePopupData: {
    topic: '',
    title: '',
    lsiKeywords: '',
    outline: [],
    schemaJsonLd: '',
    rawStageData: '',
    stages: [
        { id: 1, key: 'search_intent', icon: '🔍', name: 'Search Intent Analysis', status: 'pending', detail: 'Analyzes user search intent, audience profile & core hook' },
        { id: 2, key: 'keyword_research', icon: '🏷️', name: 'Keyword & Entity Research', status: 'pending', detail: 'Extracts high-value semantic LSI entities & RAG knowledge' },
        { id: 3, key: 'serp_competitor', icon: '🌐', name: 'SERP & Competitor Analysis', status: 'pending', detail: 'Formulates competitive depth benchmarks & superiority' },
        { id: 4, key: 'content_gaps', icon: '🎯', name: 'Content Gap Closure', status: 'pending', detail: 'Bridges overlooked edge cases, practical caveats & FAQs' },
        { id: 5, key: 'article_outline', icon: '📑', name: 'Outline Architecture', status: 'pending', detail: 'Architects non-overlapping H2/H3 structural chapters' },
        { id: 6, key: 'section_generation', icon: '✍️', name: 'Section-by-Section Synthesis', status: 'pending', detail: 'Drafts publisher-grade prose directly into canvas' },
        { id: 7, key: 'fact_verification', icon: '🛡️', name: 'Fact & Source Grounding', status: 'pending', detail: 'Validates technical metrics, parameters & domain accuracy' },
        { id: 8, key: 'originality_check', icon: '✨', name: 'Originality & Novelty Check', status: 'pending', detail: 'Ensures unique thought-leadership with zero generic filler' },
        { id: 9, key: 'seo_optimization', icon: '⌁', name: 'SEO Deep Optimization', status: 'pending', detail: 'Aligns keyword density, headers, and scannable visual anchors' },
        { id: 10, key: 'readability_opt', icon: '📖', name: 'Readability & Cadence Flow', status: 'pending', detail: 'Tunes sentence cadence, active voice, and transitional rhythm' },
        { id: 11, key: 'internal_links', icon: '🔗', name: 'Internal Link Suggestions', status: 'pending', detail: 'Identifies contextual high-intent internal link hooks' },
        { id: 12, key: 'media_suggestions', icon: '🖼️', name: 'Rich Media & Comparison Table', status: 'pending', detail: 'Formats structured comparison matrices and tables' },
        { id: 13, key: 'schema_generation', icon: '📋', name: 'Schema FAQ & JSON-LD', status: 'pending', detail: 'Compiles Schema.org Article & FAQPage JSON-LD structures' },
        { id: 14, key: 'quality_audit', icon: '🏆', name: 'Final 10-Point Quality Audit', status: 'pending', detail: 'Validates editorial compliance with enterprise publishing standards' },
        { id: 15, key: 'publish_assembly', icon: '🚀', name: 'Publish-Ready Assembly', status: 'pending', detail: 'Assembles clean final article into TipTap editor canvas' }
    ]
},

getPipelineCompletedCount() {
    if (!this.pipelinePopupData || !Array.isArray(this.pipelinePopupData.stages)) return 0;
    return this.pipelinePopupData.stages.filter(s => s.status === 'completed').length;
},

getPipelineProgressLabel() {
    if (!this.isTransforming && this.getPipelineCompletedCount() === 15) {
        return 'All 15 Stages Complete (Publish Ready)';
    }
    if (!this.isTransforming) {
        return 'Pipeline Ready';
    }
    const runningStage = this.pipelinePopupData.stages.find(s => s.status === 'running');
    if (runningStage) {
        return 'Stage ' + runningStage.id + ' Active: ' + runningStage.name;
    }
    return this.swarmStatusMessage || 'Executing Swarm Pipeline...';
},

resetPipelinePopupData(topic) {
    this.pipelinePopupData.topic = topic || '';
    this.pipelinePopupData.title = '';
    this.pipelinePopupData.lsiKeywords = '';
    this.pipelinePopupData.outline = [];
    this.pipelinePopupData.schemaJsonLd = '';
    this.pipelinePopupData.rawStageData = '';
    if (Array.isArray(this.pipelinePopupData.stages)) {
        this.pipelinePopupData.stages.forEach(s => {
            s.status = 'pending';
        });
    }
},

updatePipelineStage(payload) {
    if (!payload || !this.pipelinePopupData || !Array.isArray(this.pipelinePopupData.stages)) return;
    const key = payload.key || '';
    const id = payload.id;
    const stage = this.pipelinePopupData.stages.find(s => s.key === key || s.id === id);
    if (stage) {
        if (payload.status) stage.status = payload.status;
        if (payload.detail) stage.detail = payload.detail;
    }
    if (id && id > 1) {
        this.pipelinePopupData.stages.forEach(s => {
            if (s.id < id && s.status === 'pending') {
                s.status = 'completed';
            }
        });
    }
},

copyPipelineData() {
    const d = this.pipelinePopupData;
    let report = "=== 15-STAGE PRODUCTION PIPELINE REPORT ===\n";
    report += "Topic: " + (d.topic || 'N/A') + "\n";
    report += "Title: " + (d.title || 'N/A') + "\n";
    report += "Completed Stages: " + this.getPipelineCompletedCount() + " / 15\n\n";

    if (d.lsiKeywords) {
        report += "--- Extracted LSI Entities ---\n" + d.lsiKeywords + "\n\n";
    }
    if (d.outline && d.outline.length > 0) {
        report += "--- Section Outline Architecture ---\n";
        d.outline.forEach((o, i) => {
            report += (i + 1) + ". " + o.title + " (" + o.focus + ")\n";
        });
        report += "\n";
    }
    if (d.schemaJsonLd) {
        report += "--- Schema.org JSON-LD ---\n" + d.schemaJsonLd + "\n\n";
    }

    navigator.clipboard.writeText(report).then(() => {
        alert("Pipeline Intelligence Report copied to clipboard!");
    }).catch(() => {});
},

extractCleanFinalArticle(rawText) {
    if (!rawText || typeof rawText !== 'string') return '';
    let text = rawText;

    // 1. If output contains raw pipeline directives or stage dumps, extract to popup and strip from article
    const pipelineMarkerRegex = /(?:===+\s*⚡?\s*ACTIVE ENTERPRISE PRODUCTION PIPELINE[\s\S]*?===+\s*END OF PIPELINE DIRECTIVES\s*===+|#+\s*15-Stage Production Pipeline[\s\S]*?(?=(?:^#\s+|<h1|\Z))|Stage\s+\d+:\s*[^\n]+(?:\n+(?:Target Intent|LSI Entities|Focus|Drafting)[^\n]+)*)/gi;

    if (pipelineMarkerRegex.test(text)) {
        const matches = text.match(pipelineMarkerRegex);
        if (matches && matches.length > 0) {
            this.showPipelinePopup = true;
            this.pipelinePopupData.rawStageData = matches.join('\n\n');
        }
        text = text.replace(pipelineMarkerRegex, '').trim();
    }

    // 2. Extract and strip raw <script type="application/ld+json"> from editor canvas
    const schemaScriptRegex = /<script\b[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi;
    let schemaMatch;
    while ((schemaMatch = schemaScriptRegex.exec(text)) !== null) {
        if (schemaMatch[0]) {
            this.pipelinePopupData.schemaJsonLd = schemaMatch[0];
        }
    }
    text = text.replace(schemaScriptRegex, '').trim();

    // 3. Clean duplicate consecutive H2 titles (e.g. <h2>Title</h2>\nTitle)
    text = text.replace(/(<h2[^>]*>(.*?)<\/h2>)\s*(?:\2|\*\*?\2\*\*?)/gi, '$1');

    return text.trim();
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
