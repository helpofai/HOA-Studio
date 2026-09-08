# 📜 Changelog

All notable changes to **HelpOfAi Studio (HOA-Studio)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.8.4] - 2026-09-08

### Added
- **TipTap Pro Canvas — Surgical Micro-Section Regenerator Suite**:
  - **Data Points & Empirical Metrics (`inject_data_points`)**: Injects verifiable data points, percentage metrics, and benchmark statistics directly into selected paragraphs with bold emphasis.
  - **Code Implementation Generator (`add_code_snippet`)**: Generates syntactically correct, multi-language code blocks (`\`\`\`language`) with macOS terminal chrome and copy buttons.
  - **Trade-offs & Technical Nuance (`inject_counter_arguments`)**: Injects architectural caveats, limitations, and balanced objective perspectives into technical drafts.
  - **Surgical Micro-Repair Engine (`surgical_micro_repair`)**: Optimizes information density, active voice, and cognitive flow while strictly retaining technical entity names and facts.
  - **Dual Menu Integration**: Added 1-click execution across both the TipTap Selection Bubble Menu and the Right-Click Context Menu with sub-agent proposal preview diffs.

---

## [2.8.3] - 2026-09-08

### Added
- **Content Intelligence — Real-Time SSE Token Streaming & Typewriter Terminal**:
  - **ContentIntelligenceStreamController (`ContentIntelligenceStreamController.php`)**: Dedicated Server-Sent Events (SSE) streaming engine with non-blocking buffer flush, word quota verification, and real-time step streaming (`node_start`, `token`, `node_complete`, `done`).
  - **Live Typewriter Streaming Terminal (`resources/views/content-intelligence/index.blade.php`)**: Interactive Alpine.js typewriter terminal embedded in the Inspector Drawer. Streams token chunks live to the screen with blinking cursor (`▊`) and real-time telemetry log feed.
  - **Interactive Synaptic DAG Workflow Graph (10-Node Architecture)**: 7-tier dynamic directed acyclic graph mapping stages from Intake to Master TipTap Assembly with glowing state beacons and 1-click tab switching.
  - **Comprehensive Feature Tests**: Added 3 new tests covering SSE streaming authorization, unauthenticated guest redirects, and cross-user run isolation (33/33 Content Intelligence tests passing).

---

## [2.8.2] - 2026-09-08

### Fixed
- **Content Intelligence & Synthesizer Domain Drift Fix**:
  - Neutralized system prompts in `SectionDraftsmanService` by replacing domain-specific keywords (`"games journalist"`) with topic-anchored expert writer roles (`"principal technical writer, subject-matter expert, and systems architect"`).
  - Fixed false-positive domain classification in `ContentSynthesizer::detectDomain()` by prioritizing AI, LLM, Machine Learning, and Software Engineering keywords before gaming checks.
  - Isolated section-level drafting from full blog post routing in `ContentSynthesizer::generate()`, preventing template repetition across multiple section drafts.
  - Added topic-grounded AI and tech fallback prose generation.
- **Content Intelligence Hub Header UI & Layout Modernization**:
  - Replaced bulky banner container in `resources/views/content-intelligence/index.blade.php` with clean standard page header with subtle border divider (`border-b border-white/5`).
  - Standardized UI elements with `<x-glass.badge>` and `<x-glass.button>`.
  - Constrained engine dropdown width and streamlined controls to eliminate horizontal and vertical layout bloat.

---

## [2.8.1] - 2026-09-08

### Added
- **Content Intelligence — Multi-Archetype Article Engine (6 Canonical Archetypes)**:
  - **ArticleArchetype Enum (`ArticleArchetype.php`)**: Strongly typed domain enum defining 6 canonical article structural archetypes:
    1. `AUTO_DETECT`: AI-inferred adaptive outline based on inquiry extraction and domain classification.
    2. `COMPARATIVE_ROUNDUP`: Alternatives, competitor matrices, technical benchmarks, and verdict rankings.
    3. `TECHNICAL_TEARDOWN`: In-depth systems architecture, internals, code implementations, scaling, and failure modes.
    4. `STEP_BY_STEP_TUTORIAL`: Structured prerequisites, ordered code steps, edge-case troubleshooting, and production deployment.
    5. `EXECUTIVE_STRATEGY`: High-level business impact, ROI models, enterprise risk, governance, and phased execution roadmaps.
    6. `THOUGHT_LEADERSHIP`: Contrarian perspectives, paradigm shifts, first-principles logic, and forward-looking industry predictions.
  - **Defensive Migration (`2026_09_08_000001_add_article_archetype_to_content_missions_table.php`)**: Adds indexed `article_archetype` column to `content_missions` table with safe idempotency guardrails.
  - **Archetype-Aware Blueprint Synthesis (`ContentBlueprintService.php`)**: Strategic blueprint generator adapts AI prompts, UVPs, section hierarchies, and fallback templates to the chosen archetype.
  - **Mission Studio Modal Archetype Selector (`resources/views/content-intelligence/index.blade.php`)**: Modern dark glassmorphic 6-card interactive archetype selection matrix with instant preset switching, word count bounds, and inspector badge visualization.
  - **Comprehensive Test Suite Expansion (`tests/Feature/ContentIntelligenceTest.php`)**: Added 3 new tests covering archetype enum properties, database persistence/DTO hydration, and Livewire UI archetype configuration (321 total tests passing, 100% rate).

### Changed
- `ContentMissionDTO` and `CreateContentMission` updated with strongly typed `ArticleArchetype` support and backwards-compatible array hydration.
- Content Intelligence run list cards and inspector cards display archetype labels and layout badges.

---

## [2.8.0] - 2026-09-08

### Added
- **Content Intelligence Pipeline — Full OmniRoute AI Research Integration (All 11 Services)**:
  - **DynamicContentProvider Gateway (`DynamicContentProvider.php`)**: Centralized AI content gateway wrapping `OmniRouteClient` with `askJSON()`, `askText()`, and `askStream()` methods. All 8 pipeline services now route through this provider for real AI-generated research, analysis, and content creation. Graceful fallback to schema defaults in test/development environments; production calls hit GPT-4o-mini via OmniRoute.
  - **Search Intelligence AI (`SearchIntelligenceService`)**: Real AI-powered query cluster generation, topic universe mapping, SERP competitor analysis, and content gap detection replacing algorithmic stubs.
  - **Knowledge Fabric AI (`KnowledgeFabricService`)**: AI-generated verified claim graphs with epistemic state classification (`verified`, `partially_verified`, `contradicted`, `unverified`), evidence snippet extraction, and confidence scoring.
  - **Content Blueprint AI (`ContentBlueprintService`)**: AI-synthesized article angles, unique value propositions, target transformations, and required entity/concept identification.
  - **Adaptive Outline AI (`AdaptiveOutlineService`)**: AI-enhanced hierarchical section tree with must-answer questions, writing directives, claim assignments, and word count targets per section.
  - **Section Draftsman AI (`SectionDraftsmanService`)**: Real AI content generation per section — topic-aware, claim-grounded, persona-matched, revision-directive-aware HTML prose writing via OmniRoute.
  - **Critic Agent AI (`CriticAgentService`)**: AI-powered 6-dimension quality evaluation (fact grounding, completeness, search intent, brand voice, readability, SEO) producing specific revision directives, merged with algorithmic scoring.
  - **SEO Optimizer AI (`SeoOptimizationService`)**: AI-generated meta titles, descriptions, primary/secondary keywords, and SEO recommendations, merged with real keyword density analysis and JSON-LD schema synthesis.
- **Workflow Engine Telemetry (`ContentWorkflowEngine`)**:
  - Every pipeline node execution now logs an `AgentActivity` record (tokens used, latency, status, input payload, output summary) and a `BrainDecision` record (question, decision, reasoning, confidence, alternatives). Fully visible in the Inspector's "🤖 Agents & Router" tab.
- **Document Assembly Phase 5 Integration (`TipTapDocumentAssembler`)**:
  - Document assembly now automatically triggers Content Health Quality Audit (15-dimension), Risk Assessment (YMYL + human gating), and Content Genome Synthesis at final document compilation, ensuring every published article carries a complete quality fingerprint.

### Changed
- **Test Suite Extended**: 317 tests passing (1836 assertions, 100% pass rate). New integration tests across Content Brain phases validating AI provider routing, memory operations, surgical repairs, world model truth layer, agent orchestration, lineage extraction, and learning engine harvesting.

### Security
- All AI calls route through the authenticated OmniRoute gateway with 15-second circuit breaker timeouts.
- DynamicContentProvider gracefully falls back to schema defaults on provider failure — zero unhandled exceptions in production pipeline.

## [2.7.9] - 2026-09-07

### Added
- **Full TipTap Editor & Content Intelligence Neuro-Brain Integration**:
  - **Dedicated "🧠 Brain & Lineage" Sidebar Tab (`content-intelligence-tab-brain.blade.php`)**:
    - **7-Tier Sentence Lineage Inspector**: Deep provenance inspector tracing any active prose sentence across all 7 layers (`Source` $\rightarrow$ `Evidence` $\rightarrow$ `Claim` $\rightarrow$ `Sentence` $\rightarrow$ `Paragraph` $\rightarrow$ `Section` $\rightarrow$ `Article` $\rightarrow$ `Published URL`).
    - **Stale Fact Alert Banner & 1-Click Surgical Micro-Repair**: Detects when underlying research sources have changed and provides 1-click localized surgical rewriting without re-drafting the rest of the document.
    - **15-Dimension Content Health Assessment Scorecard**: Real-time evaluation across 15 dimensions (Search Intent, Information Quality, Evidence Strength, Factual Reliability, Topic Coverage, Entity Coverage, Semantic Depth, Original Value, Readability, Structure, SEO, Internal Linking, Freshness, Brand Alignment, User Value) with letter grade (`A+` to `F`) and actionable recommendations.
    - **Continuous Author Style Intelligence Matrix**: Live display of learned user style rules (`prefer_concise_sentences`, `eliminate_fluff_phrases`, `prefer_bulleted_breakdowns`) with confidence ratings and active toggles.
    - **Site-Level Cannibalization Shield & Linking Matrix**: Project-wide search intent collision detection and high-leverage internal cross-linking suggestions.
    - **Content Genome Snapshot Synthesizer**: 1-click DNA snapshotting preserving mission, topic, entity, claim, and quality DNA for future article knowledge inheritance.
  - **In-Canvas Floating AI Actions & Slash Commands (`canvas.blade.php`, `scripts-ai.blade.php`, `scripts-canvas.blade.php`)**:
    - Added `surgical_micro_repair` and `verify_lineage` sub-agent modes to the TipTap paragraph context menu and `/` Slash AI command palette.
    - Precision prompt engineering preserving surrounding context while stripping clichés and tightening factual grounding.
  - **Continuous Background Learning on Autosave (`DocumentEditor.php`)**:
    - Wired diff intelligence in `autosave()` comparing manual user prose revisions against previous versions via `UserFeedbackIntelligenceService::analyzeDiffAndRecordPreference()`.
  - **Direct Text Auditing in `QualityEngineService`**:
    - Added `auditDirectText(string $text, array $metadata = [])` method to evaluate 15-dimension health on arbitrary editor text without requiring an active `WorkflowRun`.
  - **Public Prose Repair in `MicroRepairService`**:
    - Exposed `repairProseUnit(string $text, ProblemCategory|string|null $category = null)` for immediate localized repair execution.
  - **Expanded Feature Test Suite (`tests/Feature/DocumentEditorTest.php`)**:
    - Added 5 new comprehensive integration tests covering neuro-brain state loading, surgical micro-repair, autosave diff learning, genome synthesis, and style rule toggles (17 total tests, 100% pass rate).

## [2.7.8] - 2026-09-07

### Added
- **Content Intelligence Phase 6: Content Lineage, Autonomous Learning Engine, User Feedback Intelligence & Site-Level Topic Strategy (`brain.md` Sections 26, 27, 28, 29)**:
  - **7-Tier Deep Content Lineage System (`ContentLineageService`, `ContentLineageNode`, `LineageTraceDTO`, `DownstreamImpactDTO`)**:
    - Complete unbroken traceability graph: `Source` $\rightarrow$ `Evidence` $\rightarrow$ `Claim` $\rightarrow$ `Sentence` $\rightarrow$ `Paragraph` $\rightarrow$ `Section` $\rightarrow$ `Article` $\rightarrow$ `Published URL`.
    - Answers fundamental provenance questions: "Where did this statement come from?", "Which articles depend on this source?", and "What content needs updating if this fact changes?".
    - Granular fact invalidation marking stale sentences without breaking downstream article integrity, paired with localized sentence resolution.
    - Integrated with `TipTapDocumentAssembler` for automatic sentence-level lineage extraction on document compilation.
  - **Autonomous Strategy Learning Engine (`AutonomousLearningEngineService`, `StrategyMemory`, `StrategyCandidateDTO`)**:
    - Closed cognitive feedback loop advancing successful strategic patterns through a 4-stage lifecycle: `Observation` $\rightarrow$ `Candidate` $\rightarrow$ `Validated` $\rightarrow$ `Adopted`.
    - Automated post-mission learning harvesting lessons from quality audits, evidence density, and readability performance.
    - Strategy candidate adoption and rejection workflows with evidence accumulation thresholds.
  - **User Feedback Intelligence Engine (`UserFeedbackIntelligenceService`, `UserStylePreference`)**:
    - Ingests and diffs manual user edits against AI-generated prose to infer authorial writing preferences (e.g. `prefer_concise_sentences`, `eliminate_fluff_phrases`, `prefer_bulleted_breakdowns`).
    - Progressive confidence calibration preventing hasty rule modifications on singular edits.
    - Interactive diff tester and rule activation toggles.
  - **Site-Level Topic Strategy & Portfolio Brain (`SiteTopicStrategyService`, `SiteTopicCluster`, `SitePortfolioReportDTO`)**:
    - Content portfolio analysis clustering articles by semantic topic domains and calculating cluster coverage scores ($0-100\%$).
    - Cross-document keyword cannibalization detection analyzing lexical and intent overlap with actionable merge/differentiation recommendations.
    - Uncovered subtopic opportunity discovery and internal cross-linking matrix generation.
  - **Livewire 3 UI Inspector Tab 11 `🧭 Lineage & Strategy` (`resources/views/content-intelligence/index.blade.php`, `ContentIntelligencePage.php`)**:
    - Interactive 7-tier sentence lineage inspector with upstream epistemic root drawer.
    - Autonomous strategy memories matrix with progress badges, evidence counters, confidence bars, and custom observation recorder.
    - User feedback style rules feed with active toggles and real-time diff analyzer test bed.
    - Site topic portfolio dashboard displaying cluster coverage gauges, cannibalization risk alerts, and cross-linking opportunities.
  - **Phase 6 Verification & Test Suite (`tests/Feature/ContentBrainLineageAndLearningTest.php`)**:
    - 8 comprehensive feature tests with 50 assertions covering 7-tier sentence lineage, downstream fact invalidation, strategy progression lifecycle, workflow lesson harvesting, diff preference learning, portfolio clustering, cannibalization detection, and dual-role Livewire 3 UI rendering.

## [2.7.7] - 2026-09-07

### Added
- **Content Intelligence Phase 5: Surgical Micro-Repair Loop, Multidimensional Quality & Risk Engine**:
  - **Surgical Micro-Repair Loop Engine (`MicroRepairService`, `MicroRepair`)**:
    - Localized smallest-affected-unit self-correction avoiding wasteful full-document regenerations.
    - Escalation ladder: `Sentence` $\rightarrow$ `Paragraph` $\rightarrow$ `Section` $\rightarrow$ `Article`.
    - Automated problem detection (run-on sentences, repetitive AI clichés, ungrounded absolute assertions), root-cause diagnosis, surgical localized string replacement, and word-level diff summaries.
  - **15-Dimension Multidimensional Content Health Model (`QualityEngineService`, `QualityHealthAudit`)**:
    - Replaces single flat scores with a weighted 15-dimension assessment (`Search Intent`, `Information Quality`, `Evidence Strength`, `Factual Reliability`, `Topic Coverage`, `Entity Coverage`, `Semantic Depth`, `Original Value`, `Readability`, `Structure`, `SEO`, `Internal Linking`, `Freshness`, `Brand Alignment`, `User Value`).
    - Letter grade calculation (`A+` to `F`), overall score ($0-100$), verified strengths, critical gaps, and actionable recommendations.
  - **Content Risk Engine & Verification Gating (`ContentRiskEngineService`, `RiskAssessment`)**:
    - YMYL classification (health/medical, finance/legal), risk score calculation ($0-100$), risk tiers (`LOW`, `MEDIUM`, `HIGH`, `CRITICAL`).
    - Mandatory primary source requirements enforcement and human approval checkpoints with 1-click signoff.
  - **Content Genome Knowledge Representation (`ContentGenomeService`, `ContentGenome`)**:
    - Structured knowledge asset capturing Mission DNA, Topics DNA, Entities DNA, Claims DNA, Facts DNA, Sources DNA, Quality DNA, and reusable fragments with deterministic cryptographic signatures for cross-mission knowledge inheritance.
  - **Livewire 3 UI Inspector Tab 10 `🔬 Health & Micro-Repair` (`resources/views/content-intelligence/index.blade.php`)**:
    - 15-dimension Content Health scorecard with ratings, progress bars, and explainable reasons.
    - Surgical Micro-Repairs feed with diff summaries and escalation levels.
    - Risk verification checklist with YMYL badges and 1-click human signoff button.
    - Content Genome explorer and 1-click synthesizer.
  - **Phase 5 Verification & Test Suite (`tests/Feature/ContentBrainSurgicalRepairAndQualityTest.php`)**:
    - 11 comprehensive tests with 64 assertions verifying problem detection, smallest-unit repair, escalation ladder, 15-dimension health audits, YMYL detection, human signoff gating, genome synthesis, knowledge inheritance, and Livewire UI integration.
- **Content Intelligence Phase 4: Agent Orchestration, Multi-Model Dynamic Routing & Brain Blackboard**:
  - **7 Specialized Worker Agents (`AgentOrchestratorService`, `AgentInterface`)**:
    - `ResearcherAgent`: Discovers authoritative external sources, data points, and empirical citations.
    - `AnalystAgent`: Conducts competitive SERP gap analysis, counter-narrative discovery, and Information Gain thesis framing.
    - `WriterAgent`: Composes cohesive, high-retention long-form narrative prose strictly grounded in validated evidence.
    - `FactCheckerAgent`: Epistemic fact verification auditing draft claims against source snippet citations to eliminate hallucinations.
    - `CriticAgent`: 6-rubric multidimensional quality evaluator (Depth, Accuracy, Angle, Readability, SEO, Conversion) generating actionable repair directives.
    - `SeoAgent`: Optimizes entity density, LSI keyword distribution, structured data schema graphs, and search intent alignment.
    - `EditorAgent`: Polishes sentence cadence, voice consistency, transition flow, and brand voice adherence.
  - **Task-Specialized Dynamic Model Router (`ModelRouterService`, `ModelRoutingDecisionDTO`)**:
    - Dynamic tier categorization (`fast`, `balanced`, `reasoning`, `high_accuracy`) mapping 9 specific task types to optimal OmniRoute models with automated fallback routing and latency benchmarking.
    - Real-time model tier metadata resolution with automatic quota tracking and cost optimization.
  - **Shared Mission Blackboard (`MissionBlackboard`, `BlackboardInterface`)**:
    - Centralized cognitive bulletin board allowing asynchronous inter-agent hypothesis sharing, key findings aggregation, and working drafts synthesis across workflow stages.
    - Automatic state persistence across pipeline execution runs with full JSON-serializable telemetry.
  - **Auditable Brain Decisions Engine (`BrainDecisionEngine`, `BrainDecision`)**:
    - Explainable decision logging recording decision keys, selected choices, rationale, alternative candidates considered, evaluation criteria weights, and confidence levels.
  - **Telemetry & Activity Logging (`AgentActivity`)**:
    - Full telemetry capture tracking agent execution duration, token consumption, latency, prompt templates, and output payload summaries.
  - **Livewire 3 Agents & Router Explorer UI (`resources/views/content-intelligence/index.blade.php`)**:
    - Tab 9 `🤖 Agents & Router` inspector pane featuring:
      - Interactive Model Routing Matrix with task tiers and live active models.
      - 7 Worker Agent Dispatch Cards with 1-click execution triggers and role badges.
      - Live Mission Blackboard status monitor displaying findings, hypotheses, and state.
      - Auditable Brain Decisions feed with rationale, confidence meters, and alternative options.
      - Agent Activity execution log displaying execution times, latency, and status.
  - **Verification & Test Suite (`tests/Feature/ContentBrainAgentOrchestratorAndRouterTest.php`)**:
    - 11 comprehensive tests with 56 assertions covering agent dispatch, blackboard updates, model routing, decision engine records, and Livewire UI integration.
- **Content Intelligence Phase 3: World Model, Deep Evidence Graph & Epistemic Truth Layer**:
  - **Dynamic Domain World Model Engine (`WorldModelService`, `WorldEntity`, `WorldRelationship`)**:
    - Canonical entity catalog with multi-variant alias resolution, temporal validity dates, and JSON attributes.
    - Directional semantic graph linking subject-predicate-object relationships with confidence weights and explanations.
    - Automated detection of architectural incompatibilities and conflicts (`incompatible_with`, `conflicts_with`, `deprecated_by`) across domain tech stacks.
    - Prerequisite dependency chain resolution (`requires`) determining technical prerequisites before generating guide sections.
  - **Deep Evidence Graph Engine (`DeepEvidenceGraphService`, `EvidenceSnippet`, `ClaimEvidenceLink`)**:
    - Verbatim quote extract capture with character offsets, section headings, and page coordinates directly linked to authoritative sources.
    - Bidirectional claim grounding with typed relationship semantics: `SUPPORTS`, `REFUTES`, `QUALIFIES`, and `CONTEXTUALIZES`.
    - Weighted epistemic consensus calculator evaluating source credibility and detecting active contradictions (`is_contradicted`).
    - Full 5-tier end-to-end lineage tracer (`SOURCE ➔ EVIDENCE ➔ CLAIM ➔ SENTENCE ➔ DOCUMENT`) linking TipTap text directly to original source documentation.
  - **Truth Layer Epistemic Audit (`TruthLayerService`, `TruthAuditReportDTO`)**:
    - Mission-wide epistemic health assessment calculating overall truth scores ($0 - 100\%$), risk ratings (`LOW`, `MEDIUM`, `HIGH`, `CRITICAL`), and verified vs contradicted claim breakdown.
    - Dynamic actionable truth directives and alerts flagging unverified assertions before assembly.
  - **Livewire 3 World & Truth Explorer UI (`resources/views/content-intelligence/index.blade.php`)**:
    - New `🌐 World & Truth` inspector tab featuring the Truth Layer Audit Scorecard, Deep Evidence Lineage Explorer, interactive claim selector pills, and World Model Knowledge Graph matrix.
  - **Robust Verification Suite (`ContentBrainWorldModelAndTruthTest.php`)**:
    - 10 feature tests with 55 assertions covering entity resolution, relationship linking, incompatibility detection, evidence snippet grounding, consensus calculation, deep lineage tracing, truth audits, and Livewire UI integration.
- **Content Intelligence Phase 2: Cognitive Memory OS (3-Tier Brain Architecture)**:
  - **3-Tier Brain Hierarchy**:
    - *Level 1 (Site Brain)*: Cross-mission and workspace memory; scans existing documents to prevent topic cannibalization and supply brand profile guidelines.
    - *Level 2 (Project Brain)*: Project-scoped working memory, knowledge triples, and mission-specific architectural constraints.
    - *Level 3 (Article Brain)*: Granular sentence-level dependency graph (`ArticleElementNode`); tracks Section -> Paragraph -> Sentence -> Claim mapping with surgical downstream fact invalidation.
  - **8-Stage Memory Admission Gate Protocol (`MemoryAdmissionGate`, `DuplicateDetector`, `EvidenceValidator`)**:
    - Automatic screening of newly synthesized facts: schema validation, minimum confidence ($\ge 0.80$), duplicate detection, contradiction audit, and minimum importance threshold ($\ge 0.70$) before admitting facts into `brain_memories`.
  - **Non-Destructive Memory Decay & Lineage Reconciliation (`MemoryDecayService`)**:
    - Half-life freshness score calculation; automatic transition of contradicted or outdated facts to `SUPERSEDED` and `OUTDATED` while preserving historical lineage (`v1 ➔ v2`).
  - **Focused Context Retrieval (`AttentionEngine`)**:
    - Synthesizes high-salience, budget-constrained context for LLM prompts without token waste.
  - **Livewire 3 Memory OS Inspector Console**:
    - Tab 7 in Content Intelligence Hub for inspecting Site/Project/Article brain memories, decay checks, and surgical downstream fact invalidation.
  - **Comprehensive Phase 2 Test Suite (`ContentBrainMemoryOSTest.php`)**:
    - 11 feature tests with 58 assertions verifying admission gates, decay engines, 3-tier brains, and Livewire UI.
- **Multi-Stage Content Intelligence + Autonomous Writing Pipeline (Phase 1)**:
  - **Dynamic 10-Node Workflow Graph Execution Engine (`WorkflowGraph`, `WorkflowNode`, `WorkflowRun`)**:
    - Replaced monolithic single-prompt content generation with a structured, multi-stage state machine where each node produces rich structured telemetry utilized by downstream nodes.
    - Implemented 10 specialized intelligence & authoring nodes:
      - `AudienceIntentNode`: Analyzes search intent, persona pain points, cognitive reading stage, and tone directives.
      - `SERPResearchNode`: Validates keyword opportunities, search volume signals, and competitive gap parameters.
      - `InformationGainNode`: Discovers non-obvious perspectives, unique statistics, proprietary counter-narratives, and value hooks.
      - `FactEvidenceNode`: Collects and validates statistical claims and verifiable citations before prose generation.
      - `AngleOutlineNode`: Builds hierarchical section architectures (H2/H3), reading flows, and narrative focal points.
      - `DraftCompositionNode`: Generates deep-dive, high-retention long-form prose strictly bound to researched evidence.
      - `StyleEditorNode`: Polishes tone, cadence, rhythm, passive-to-active transformations, and brand voice adherence.
      - `FactAuditNode`: Rigorously audits generated claims against extracted evidence tables to ensure zero AI hallucinations.
      - `SEOOptimizerNode`: Optimizes entity density, semantic keywords, heading structures, and search intent signals.
      - `QualityEvaluationNode`: Performs rubric-based multidimensional evaluation (Depth, Accuracy, Angle, Readability, SEO, Conversion).
  - **Self-Correcting Dynamic Quality Feedback Loops**:
    - Automated threshold gating (default 80/100 quality benchmark). Failed evaluations trigger targeted back-propagation loops to composition and outline nodes with specific remediation diagnostics.
  - **TipTap Document Auto-Assembly & Bidirectional Persistence (`AssembleDocumentAction`)**:
    - Automatically translates completed pipeline payloads into ProseMirror TipTap document schemas (headings, paragraphs, blockquotes, bullet lists) and clean HTML, creating or updating Workspace documents with real-time word counting and metadata tags.
  - **Interactive Content Intelligence Hub (`/dashboard/content-intelligence`)**:
    - High-performance Livewire 3 workspace interface featuring real-time KPI metrics (Missions, Active Runs, Assembled Articles, Quality Averages).
    - Interactive single-node step execution (`stepWorkflow`) and full autonomous pipeline runs (`runFullWorkflow`).
    - Sliding Graph Inspector drawer displaying live node states, execution latency, retry counts, and payload payloads.
    - Content Mission initialization modal configuring personas, risk tolerance levels, budget tiers, and target word counts.
  - **Sidebar Navigation Integration**:
    - Direct navigation access integrated across both User Workspace sidebar and Admin Control Center with SPA page transitions (`wire:navigate`) and active route highlighting.
  - **Robust Verification & Integrity Testing**:
    - 25 dedicated feature tests in `ContentIntelligenceTest.php` verifying authorization, state transitions, mission creation, node execution, feedback loops, and TipTap document compilation.
- **Client-Side Reading Progress Memory & Card Synchronization Engine (`hoaCardReadingProgress`)**:
  - Automatic persistent tracking of reading progress (`progress`, `completed`, `scrollY`, `updated_at`) using browser `localStorage` keyed by unique article slug (`hoa_read_progress_{slug}`).
  - **Dual-Gradient Progress Bar & Dynamic Status Badges**: Article cards across Grid View, List View, and Featured Hero dynamically reveal an animated gradient progress track (`0% → 100%`), live progress badge (`• 35% read` or `✓ 100% Read`), time-to-finish indicator, and thumbnail status badges.
  - **Upgraded Modern Glassmorphic Action Buttons**: Replaced generic text links with high-end, rounded-xl glassmorphic buttons with dynamic states:
    - *Unread*: "Read →" with subtle border glow and hover translation.
    - *In Progress*: "Resume (35%) →" with active indigo glow and direct jump option.
    - *Completed*: "Read Again ↺" with emerald glass styling and smooth 180° rotation on hover.
  - **Zero-Latency bfcache & Multi-Tab Synchronization**: Listens to `storage`, `pageshow`, and `focus` window events, ensuring instant updates when navigating back from an article without page reloads.
  - **Pick-Up Where You Left Off (Floating Resume Toast)**: On `/blog/{slug}`, if a reader previously scrolled past 350px without completing the article, a non-intrusive floating toast appears with a 1-click `Jump →` button that smoothly scrolls to the saved point.
- **Dynamic Knowledge Archive & Content Explorer Suite (`/blog` & `/blog/archive`)**:
  - Real-time debounced search across titles, excerpts, categories, and tags.
  - Interactive tag cloud with article frequency counts (`BlogPost::getPublishedTagsWithCounts()`).
  - Categories directory with live count metrics.
  - Chronological archive timeline (`BlogPost::getPublishedArchiveTimeline()`).
  - Quick read-time filters (< 5m quick vs 5m+ deep dive) and multi-criteria sorting.
  - Dynamic Active Filter Chips Bar with 1-click reset.
  - Grid View (`▦`) and List View (`☰`) presentation layout switcher.
- **Multi-Format Featured Image Upload Engine**:
  - Support for PNG, JPG, WebP, GIF, SVG, AVIF, BMP, ICO, and TIFF formats.
  - Real-time upload progress bar and instant image preview in Section 1 post editor tab.
  - SEO-friendly slug-based filename generation with timestamps.
  - Public storage fallback route (`/storage/{path}`) and NTFS junction handling for Windows & shared hosting compatibility.
- **Dual Editorial Publication & Revision Dates**:
  - Added display of `Published on {date}` and `Updated on {date}` on article views.
  - Atomic view counting (`DB::table()->increment('views_count')`) that preserves content modification timestamps.

## [2.7.6] - 2026-09-05

### Added
- **Interactive Self-Hosted Mermaid Vector Schemas & ER Diagram Pan/Zoom Suite:**
  - 100% self-hosted local Mermaid library bundled via Vite (0 external CDN dependencies).
  - Auto-detects `erDiagram`, `flowchart`, `sequenceDiagram`, `classDiagram`, `stateDiagram`, `gitGraph`, `pie`, `mindmap`, and vector diagrams in publication views.
  - **Drag-to-Move Pan**: Smooth mouse dragging (`cursor: grab` / `cursor: grabbing`) and mobile single-finger touch dragging across large schemas with zero latency.
  - **Precision Zoom Controls**: `➕` Zoom In (+25%), `➖` Zoom Out (-25%), live percentage indicator (`100%`, `125%`, etc.), double-click detail toggle, and 1-click `⟲ Fit` canvas reset.
  - **Focal-Point Mouse Wheel & Pinch Zoom**: Mouse wheel zooming tracking cursor focal point, and two-finger pinch-to-zoom on touch screens.
  - **Embedded Markdown Auto-Unpacker**: Automatically detects and unpacks giant raw Markdown code blocks containing embedded headings (`##`), dividers (`---`), ASCII architecture diagrams, and Mermaid diagrams into separate semantic DOM elements (`<h2>`, `<pre>`, `<hr>`), allowing dynamic TOC indexing.
  - **Floating Quick Dock & Source Drawers**: In-canvas floating quick action buttons, toggleable Mermaid source-code drawers, and 1-click schema clipboard copying.
- **Cyberpunk ASCII Architecture Flow Terminals:**
  - Box-drawing flowcharts (e.g. `┌─┐│└┘▼▲`) are automatically wrapped in a macOS terminal frame (`🔴 🟡 🟢`) with locked monospace font alignment and 1-click diagram copy.
- **Permission & Feature Matrix Table Enhancer:**
  - Tables comparing features/plans auto-highlight checkmarks (`✓` in glowing emerald), crossmarks (`✕` in muted slate), and status pills (`⚡ ...`) with responsive horizontal scrollers.
- **Publisher-Grade Blog Layout & Tabbed Discovery Rail:**
  - Sticky aside rail with Alpine.js 3-tab widget displaying Similar Articles, Articles by Author, and Trending Posts.
  - Dynamic Table of Contents (TOC) with mobile drawer and reading progress tracking.
- **Public Blog Post Publishing System & Reader Platform (`/blog`):**
  - Integrated public blog directory (`/blog`) with ambient lighting, search, category filter badges, pagination, and sticky spotlight hero cards.
  - Built article reader page (`/blog/{slug}`) with reading time estimates, table of contents, author profile cards, and related posts.
  - Added dashboard Blog Manager (`/dashboard/blog`) for authors to manage, draft, publish, and delete blog posts with Livewire 3 reactivity.
  - Added defensive database migration `2026_09_05_000001_create_blog_posts_table.php` with safe existence checks.
- **WordPress-Style Post Settings Tab & Publishing Suite (Section 1):**
  - Added dedicated **Post** tab positioned as Section 1 in the Content Intelligence sidebar with default active focus.
  - Featured Image manager with live thumbnail preview, custom URL input, presets, and 1-click removal.
  - WordPress-style Category checklist with radio selectors, primary badge, and inline "+ Add New Category" creator.
  - Interactive Tag chips with Enter key addition, removal buttons, and quick popular suggestions.
  - AI Excerpt generator and live slug/permalink editor with 1-click URL copying.
  - Status & Visibility controls (`published` / `draft`, sticky spotlight hero toggle).
  - Header toolbar blog publishing shortcut with synchronization to sidebar Section 1.
- **Codebase Snapshot Restore Points Bulk Selection Suite:**
  - Added multi-select checkbox controls to restore points table with reactive row highlighting (`bg-violet-600/15 border-l-2 border-violet-500`).
  - Added glassmorphic **Bulk Select** preset options dropdown (Select All, Select Auto Updates, Select Manual Snapshots, and Clear Selection).
  - Added floating / docked **Bulk Action Toolbar** with real-time selection counter and storage footprint calculation (`~X MB`).
  - Integrated **Bulk Download**: Packages multiple selected snapshot archives and SQL dumps into a single combined ZIP (`snapshots_bundle_YYYYMMDD_HHmmss.zip`).
  - Integrated **Bulk Delete**: Batch removes selected snapshot archives and SQL dumps with atomic manifest updates and immediate disk space recovery.
  - Added **Prune Old (Keep 3)** 1-click action to automatically retain the 3 most recent backups and purge older snapshots.
  - Added cumulative disk storage footprint badge to section header (`📦 X MB total`).

## [2.7.5] - 2026-09-05

### Fixed
- **OmniRoute Telemetry & Settings Alpine Scope Stability:**
  - Resolved `ReferenceError: activePoint is not defined` during periodic Livewire DOM morphing cycles by migrating telemetry cursor tracking and floating HUD expressions to `$data.activePoint`.
  - Resolved `ReferenceError: showKey is not defined` on `/dashboard/ai-models/omniroute` by declaring reactive key state on root components and evaluating `$data.showKey ?? false`.
  - Added deterministic `wire:key` DOM anchoring across the SVG canvas container, user API key forms, and password input containers.
- **Framework View Cache Integrity:**
  - Reinstalled and sanitized vendor framework blade views, ensuring 100% clean compilation for all exception handling, pagination, and mailer templates.

### Added
- **Multi-Format Document Export Engine:**
  - Added native TipTap JSON AST document export (`application/json`) with indented formatting.
  - Added direct MS Word `.docx` file export with HTML document envelopes.
  - Integrated zero-dialog browser print/PDF preview formatting (`window.print()`).
  - Added 1-click Rich Text & HTML clipboard copying with visual confirmation toasts.

## [2.7.4] - 2026-09-05

### Added
- **Floating Table Operations Toolbar (`hoa-table-floating-bar`):**
  - Added an intelligent, glassmorphic floating toolbar that docks directly above active tables whenever the cursor is inside any table cell.
  - Quick 1-click actions: Insert Row Above (↑), Insert Row Below (↓), Delete Row (✕), Insert Column Left (←), Insert Column Right (→), Delete Column (✕), Toggle Header Row (🔲), Merge/Split Selected Cells (🔗), and Delete Entire Table (🗑️).
  - Built-in canvas boundary protection and sticky positioning when scrolling long tables.
- **Context Menu Table Controls Section:**
  - Dynamic table grid controls in the custom right-click context menu, detecting when the user right-clicks inside any `table`, `th`, or `td` element.
- **Complete TipTap 3 Table Methods Suite:**
  - Expanded `TiptapDriver` with `toggleHeaderRow()`, `toggleHeaderColumn()`, `toggleHeaderCell()`, `mergeCells()`, `splitCell()`, `mergeOrSplit()`, `fixTables()`, `goToNextCell()`, and `goToPreviousCell()`.
  - Added custom CSS styles for multi-cell selections (`.selectedCell`) with glowing indigo border and high-contrast column resize handles (`.column-resize-handle`).
- **Real-Time Code Syntax Highlighting Suite (`CodeBlockLowlight`):**
  - Integrated `@tiptap/extension-code-block-lowlight` powered by `lowlight` (highlight.js v11) covering 35+ common languages (JavaScript, TypeScript, Python, PHP, HTML, CSS, SQL, JSON, Bash, Markdown, Go, Rust, Java, C++, C#, etc.).
  - Added custom `CustomCodeBlockLowlight` NodeView featuring:
    - Sleek macOS terminal header with colored dots (`● ● ●`).
    - Interactive language selector dropdown with live syntax switching.
    - 1-Click "Copy Code" button with instant visual `"Copied! ✓"` feedback.
    - In-block <kbd>Tab</kbd> indentation (`enableTabIndentation: true`) and effortless exit navigation.
  - High-fidelity dark IDE theme CSS tokens (`hljs-keyword`, `hljs-string`, `hljs-title`, `hljs-number`, `hljs-comment`, `hljs-built_in`, `hljs-function`).

## [2.7.3] - 2026-09-04

### Added
- **Dynamic AI Reasoning & Writing Intelligence Suite:**
  - Upgraded surgical paragraph transformation engine across all 7 right-click writing intelligence actions:
    - *Recreate Paragraph* (`recreate`): Completely re-architects sentence structures and syntax from scratch using domain authority and high-engagement rhetoric.
    - *Rewrite & Polish* (`rewrite`): Substantive qualitative enhancement eliminating passive voice, weak verbs, and wordy filler phrases.
    - *Expand with Depth* (`expand`): Adds deep analytical rigor, tactical implications, and concrete real-world rationale (1.5x–2.2x depth).
    - *Shorten & Condense* (`shorten`): Distills selected text into its punchy essence in 40%–60% of original word count.
    - *Simplify (8th-Grade)* (`simplify`): Replaces polysyllabic academic abstractions with crisp, effortless plain English (Hemingway style).
    - *Generate FAQ Block* (`generate_faq`): Formulates 2–3 high-intent search questions with authoritative answers and bold key entities.
    - *SEO Optimize Text* (`seo_optimize`): Naturally front-loads primary entities and weaves focus keywords for Google AI Overviews and GEO readiness.
- **Deep Full-Document Narrative Comprehension:**
  - `ContentWriterBrain::buildSurgicalPrompt` now synthesizes the entire document thesis, working title, primary focus keyword, narrative placement role (Opening hook, Core body analysis, Concluding synthesis), and surrounding inflow/outflow context.
  - Automatically derives preceding and following text from the document even if the client cursor collapsed.
- **Zero-Token Local Algorithmic Writing Engine (Offline / Quota Fallback):**
  - Added pure-PHP and client-side JavaScript deterministic transformation algorithms (`executeLocalActionTransform` and `applyLocalParagraphAction`) for all 7 actions.
  - Activates seamlessly if external AI models are unavailable, network is disconnected, or monthly word quotas are exhausted.
- **Strict Anti-Echo Guarantee & Calibrated Temperatures:**
  - Added explicit anti-echo mandates in both system and user prompts to forbid returning unchanged text.
  - Dynamic temperature tuning per action (`recreate`: 0.82, `rewrite`: 0.78, `expand`: 0.75, `shorten`: 0.55, `simplify`: 0.60, `generate_faq`: 0.70, `seo_optimize`: 0.65).
  - Controller safety net automatically intercepts and transforms any identical text echoes before completing stream.
- **Interactive In-Canvas Sub-Agent Proposal Card:**
  - Locked ProseMirror selection range on right-click to eliminate selection loss.
  - Dynamic sub-agent proposal inspector showing exact active mode badge, token velocity, and model badge.

## [2.7.2] - 2026-09-04

### Added
- **Google AI Overviews & Generative Engine Optimization (GEO) Pillar:**
  - Added dedicated 7th SEO pillar evaluating direct definition snippets (40-60 words under H2), verifiable data point density, comparison table presence, and People Also Ask (PAA) query coverage.
  - Integrated in-canvas visual callouts (`#seo-loc-geo_direct_answer`, `#seo-loc-geo_structured_synthesis`) and canvas locator.
- **1-Click Magic SEO & GEO Auto-Healer:**
  - Master action in SEO tab dynamically aggregating all detected failing checks into a unified editorial directive, performing a single-pass holistic optimization without disjointed piecemeal rewriting.
- **Dynamic Schema.org JSON-LD Studio & Validator:**
  - Added `SchemaGenerator` service generating verified `BlogPosting`, `FAQPage`, and `HowTo` structured data.
  - Interactive Schema Studio in SEO tab with live syntax-highlighted JSON-LD, 1-click clipboard copy, and direct document injection.
- **Semantic NLP Entity Density Matrix:**
  - Real-time SurferSEO/Clearscope-style topical entity extraction with bi-gram and tri-gram analysis.
  - Interactive density chips with color-coded usage status (Underused, Optimal, Overused).
- **Multi-Platform SERP & Social Simulator:**
  - Interactive live preview in Titles & Meta tab supporting Google Desktop, Google Mobile, Google AI Overviews (Gemini), X/Twitter Summary Cards, and LinkedIn/Facebook OpenGraph cards.

- **Comprehensive Content Intelligence Sidebar Overhaul:**
  - **10-Point E-E-A-T Quality Audit:** Empirical evaluation across all 10 true E-E-A-T & GEO dimensions (search intent, topic coverage, original value, readability, SEO structure, internal linking, outbound citations, E-E-A-T signals, GEO readiness, technical SEO) with diagnostic progress bars, granular feedback, and 1-Click Master E-E-A-T Auto-Healer.
  - **Titles & Meta Descriptions:** Added real-time character counters (50-65 chars title, 120-160 chars meta), full meta description persistence in `SeoAnalysis::metrics['meta_description']` and seamless integration with `SeoAnalyzer`.
  - **AI Content Ideas & Gaps:** Added 1-click `⚡ AI Draft Section` to automatically draft missing gap sections into the editor canvas, plus structured comparison table generator.
  - **Keywords Density Matrix:** Real-time primary keyword density percentage and occurrence tracker with dynamic color tiers (0.8%–2.5% optimal), plus real-time secondary keyword usage tracking.
  - **Outline & Versions:** Added instant `↻ Sync` on tab activation and hardened diff inspection across all editor drivers.

- **Dual-Engine Architecture (With AI & Local Algorithmic Fallback):**
  - All Content Intelligence features (Viral Titles, Meta Descriptions, LSI Semantic Keywords, FAQ Generation, Content Gaps, Quick Answers, and Auto-Healing) now operate on a dual-engine architecture:
    - **With AI**: Leverages OmniRoute LLM streaming, deep semantic reasoning, and dynamic context synthesis.
    - **Without AI / Local Algorithmic Engine**: Executes deterministic statistical NLP, n-gram extraction, linguistic formulaic headline models, and DOM AST structural healers.
    - If the AI model is unavailable, offline, or monthly word quota is reached, features automatically and seamlessly execute native local algorithms with zero tokens and zero downtime.

### Fixed
- **SEO Heatmap Toggle & Editor Canvas Read-Only State:**
  - Resolved `Uncaught TypeError: ed.setEditable is not a function` by adding `setEditable` and `isEditable` methods to `TiptapDriver`.
  - Added unified `toggleSeoHeatmap(forceState)` method in Alpine editor state to safely stash live drafts, toggle read-only inspection mode, and restore editable content cleanly.

---

## [2.7.1] - 2026-09-04

### Added
- **In-Canvas SEO Recommendation & Color-Coded Heatmap System:**
  - Implemented real-time in-canvas visual callouts and annotations directly inside the editor canvas for missing SEO requirements.
  - Added 4-tier visual color system: 🔴 Critical Issues (Missing intro focus keyword, keyword stuffing, run-on sentences), 🟡 Warnings & Structure (Missing subheading keyword, bulky paragraphs, missing outbound citations), 🔵 Authority & E-E-A-T (Citations, clinical/research references, trust terms), 🟢 Focus Keyword Optimization.
  - Added floating in-canvas inspection legend bar and interactive `🎯 Locate in Content` buttons in the SEO tab to smoothly scroll and highlight targeted lines.
  - Enriched 6-pillar SEO checks with actionable recommendation boxes, current vs. goal metrics, and severity indicators.

---

## [2.7.0] - 2026-09-04

### Added
- **Multi-Agent Pipeline Coordinator:** Backend orchestrator mapping 15 frontend pipeline checkboxes to true multi-LLM generation cycles.
- **Dynamic SSE Status Frames:** SSE stream multiplexes AI tokens with status_message streams for real-time UI updates.
- **RAG Vector Injection:** Agentic loop invokes RetrieveRagContext to synthesize brand entities during generation.

### Changed
- Refactored Amber Swarm Button to bypass legacy JS fetch loops in favor of backend SSE pipeline.
- Updated ContentWriterBrain to interpret multi-phase pipeline streams as native article generators.

### Fixed
- SSE parsing logic in TipTap no longer drops ghost tokens.
- Resolved Livewire 500 errors from stale view caches and unclosed Blade directives.
- LLM JSON markdown format extraction no longer causes Architect failures.

## [2.6.7] - 2026-09-03

### 🐛 Fixed
- **Localhost Hybrid Routing & Telemetry Stability Fix**:
  - Fixed regression in `AiStreamController::streamTransform` and `preparePrompt` where `$hasSelection` was undefined, causing HTTP 500 (`Server error while generating transformation.`).
  - Fixed telemetry polling loop in `AdminOmniRouteSetupPage::pingGatewayHealth` where server-side sockets attempted to connect to the cloud server's local loopback (`127.0.0.1:20128`) instead of recognizing client-side daemons on remote installations (`studio.helpofai.com`).
  - Enhanced client browser bridge in `omniroute.blade.php` with periodic 8-second client health pulsing across `http://127.0.0.1:20128/v1/models` and `http://localhost:20128/v1/models`, preventing the gateway connection status from flipping between `LIVE` and `STANDBY / OFFLINE`.

---

## [2.6.6] - 2026-09-03

### 🐛 Fixed
- **Multi-Agent Swarm Double Article Bug in Editor**:
  - Resolved duplicate article synthesis triggered by the Multi-Agent Swarm button (`full-content-main-agent`).
  - Fixed regression where requests without active selection defaulted to full 15-stage pipeline article prompts regardless of action type.
  - Implemented `ContentWriterBrain::isFullArticleType()` across `TransformText`, `AiStreamController::streamTransform`, and `AiStreamController::preparePrompt` to correctly separate full-document generation from surgical components (`comparison_table`, `seo_fix_title`, `seo_fix_meta`, `quick_answer`, etc.).
  - Updated Swarm Step 4 (`rich_media`) to inspect existing document tables prior to requesting secondary tables, preventing redundant table insertion.
  - Added robust `isContentEmpty()` helper recognizing whitespace and all default placeholder variations (`Start writing your AI-powered content...`, `Start building your block content...`), ensuring `multi_agent_swarm` cleanly overwrites placeholder text without concatenation.
  - Injected missing `transformRoute` parameter into `MainEditor.blade.php` component initialization.

---

## [2.6.5] - 2026-09-02

### 🚀 Added & Improved
- **WordPress Plugin Integration Rewrite (HOA Studio AI Bridge)**:
  - Advanced Enterprise Glassmorphic Dashboard matching the Laravel backend UI using `bg-slate-950` design tokens.
  - Multi-page system with nested sidebar menus (`Dashboard`, `Connection`, `AI Settings`, `Editor Control`).
  - Added new `hoa_studio_brand_voice` global prompt injection settings natively configurable in WordPress.
  - Refactored `hoa-studio-wordpress.php` from a 340-line monolithic script into a clean, 4-module object-oriented architecture (`/includes/admin`, `/includes/api`, `/includes/gutenberg`).

### 🛡️ Cleaned & Hardened
- **AI Gateway & SSE Streaming Latency Fix**:
  - Eliminated delayed token chunking on `AiStreamController` and WP Plugin `ajax_stream_proxy` with strict `@ob_implicit_flush(true)` execution flow, restoring sub-50ms Time-To-First-Token capability over Nginx/Apache.
- **Automated Memory Protection**:
  - Implemented `php artisan hoa:prune-telemetry` to batch-delete decaying telemetry and security graph data older than 30 days without locking database tables.

---

## [2.6.4] - 2026-08-31

### 🚀 Added & Improved
- **High-Converting Welcome Page & Real Editor Simulation Suite**:
  - Rebuilt landing page with crystal-clear copy, SEO headlines, dynamic system version badge, and structured JSON-LD `FAQPage` schema.
  - Upgraded 3-column interactive studio simulation matching full Master Editor capabilities (8-engine switcher, OmniRoute AI live streaming, floating AI prompt bar, and 7-tab Content Intelligence suite).
  - Created standalone [`resources/css/welcome.css`](file:///C:/Users/rajib/Desktop/HOA-Studio/resources/css/welcome.css) with rich 3D ambient glassmorphic shadows (`.hoa-editor-shadow`), glowing hover physics (`.hoa-card-glow-shadow`), and mobile drawer scrollbars.
  - Implemented responsive right-edge slide-over mobile drawer with body teleportation and touch backdrop overlay.

---

## [2.6.3] - 2026-08-30

### 🚀 Added & Improved
- **Fast SPA Page Transitions & Request Storm Elimination**:
  - Replaced speculative `wire:navigate.hover` prefetching across user and admin sidebars with clean `wire:navigate`, preventing request queue starvation and server bottlenecks during navigation.
- **Glassmorphic Skeleton & Deferred Loading Architecture**:
  - Implemented Livewire 3 deferred loading (`wire:init="loadDashboard"`) with zero-shift glowing glass skeletons across User Dashboard and Admin Overview.
  - Hardened `<x-omniroute.telemetry-graph>` with defensive array initialization and seamless fallback telemetry streams.

---

## [2.6.2] - 2026-08-30

### 🚀 Added & Improved
- **Direct GitHub Repository & Version Sync Engine**:
  - Direct live inspection of `version.json`, build number, latest commit SHA, commit author, and release date from GitHub `main` branch with automated cache-busting (`?t=` timestamp) and zero rate-limit raw streaming.
  - Dedicated **GitHub Live Sync** matrix in Admin Updates control panel showing real-time repository connection status, target branch, and latest commit ID.
- **Explicit Connection & Diagnostics Handling**:
  - Replaced silent fallbacks with transparent connection diagnostic messages when remote hosts or DNS/cURL queries are blocked on shared hosting.

### 🛡️ Cleaned & Hardened
- **Clean Feature Decoupling & Modular Hygiene**:
  - Fully removed experimental image editing modules, obsolete stylesheets, unused schema migrations, and unneeded dependencies (`fabric`) to keep application bundle lean and production-focused.
  - Rebuilt production assets with Vite with zero warnings or dangling references.

---

## [2.6.1] - 2026-08-29

### 🚀 Added & Improved
- **Localized AI Sub-Agent Paragraph Recreation Engine (`sub-content-sub-agent`)**:
  - Contextual right-click & floating menu actions for rewriting, polishing, and recreating individual paragraphs.
  - Automatic paragraph detection at cursor position when no manual range is selected.
  - Capped token usage (150–450 tokens) and isolated synthesis pipeline preventing full document regeneration on localized edits.
- **Enhanced Glassmorphic High-Contrast Visual Feedback**:
  - High-contrast translucent amber highlight (`rgba(234, 179, 8, 0.22)`) with drop shadow for selected paragraphs, ensuring crisp text legibility in dark themes.
  - Glowing emerald green confirmation badge (`.ai-replaced-green-highlight`) with dynamic 5-second auto-fade transition and automated markup sanitization upon acceptance.
- **ProseMirror Atomic Transaction Replacements**:
  - Upgraded TipTap driver to execute `.setTextSelection({ from, to }).deleteSelection().insertContent()` with multi-tiered HTML normalization fallbacks.
  - Preserved canvas `scrollTop` across content updates via `requestAnimationFrame`.

### 🛡️ Fixed & Hardened
- **Livewire 3 Single-Root & DOM Isolation**:
  - Enforced single root element across Blade views and wrapped editor canvas and scripts under `wire:ignore` to prevent state resets.
  - Paused background autosaves during active AI proposal reviews to avoid premature re-renders or scroll jumps.
- **Floating Bubble & Context Menu Dismissal**:
  - Upgraded TipTap bubble selection toolbar to fixed viewport coordinates with boundary protection.
  - Isolated right-click context menu by explicit ID (`hoa-editor-context-menu`) and added global click/escape listeners for instant auto-dismissal.
  - Fixed variable scope `ReferenceError` in AI transform stream handler and restored `Placeholder` extension in TipTap driver.

---

## [2.6.0] - 2026-08-26

### 🚀 Added
- **HOA Studio Enterprise WordPress Plugin & Bridge API Suite (`/api/v1/wordpress/*`)**:
  - **Standalone WordPress Plugin (`public/plugins/hoa-studio-wordpress/` & `public/downloads/hoa-studio-wordpress.zip`)**:
    - **TipTap Fullscreen AI Editor Canvas**: Brings HOA Studio's complete TipTap ProseMirror engine, floating selection formatting toolbar, and inline AI prompt bar (`Ctrl+K` / `/`) directly into WordPress Post & Page edit screens.
    - **Gutenberg Custom AI Block Extension (`hoa-studio/ai-content-generator`)**: Injects live-streaming AI generation blocks natively into Gutenberg.
    - **Bidirectional Document & Post Sync (`/api/v1/wordpress/sync-document`)**: Sync articles between HOA Studio and WordPress drafts.
  - **Scoped User Studio Connect Keys (`hoa_live_...`)**:
    - Each user generates their own unique `SHA-256` connect tokens in User Settings &rarr; WordPress Connect Keys.
    - Authenticated via `AuthenticateStudioToken` middleware with automatic monthly word quota deduction and rate limiting (never exposes backend OmniRoute credentials).
- **Multi-Server & Sub-Directory Domain Support (`helpofai.com/studio`)**:
  - Full compatibility across **Linux**, **Apache**, **Nginx**, **LiteSpeed / OpenLiteSpeed**, and **cPanel Shared Hosting**.
  - Dynamic relative `.htaccess` routing for both root domains and subfolder paths (e.g. `public_html/studio/`).
  - Automatic `forceRootUrl` and `forceScheme` HTTPS enforcement in `AppServiceProvider`.
  - Configurable `ASSET_URL` & `update_route` support in `config/livewire.php` preventing Livewire SPA asset breakage in subdirectories.
  - LiteSpeed and Apache SSE AI streaming buffer bypass (`CacheLookup off` & `proxy_buffering off`).
- **Unified User Settings & Account Controls Suite (`/dashboard/settings`)**:
  - Built a 5-tab responsive glassmorphic settings dashboard:
    1. **Profile & Security**: Live display name, official workspace email editing, and password update.
    2. **AI Tokens & Word Quota**: Real-time word balance meter, total tokens processed, direct provider cost calculations, and per-model consumption breakdown.
    3. **My Content & Documents**: Paginated document portfolio, word count metrics, project associations, search & status filters, and instant trash/edit actions.
    4. **BYOK Custom API Keys**: Register personal OpenAI, DeepSeek, Anthropic, or local Ollama endpoints encrypted at rest with **AES-256-GCM**, unlocking unlimited rate limits.
    5. **Studio Preferences**: Configurable default AI generation models, vector embedding RAG cache duration, default editor canvas engine (TipTap, Gutenberg, Notion, Markdown), and notification options.
- **Sidebar & Top Header Navigation Polish**:
  - Added dedicated **Settings & Controls** sidebar navigation link and integrated the avatar pill across all workspace layouts with dynamic tab routing (`wire:navigate`).

---

## [2.5.2] - 2026-08-26

### 🚀 Added & Architectural Improvements
- **Thin Master Scripts Engine Refactor (`resources/views/editor/partial/`)**:
  - Refactored monolithic `scripts.blade.php` into a thin master orchestrator delegating to 5 feature-oriented sub-script blades:
    - `scripts-core.blade.php`: Lifecycle, model fetching, autosave engine, and draft disaster recovery.
    - `scripts-canvas.blade.php`: ProseMirror / TipTap editor instance, formatting status, context menus, and outline navigation.
    - `scripts-ai.blade.php`: Server-Sent Events (SSE) AI streaming, multi-agent swarm, and targeted SEO fixes.
    - `scripts-diff.blade.php`: Visual LCS diff review, candidate variations, ghost completion, and metrics calculation.
    - `scripts-telemetry.blade.php`: Floating bubble dragging, system logging, and memory buffer.

### 🛠️ Fixed & Polished
- **AI Content Direct Persistence & Anti-Cut Protection**:
  - Eliminated unwanted modal/diff review popups during full-document generation by adding strict active DOM selection validation.
  - Streamed AI content now always commits directly to the editor canvas with immediate autosave and disaster draft persistence.
- **Panel Visibility Persistence**:
  - User manual toggle choices for AI Command Center (`showLeftPanel`) and Content Intelligence (`showRightPanel`) are now persisted in browser `localStorage` and will not reset during AI transforms or Livewire renders.
- **Removal of Legacy Telemetry Modal**:
  - Cleaned up all remaining references to the floating telemetry terminal modal (`terminal-ui.blade.php` and `showTerminalModal`).

---

## [2.5.1] - 2026-08-25

### 🚀 Added
- **Turbo Hover Prefetching Engine (`wire:navigate.hover`)**:
  - Upgraded all navigation links across Workspace and Admin sidebars to instantly pre-fetch destination pages on 60ms cursor hover, eliminating perceived navigation delay.
- **Glassmorphic Multi-Variant Skeleton Loader Suite (`<x-glass.skeleton />`)**:
  - Built high-performance pulsating skeleton components for document cards, user lists, metrics, and data tables to eliminate Cumulative Layout Shift (CLS).
- **Animated Conic Gradient Brand Logo (`<x-glass.logo />`)**:
  - Created standalone CSS conic rotating border with dark glassmorphic inner box and shimmering typography (`HOA Studio` / `HOA Admin`).
- **Hardware-Accelerated Cursor Glow Spotlight**:
  - Implemented 60 FPS LERP trailing physics spotlight cursor across all dashboard viewports.

### 🛠️ Changed & Polished
- **Fixed-Coordinate Sidebar Icon Tracks**:
  - Re-architected sidebar navigation geometry with fixed `w-8 h-8` icon containers and unified `px-3 py-2.5` padding, completely eliminating icon wobble during rapid expand/collapse actions.
- **Top Navbar Header Collapse Trigger**:
  - Moved desktop sidebar toggle button outside sidebar container into the top navigation header (Linear / VS Code pattern) for unobstructed brand logo visibility.
- **Automated `.env` 3-Way Auto-Merge Engine**:
  - Upgraded `CoreUpdateService` with safe `.env.example` key synchronization that preserves user database secrets and API tokens.

---

## [2.5.0] - 2026-08-24

### 🚀 Added
- **Advanced Core Update & Self-Healing Rollback Engine**:
  - Dual-Engine Update Architecture supporting **Native Git** (VPS/CLI) and **Pure-PHP Zip Archive** (Shared Hosting / cPanel).
  - Pre-flight automated snapshots: creates immutable point-in-time codebase `.zip` backups and database `.sql` dumps in `storage/app/updates/backups/`.
  - Automated post-update synthetic diagnostics via `HealthProberService` (testing database tables, user model connectivity, write permissions, and Vite manifest).
  - Instant self-healing auto-rollback triggered automatically if any post-update health check fails or throws an exception.
- **Admin Time-Machine Rollback Control Center (`/admin/updates`)**:
  - Live version checker with GitHub release notes, 1-Click Update, manual snapshot creation, and 1-Click Rollback to any historical restore point.
- **Offline Disaster Recovery Script (`public/hoa-rescue.php`)**:
  - Standalone, zero-dependency recovery tool protected by `RESCUE_SECRET` enabling admins to force sites online, flush corrupted caches, or unpack restore points directly via native PHP.

---

## [2.4.0] - 2026-08-24

### 🚀 Added
- **Multi-Candidate AI Generation (Choice Variations)**:
  - Added support for generating, previewing, and toggling across variation candidates (`[ #1 ● ] [ #2 ] [ #3 ]`).
  - Added dynamic variation regeneration with persona style presets (*Professional, Casual, Persuasive, Academic*).
- **Granular Git-Style Word-by-Word Diffing**:
  - Built a 2D dynamic programming Longest Common Subsequence (LCS) diffing engine (`computeWordDiff`).
  - Highlights exact word-level deletions (`<del>` in rose) and additions (`<ins>` in emerald).
  - Added a **Split View (`◫ Split`)** vs **Unified View (`≡ Unified`)** switcher into the visual diff inspector.
- **Interactive Transform Modifiers & Sliders**:
  - Collapsible drawer controls for **Creativity Intensity** (`0.3` to `1.0`), **Tone Personas**, and **Length Targets** (*Shorter, Same, Longer*).
- **Live Before-vs-After SEO & Readability Delta Telemetry**:
  - Real-time 4-column metric preview bar measuring Word Count Delta, Flesch-Kincaid Readability Delta, Focus Keyword Frequency Delta, and Copywriting Power Verbs.
- **In-Canvas Inline Ghost Auto-Completion Mode**:
  - Copilot/Cursor-style typing pause debounce (`1.2s`) predicting the next 25–35 words from preceding document context.
  - Floating inline ghost container with `Tab` to accept and `Esc` to dismiss.
- **Snapshot Version Diff & Instant Time-Machine**:
  - Upgraded Version History (Tab 7) with side-by-side snapshot comparison (`🔍 Diff`) against the live editor canvas.
  - 1-click snapshot restoration rollback via Livewire.
- **Draggable Floating Selection Toolbar (`.editor-floating-actions`)**:
  - Added a dedicated drag handle indicator (`⋮⋮`) with mouse and touch coordinate tracking to position the floating toolbar anywhere on screen.

### 🛡️ Fixed & Hardened
- **Safe JSON Response Extraction**:
  - Hardened all AI transform and SEO audit fix requests with text-first decoding (`await resp.text()`) and safe `JSON.parse()` wrappers to prevent `Unexpected token '!'` HTML error crashes.
- **Route Fallbacks**:
  - Injected `transformRoute: '{{ route('ai.transform') }}'` into main editor initialization with automatic `/dashboard/api/ai/transform` fallback.
- **DOM Hierarchy Integrity**:
  - Fixed root canvas container markup and Blade comment tags in `canvas.blade.php`.

---

## [2.3.0] - 2026-08-22

### 🚀 Added
- **Multi-Driver Document Canvas**:
  - Support for switching active drivers between **TipTap ProseMirror**, **Notion Block Canvas**, **Gutenberg Block Canvas**, **Markdown Split Screen**, and **Raw HTML**.
- **OmniRoute Gateway v3.8.50 Live Telemetry**:
  - Added `ai-telemetry.log` floating terminal modal with level filtering (`AI`, `SEO`, `ERROR`, `SYSTEM`).
  - Live token streaming speedometer displaying real-time `tok/s` and received token counts.
- **Multi-Agent Copywriting Swarm**:
  - 5-step automated publishing pipeline: Researcher, Outliner Architect, Section Draftsman, Rich Media Engineer, and Rank Math Optimizer.

### 🛡️ Fixed
- Restructured `scripts.blade.php` to debounce autosaves (2000ms) and prevent duplicate write locks during active SSE streaming.

---

## [2.2.0] - 2026-08-20

### 🚀 Added
- **Rank Math 4-Pillar SEO Analyzer**:
  - Real-time scoring (0–100) across **Basic SEO**, **Additional SEO**, **Title Readability**, and **Content Readability**.
  - Integrated 1-click **⚡ AI Section Fix** buttons for each SEO audit check.
- **Google SERP Snippet Preview**:
  - Live desktop vs. mobile Google search result snippet simulator.
- **Local Draft Auto-Recovery**:
  - Ambient banner recovering unsaved browser edits from `localStorage` in case of accidental tab closures.

---

## [2.1.0] - 2026-08-18

### 🚀 Added
- **Vector Knowledge Base & RAG Pipeline**:
  - Multi-source ingestion for text, files, and web URLs.
  - Recursive 500-token chunker with 50-token semantic overlap.
  - SHA-256 cached vector embeddings with configurable TTL (**1 Day**, **7 Days**, **30 Days**).
  - Cosine similarity ranking engine injecting grounded passages into AI prompts.
- **Brand Voice Profiler**:
  - Custom brand voice personas with tone descriptors, audience targeting, and automated prompt constraints injection.

---

## [2.0.0] - 2026-08-15

### 🚀 Added
- **Full Architecture Rewrite**:
  - Upgraded to **Laravel 12.x**, **PHP 8.5.0**, **Livewire 3.x**, and **Tailwind CSS 4.0**.
  - Vite 8.x client bundler with sub-2s production asset compiles.
- **Cryptographic BYOK Security**:
  - AES-256-GCM encrypted user API keys for OpenAI, DeepSeek, Anthropic, and Groq.
  - Unlimited rate limits for BYOK endpoints vs. tiered plan throttling for shared admin gateway.
- **Multi-Format Binary Exporter**:
  - 1-click export to **Markdown (`.md`)**, **HTML (`.html`)**, **Plain Text (`.txt`)**, and **Word (`.docx`)** via WordprocessingML.
  - Print-ready PDF styling with `@media print`.
- **Password-Gated Public Sharing (`/share/{token}`)**:
  - AES-256 password gate, permission flags (`allow_download`, `allow_copy`), and expiration timers.

---

## [1.0.0] - 2026-08-01

### 🚀 Initial Release
- Initial core release of HelpOfAi Studio.
- Basic document management, project folders, user authentication, and initial TipTap WYSIWYG editor implementation.