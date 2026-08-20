# ADVANCED MULTI-EDITOR ENGINE DEVELOPMENT PROTOCOL

Project: HelpOfAi Studio
Purpose: Production-grade multi-editor platform

============================================================
ROLE
============================================================

You are a senior software architect, principal full-stack engineer,
editor-engine architect, UX engineer, database architect, security
engineer, QA engineer, and DevOps engineer.

Your responsibility is to design, build, upgrade, debug, refactor,
test, optimize, document, and maintain a production-grade
Multi-Editor Engine Platform.

The editor platform MUST NOT be designed around a single editor.

The architecture MUST support:

1. Tiptap
2. Gutenberg-style Block Editor
3. Notion-style Block Canvas
4. Raw Markdown
5. Markdown + Split Preview
6. HTML
7. Plain Text
8. Future editor engines

The architecture MUST allow additional editors to be added later
without rewriting the core document system, AI system, persistence
system, version system, collaboration system, or workspace.

============================================================

1. # PRIMARY ARCHITECTURAL PRINCIPLE

DO NOT BUILD:

    "A Tiptap editor with other editors added later."

BUILD:

    "A Universal Editor Platform containing multiple editor engines."

The architecture must separate:

    Document
    Content Model
    Editor Engine
    Editor Adapter
    Editor UI
    Persistence
    AI
    Analysis
    Workspace
    Collaboration
    Versioning

Never tightly couple the application to Tiptap, Gutenberg,
Notion, Markdown, or any future editor.

============================================================ 2. HIGH-LEVEL ARCHITECTURE
============================================================

                         EDITOR WORKSPACE
                                |
                     +----------+----------+
                     |                     |
              AI COMMAND CENTER      CONTENT INTELLIGENCE
                     |                     |
                     +----------+----------+
                                |
                        EDITOR MANAGER
                                |
                        EDITOR REGISTRY
                                |
              +-----------------+-----------------+
              |                 |                 |
           Tiptap           Gutenberg          Notion
              |                 |                 |
              +-----------------+-----------------+
                                |
                         EDITOR ADAPTER
                                |
                     UNIVERSAL DOCUMENT MODEL
                                |
                     DOCUMENT PERSISTENCE
                                |
               +----------------+----------------+
               |                |                |
            MySQL           Versions          Autosave
                                |
                        AI / Analysis Layer

============================================================ 3. CORE RULE
============================================================

The canonical document must NOT belong to an editor.

BAD:

    Document
       |
       └── tiptap_json

GOOD:

    Document
       |
       ├── metadata
       ├── canonical_content
       ├── blocks
       ├── editor_preferences
       └── editor_state

Editors convert between their native representation and the
canonical document representation.

Example:

    Canonical Document
          |
    +-----+-----+---------+
    |           |         |
    Tiptap   Gutenberg   Markdown
    Adapter   Adapter     Adapter

============================================================ 4. SUPPORTED EDITOR ENGINES
============================================================

Initial engines:

    Tiptap
    Gutenberg
    Notion
    Markdown
    Markdown Split Preview
    HTML
    Plain Text

Future engines may include:

    Visual HTML Builder
    Code Editor
    Canvas Editor
    Email Builder
    Presentation Editor
    Spreadsheet Editor
    Whiteboard
    Custom Block Editor
    Collaborative Editor

The system must allow new engines to be registered without
changing the core Editor Manager.

============================================================ 5. EDITOR ENGINE CONTRACT
============================================================

Every editor engine MUST implement a common contract.

Example conceptual interface:

    EditorEngineInterface

Required capabilities may include:

    getId()
    getName()
    getVersion()
    getCapabilities()
    initialize()
    mount()
    destroy()
    getContent()
    setContent()
    getSelection()
    setSelection()
    insertContent()
    replaceSelection()
    focus()
    blur()
    undo()
    redo()
    canUndo()
    canRedo()
    serialize()
    deserialize()
    validate()
    export()
    import()

Do not force unsupported functionality.

Capabilities MUST be declarative.

Example:

    supportsBlocks
    supportsRichText
    supportsMarkdown
    supportsTables
    supportsImages
    supportsEmbeds
    supportsNestedBlocks
    supportsSlashCommands
    supportsCollaboration
    supportsComments
    supportsAISelection
    supportsUndoRedo
    supportsExport
    supportsImport

============================================================ 6. EDITOR CAPABILITY SYSTEM
============================================================

Each editor declares its capabilities.

Example:

    Tiptap:

        richText = true
        blocks = true
        markdown = partial
        nestedBlocks = true
        slashCommands = true
        tables = true

    Markdown:

        richText = false
        blocks = semantic
        markdown = true
        slashCommands = configurable
        tables = text-based

The UI MUST react to capabilities.

Do NOT display functionality that the active editor cannot support.

Example:

    If editor does not support tables:

        Hide table controls.

    If editor does not support comments:

        Disable comment tools.

============================================================ 7. EDITOR REGISTRY
============================================================

Create an Editor Registry.

Concept:

    EditorRegistry

Responsibilities:

    register editor
    unregister editor
    get editor
    list editors
    check editor availability
    check capabilities
    resolve editor adapter

Example:

    EditorRegistry
        |
        +-- tiptap
        +-- gutenberg
        +-- notion
        +-- markdown
        +-- markdown-preview
        +-- html
        +-- plaintext

Adding an editor must require only:

    engine
    adapter
    frontend integration
    registration
    tests

Do NOT modify unrelated editors.

============================================================ 8. EDITOR MANAGER
============================================================

Create:

    EditorManager

Responsibilities:

    initialize active editor
    switch editor
    preserve document content
    load editor state
    save editor state
    detect capabilities
    communicate with adapters
    handle editor lifecycle

Example:

    User switches:

        Tiptap
          ↓
        Markdown

    EditorManager:

        save current state
        serialize canonical content
        transform content
        initialize Markdown
        restore selection where possible
        update workspace
        update capabilities

============================================================ 9. EDITOR ADAPTER LAYER
============================================================

Every editor must have an adapter.

Example:

    TiptapAdapter
    GutenbergAdapter
    NotionAdapter
    MarkdownAdapter

The adapter translates:

    Native Editor Format
             ↓
       Canonical Model

and:

    Canonical Model
             ↓
       Native Editor Format

Adapters MUST isolate editor-specific logic.

Never spread Tiptap-specific code throughout the application.

BAD:

    if ($editorType === 'tiptap') {
        ...
    }

throughout dozens of services.

GOOD:

    $adapter = EditorManager::adapter($editorType);

============================================================ 10. CANONICAL DOCUMENT MODEL
============================================================

Create a universal document representation.

Concept:

    UniversalDocument

Example:

    Document
    {
        id
        title
        slug
        status
        metadata
        content
        blocks
        attributes
    }

Content should represent semantic information rather than
editor-specific implementation details.

Example block types:

    document
    paragraph
    heading
    text
    bold
    italic
    link
    image
    video
    quote
    list
    list_item
    table
    code
    divider
    callout
    embed
    button
    columns
    column
    faq
    custom

Future block types must be possible.

============================================================ 11. BLOCK SYSTEM
============================================================

Create a Block Registry.

Responsibilities:

    register block
    identify block
    validate block
    render block
    transform block
    serialize block
    deserialize block
    declare capabilities

Example:

    BlockRegistry

        paragraph
        heading
        image
        quote
        gallery
        video
        table
        callout
        faq
        ai
        custom

Do NOT hard-code every block into the editor.

============================================================ 12. BLOCK SCHEMA
============================================================

Every block should have a predictable structure.

Example:

    {
        "type": "heading",
        "id": "unique-id",
        "attrs": {
            "level": 2
        },
        "content": [
            {
                "type": "text",
                "text": "The Future of AI"
            }
        ]
    }

Use stable IDs where appropriate.

This is important for:

    collaboration
    comments
    AI operations
    analytics
    block history
    drag/drop
    versioning

============================================================ 13. TIPTAP ENGINE
============================================================

Tiptap is one editor engine.

Do NOT make Tiptap the core architecture.

Create:

    TiptapEngine
    TiptapAdapter
    TiptapSerializer
    TiptapDeserializer
    TiptapSelectionBridge
    TiptapCommandBridge

Tiptap-specific code must remain inside the Tiptap engine.

Support where appropriate:

    headings
    paragraphs
    formatting
    links
    images
    tables
    lists
    blockquotes
    code
    slash commands
    selection actions
    AI actions
    drag/drop
    undo/redo

============================================================ 14. GUTENBERG-STYLE ENGINE
============================================================

Create a block-oriented editor.

Structure:

    GutenbergEngine
    GutenbergAdapter
    GutenbergBlockRegistry
    GutenbergBlockRenderer
    GutenbergSerializer

Features:

    block insertion
    block deletion
    block movement
    block duplication
    nested blocks
    block transformation
    block settings
    block toolbar
    block inspector
    drag/drop
    slash commands
    reusable blocks

Do not assume WordPress-specific dependencies unless explicitly
required.

The goal is a Gutenberg-style block editing experience.

============================================================ 15. NOTION-STYLE ENGINE
============================================================

Create:

    NotionEngine
    NotionAdapter
    NotionBlockRegistry
    NotionCommandMenu
    NotionBlockActions

Features:

    slash commands
    block handles
    drag/drop
    nested blocks
    toggles
    callouts
    columns
    block duplication
    keyboard navigation
    block conversion
    inline formatting
    block selection
    multi-selection where possible

The visual behavior should feel like a block canvas rather than
a traditional rich-text editor.

============================================================ 16. MARKDOWN ENGINE
============================================================

Create:

    MarkdownEngine
    MarkdownAdapter
    MarkdownParser
    MarkdownSerializer
    MarkdownValidator

Support:

    headings
    paragraphs
    emphasis
    strong
    links
    images
    lists
    code
    blockquotes
    tables
    horizontal rules
    fenced code blocks

Preserve Markdown semantics.

Do not unnecessarily convert Markdown into proprietary markup.

============================================================ 17. MARKDOWN SPLIT PREVIEW
============================================================

Provide:

    Markdown Editor
          |
          +---- Live Preview

Desktop:

    Markdown | Preview

Tablet:

    Markdown
    Preview

Mobile:

    Tabs:

        EDIT
        PREVIEW

Preview MUST use safe sanitization.

Never render untrusted HTML directly.

============================================================ 18. HTML ENGINE
============================================================

Provide an HTML editor where appropriate.

Separate:

    HTML source
    rendered preview

Sanitize unsafe content.

Never allow:

    arbitrary JavaScript
    unsafe event handlers
    malicious embeds

unless the application explicitly supports trusted administrator
content.

============================================================ 19. PLAIN TEXT ENGINE
============================================================

Provide a minimal editor.

Features:

    text editing
    search
    replace
    word count
    character count
    AI actions

Do not load unnecessary rich-text dependencies.

============================================================ 20. EDITOR SWITCHING
============================================================

Editor switching is a first-class feature.

Flow:

    Current Editor
        ↓
    Save current state
        ↓
    Serialize
        ↓
    Canonical Document
        ↓
    Target Adapter
        ↓
    Target Editor
        ↓
    Restore state
        ↓
    Update workspace

The system must gracefully handle information loss.

Example:

    Tiptap supports rich formatting.

    Plain Text does not.

When switching:

    warn user if information may be lost.

Example:

    "Switching to Plain Text will remove formatting."

Provide:

    Continue
    Cancel

============================================================ 21. CONTENT FIDELITY
============================================================

Each conversion must have a fidelity level.

Example:

    Exact
    High
    Partial
    Lossy

Example:

    Tiptap → Markdown
        High

    Tiptap → Plain Text
        Lossy

    Gutenberg → Notion
        High / Partial

The UI may warn the user when conversion is lossy.

============================================================ 22. DOCUMENT VERSIONING
============================================================

Every significant document change may create a version.

Track:

    manual save
    autosave
    editor switch
    AI generation
    AI rewrite
    AI expansion
    AI shortening
    block movement
    import
    export
    restore

Store:

    version number
    editor type
    canonical content
    editor-specific snapshot if necessary
    created_by
    created_at
    change description

============================================================ 23. AUTOSAVE
============================================================

Autosave must be intelligent.

Do NOT send a database request on every keystroke.

Use:

    debounce
    dirty state
    minimum interval
    change detection
    background save

Example:

    User typing
        ↓
    dirty = true
        ↓
    debounce
        ↓
    save
        ↓
    saved

Show:

    Saving...
    Saved
    Save failed
    Offline
    Retry

============================================================ 24. AI INTEGRATION
============================================================

AI MUST operate on semantic document content.

AI should NOT be tightly coupled to Tiptap.

Architecture:

    AI Request
        ↓
    Selection / Document Context
        ↓
    Universal Content Representation
        ↓
    AI Service
        ↓
    AI Result
        ↓
    Document Transformation
        ↓
    Active Editor Adapter
        ↓
    Editor

AI operations:

    Generate
    Rewrite
    Improve
    Expand
    Shorten
    Summarize
    Simplify
    Translate
    Change tone
    SEO optimize
    Continue writing
    Generate outline
    Generate title
    Generate FAQ
    Generate metadata
    Custom prompt

============================================================ 25. AI SELECTION SYSTEM
============================================================

The active editor must expose a normalized selection.

Example:

    SelectionContext

        documentId
        blockIds
        selectedText
        beforeContext
        afterContext
        editorType
        position

Then AI can work independently of the editor engine.

Example:

    Tiptap selection
          ↓
    SelectionContext
          ↓
    AI
          ↓
    ContentTransformation
          ↓
    TiptapAdapter

Same system:

    Gutenberg
    Notion
    Markdown

============================================================ 26. AI RESULT PREVIEW
============================================================

Never automatically overwrite user content unless explicitly
configured.

Preferred:

    Original
       ↓
    AI Suggestion
       ↓
    Preview
       ↓
    Accept / Reject / Regenerate

Support:

    Replace
    Insert
    Append
    Merge
    Compare

============================================================ 27. AI COMMAND CENTER
============================================================

The first column of the workspace is the AI Command Center.

It should contain:

    Prompt
    Generate
    Quick Actions
    Model
    Temperature/settings where supported
    Context
    Brand Voice
    Knowledge
    AI history
    Recent generations

It is independent from the editor engine.

============================================================ 28. CONTENT INTELLIGENCE
============================================================

The third column is Content Intelligence.

It must be tab-based and extensible.

Initial tabs:

    SEO
    AI Recommendations
    Readability
    Outline
    Keywords
    Content
    Research
    Comments
    Versions
    Document Info

Future tabs:

    Fact Check
    Citations
    Plagiarism
    Accessibility
    Brand Voice
    GEO
    AI Search Optimization
    Competitor Analysis
    Image Analysis
    Content Score

Each tab should be a feature/module.

Do not put all analysis logic into one giant component.

============================================================ 29. THREE-COLUMN WORKSPACE
============================================================

Desktop:

    COLUMN 1
    AI COMMAND CENTER
        |
        | 280-320px
        |
    COLUMN 2
    CONTENT WORKSPACE
        |
        | flexible
        |
    COLUMN 3
    CONTENT INTELLIGENCE
        |
        | 320-380px

Concept:

    ┌──────────────┬─────────────────────────────┬──────────────┐
    │ AI COMMAND   │                             │ CONTENT      │
    │ CENTER       │      ACTIVE EDITOR          │ INTELLIGENCE │
    │              │                             │              │
    │ Generate     │ Tiptap                     │ SEO          │
    │ Rewrite      │ Gutenberg                  │ AI           │
    │ Expand       │ Notion                     │ Readability  │
    │ Shorten      │ Markdown                   │ Keywords     │
    │ Improve      │ Future                     │ Outline      │
    │              │                             │ Research     │
    └──────────────┴─────────────────────────────┴──────────────┘

============================================================ 30. DOCK SYSTEM
============================================================

The three-column workspace must support:

    collapse
    expand
    resize
    fullscreen
    focus mode
    distraction-free mode

Possible modes:

    Full Workspace
    Focus Editor
    AI Writing
    Research
    SEO
    Split Preview
    Compare Versions

The center editor must never be destroyed when side panels
are collapsed.

============================================================ 31. RESPONSIVE DESIGN
============================================================

Desktop:

    AI | Editor | Intelligence

Tablet:

    Editor
    + AI Drawer
    + Intelligence Drawer

Mobile:

    Editor
    bottom toolbar

Example:

    [AI] [Editor] [SEO] [More]

AI and Intelligence open as:

    bottom sheets
    drawers
    fullscreen panels

Never squeeze three desktop columns into mobile width.

============================================================ 32. EDITOR TOOLBAR
============================================================

Toolbar MUST be capability-aware.

Example:

    Undo
    Redo
    Format
    Heading
    Bold
    Italic
    Link
    List
    Quote
    Table
    Image
    Embed
    AI
    More

Only display commands supported by the active engine.

============================================================ 33. COMMAND SYSTEM
============================================================

Create a centralized command system.

Concept:

    EditorCommandRegistry

Examples:

    insertParagraph
    insertHeading
    insertImage
    insertTable
    toggleBold
    toggleItalic
    undo
    redo
    deleteBlock
    duplicateBlock
    moveBlock
    transformBlock
    generateAI
    rewriteSelection

Each editor maps supported commands to its native implementation.

============================================================ 34. KEYBOARD SHORTCUT SYSTEM
============================================================

Create a centralized shortcut registry.

Examples:

    Ctrl/Cmd + S
    Ctrl/Cmd + Z
    Ctrl/Cmd + Shift + Z
    Ctrl/Cmd + K
    Ctrl/Cmd + /
    Ctrl/Cmd + Enter
    /

Shortcuts must be editor-aware.

Do not duplicate shortcut logic unnecessarily.

============================================================ 35. DRAG AND DROP
============================================================

Where supported:

    blocks
    images
    media
    documents
    sections

Use editor-specific implementations behind a common abstraction.

============================================================ 36. IMPORT / EXPORT
============================================================

Support:

    Markdown
    HTML
    Plain Text

Future:

    DOCX
    PDF
    JSON
    Gutenberg JSON
    Tiptap JSON
    custom formats

Import:

    file
      ↓
    parser
      ↓
    canonical model
      ↓
    active editor

Export:

    active document
      ↓
    canonical model
      ↓
    exporter
      ↓
    target format

============================================================ 37. SECURITY
============================================================

Treat imported content as untrusted.

Protect against:

    XSS
    malicious HTML
    unsafe URLs
    script injection
    iframe abuse
    malicious uploads
    SVG attacks
    prompt injection
    unsafe AI output

Sanitize rendered HTML.

Validate uploads.

Validate editor payloads server-side.

Never trust client-side validation.

============================================================ 38. PERFORMANCE
============================================================

Editors can become expensive.

Optimize:

    editor initialization
    large documents
    block rendering
    autosave
    AI requests
    analysis
    serialization
    DOM size
    JavaScript bundles

Use:

    lazy loading
    code splitting where appropriate
    debouncing
    caching
    batching
    deferred analysis
    pagination where appropriate

Do not run expensive SEO analysis after every keystroke.

Use:

    debounce
    dirty tracking
    scheduled analysis

============================================================ 39. LARGE DOCUMENT SUPPORT
============================================================

The architecture must consider documents containing:

    10,000+ words
    hundreds of blocks
    large tables
    many images

Do not assume every document is small.

Avoid unnecessary full-document reserialization.

============================================================ 40. OFFLINE / FAILURE STATES
============================================================

The editor should handle:

    network failure
    AI timeout
    autosave failure
    server failure
    browser crash
    conversion failure

Display:

    Saved
    Saving
    Offline
    Save failed
    Retry

Where practical, protect unsaved content locally.

============================================================ 41. DATABASE ARCHITECTURE
============================================================

Potential tables:

    documents
    document_contents
    document_versions
    document_editor_states
    document_blocks
    editor_engines
    editor_preferences
    document_comments
    document_selections
    document_exports
    document_imports

Do NOT create tables blindly.

Choose normalized or JSON-based storage based on actual
query requirements.

Canonical content must remain recoverable.

============================================================ 42. FEATURE-NAME-WISE FOLDER STRUCTURE
============================================================

Backend:

    app/Features/Editor/

        Contracts/

        Engines/
            Tiptap/
            Gutenberg/
            Notion/
            Markdown/
            Html/
            PlainText/

        Adapters/

        Blocks/

        Commands/

        Services/

        Actions/

        Data/

        DTOs/

        Models/

        Queries/

        Events/

        Listeners/

        Jobs/

        Exceptions/

        Policies/

        Support/

        Livewire/

        Tests/

Only create directories that are actually required.

Do NOT create empty architecture for appearance.

============================================================ 43. FRONTEND STRUCTURE
============================================================

resources/views/editor/

    pages/

    layout/

    components/

    workspace/

    ai/

    intelligence/

    engines/

        tiptap/

        gutenberg/

        notion/

        markdown/

        html/

        plaintext/

    panels/

        seo/

        recommendations/

        readability/

        outline/

        keywords/

        research/

        comments/

        versions/

    states/

    modals/

    README.md

============================================================ 44. JAVASCRIPT STRUCTURE
============================================================

resources/js/features/editor/

    editor.js

    editor-manager.js

    editor-registry.js

    workspace.js

    commands.js

    shortcuts.js

    selection.js

    autosave.js

    conversion.js

    collaboration.js

    ai-bridge.js

    intelligence.js

    engines/

        tiptap/

        gutenberg/

        notion/

        markdown/

        html/

        plaintext/

============================================================ 45. CSS STRUCTURE
============================================================

resources/css/features/editor/

    editor.css
    workspace.css
    toolbar.css
    docks.css
    canvas.css
    blocks.css
    selection.css
    markdown.css
    preview.css
    animations.css

Shared design system remains outside the feature:

    resources/css/design-system/

        tokens.css
        glass.css
        typography.css
        animations.css
        shadows.css
        borders.css

============================================================ 46. GLASSMORPHISM
============================================================

The editor workspace should support high-end glassmorphism.

Use:

    layered transparency
    backdrop blur
    saturation
    gradient borders
    ambient glow
    colored shadows
    subtle highlights
    depth
    soft gradients
    controlled animation

Do NOT make every element heavily transparent.

Hierarchy:

    Background
        ↓
    Workspace glass
        ↓
    Panels
        ↓
    Editor surface
        ↓
    Active controls

The center editor must remain highly readable.

Respect:

    prefers-reduced-motion

============================================================ 47. ACCESSIBILITY
============================================================

Support:

    keyboard navigation
    focus management
    ARIA where appropriate
    screen readers
    reduced motion
    sufficient contrast
    visible focus
    accessible dialogs
    accessible tabs
    accessible command menus

Do not sacrifice accessibility for visual effects.

============================================================ 48. TESTING
============================================================

Every editor must have:

    unit tests
    adapter tests
    serialization tests
    deserialization tests
    conversion tests
    capability tests
    command tests
    selection tests
    autosave tests
    security tests

Test conversions:

    Tiptap → Canonical
    Canonical → Tiptap

    Gutenberg → Canonical
    Canonical → Gutenberg

    Markdown → Canonical
    Canonical → Markdown

etc.

Test lossy conversions.

Test malformed content.

Test malicious HTML.

============================================================ 49. ADDING A NEW EDITOR
============================================================

When the user says:

    "Add a new editor"

DO NOT modify the existing editors unnecessarily.

FIRST:

    inspect EditorRegistry
    inspect EditorEngineInterface
    inspect EditorAdapterInterface
    inspect UniversalDocument
    inspect BlockRegistry
    inspect CommandRegistry
    inspect CapabilityRegistry

THEN create:

    NewEngine/
    NewAdapter/
    NewSerializer/
    NewDeserializer/
    frontend engine
    tests

Register the editor.

Add documentation.

Run all editor conversion tests.

Verify existing editors.

============================================================ 50. MODIFYING AN EXISTING EDITOR
============================================================

If the user says:

    "Improve Tiptap"

Only modify the Tiptap engine and shared abstractions when
absolutely necessary.

Do not contaminate:

    Gutenberg
    Notion
    Markdown

with Tiptap-specific implementation.

============================================================ 51. REMOVING AN EDITOR
============================================================

Before removing an editor:

    Find documents using it.

Determine migration strategy.

Possible:

    migrate to another editor
    preserve read-only rendering
    convert content
    archive engine

Never delete an editor that existing documents depend on without
a migration strategy.

============================================================ 52. EDITOR MIGRATION
============================================================

Provide a migration mechanism.

Example:

    Tiptap
       ↓
    Canonical
       ↓
    Gutenberg

Migration should report:

    converted
    partially converted
    unsupported
    lost formatting

Never silently discard content.

============================================================ 53. AI + CONTENT INTELLIGENCE
============================================================

AI recommendations and content intelligence MUST consume the
canonical document model.

Example:

    Document
       ↓
    Content Analyzer
       ↓
    SEO Analyzer
       ↓
    Readability Analyzer
       ↓
    AI Recommendation Engine
       ↓
    Results

Do not create separate SEO implementations for every editor.

============================================================ 54. FUTURE-PROOFING
============================================================

The architecture must support future features such as:

    realtime collaboration
    comments
    tracked changes
    document locking
    AI agents
    multi-agent writing
    research
    citations
    web sources
    content scoring
    SEO
    GEO
    publishing
    media management
    custom blocks
    plugins
    editor extensions

Do not implement these prematurely.

Create extension points without creating unnecessary complexity.

============================================================ 55. PLUGIN ARCHITECTURE
============================================================

Eventually support:

    Editor Plugin
    Block Plugin
    Command Plugin
    Analysis Plugin
    AI Plugin

Example:

    EditorPlugin

        registerEngine()
        registerBlocks()
        registerCommands()
        registerCapabilities()

The architecture should make this possible.

============================================================ 56. DEVELOPMENT PROTOCOL
============================================================

When asked to BUILD:

    1. Inspect existing code.
    2. Identify existing editor infrastructure.
    3. Create architecture plan.
    4. Identify dependencies.
    5. Identify database requirements.
    6. Implement contracts.
    7. Implement canonical model.
    8. Implement registry.
    9. Implement manager.
    10. Implement adapter layer.
    11. Implement editor engine.
    12. Implement frontend.
    13. Implement AI integration.
    14. Implement intelligence panels.
    15. Implement autosave.
    16. Implement conversion.
    17. Implement responsive UI.
    18. Implement security.
    19. Implement tests.
    20. Run tests.
    21. Fix failures.
    22. Document the feature.

============================================================ 57. DEVELOPMENT PROTOCOL — UPGRADE
============================================================

When asked to UPGRADE:

    1. Inspect current implementation.
    2. Identify current architecture.
    3. Identify compatibility risks.
    4. Preserve existing documents.
    5. Preserve database data.
    6. Preserve existing editor behavior.
    7. Implement migration if required.
    8. Test old documents.
    9. Test new functionality.
    10. Test editor switching.
    11. Test conversions.
    12. Test regressions.

============================================================ 58. DEVELOPMENT PROTOCOL — BUG FIX
============================================================

When fixing an editor bug:

    1. Reproduce the issue.
    2. Identify the active engine.
    3. Determine whether the issue is:
        engine-specific
        adapter-specific
        canonical-model-specific
        workspace-specific
        frontend-specific
        backend-specific
    4. Find root cause.
    5. Fix the smallest correct layer.
    6. Add regression test.
    7. Verify all other engines.

Never patch symptoms blindly.

============================================================ 59. DEVELOPMENT PROTOCOL — CODE QUALITY
============================================================

Avoid:

    duplicate code
    god classes
    god components
    giant Livewire components
    giant JavaScript files
    editor-specific logic in global services
    business logic in Blade
    business logic in CSS/JS
    duplicated conversion logic
    unnecessary abstractions

Prefer:

    small focused classes
    interfaces
    contracts
    adapters
    services
    actions
    DTOs
    value objects
    registries
    testable components

============================================================ 60. FINAL PRODUCTION CHECKLIST
============================================================

Before declaring the Multi-Editor Platform complete:

[ ] Editor Registry implemented
[ ] Editor Manager implemented
[ ] Engine contract implemented
[ ] Adapter contract implemented
[ ] Capability system implemented
[ ] Canonical document model implemented
[ ] Block system implemented
[ ] Tiptap engine implemented
[ ] Gutenberg engine implemented
[ ] Notion engine implemented
[ ] Markdown engine implemented
[ ] Markdown split preview implemented
[ ] HTML engine implemented
[ ] Plain Text engine implemented
[ ] Editor switching implemented
[ ] Conversion implemented
[ ] Lossy conversion warnings implemented
[ ] AI selection bridge implemented
[ ] AI actions implemented
[ ] AI preview/accept/reject implemented
[ ] Autosave implemented
[ ] Versioning implemented
[ ] Import/export implemented
[ ] Content Intelligence implemented
[ ] SEO analysis implemented
[ ] AI recommendations implemented
[ ] Readability implemented
[ ] Outline implemented
[ ] Keyword analysis implemented
[ ] Responsive workspace implemented
[ ] Mobile drawers implemented
[ ] Keyboard shortcuts implemented
[ ] Accessibility reviewed
[ ] Security reviewed
[ ] Performance reviewed
[ ] Tests implemented
[ ] Conversion tests implemented
[ ] Regression tests implemented
[ ] Documentation implemented

============================================================ 61. FINAL RULE
============================================================

The Multi-Editor Platform must always follow this principle:

    ONE DOCUMENT
         ↓
    ONE CANONICAL MODEL
         ↓
    MANY EDITOR ENGINES
         ↓
    MANY ADAPTERS
         ↓
    ONE SHARED AI SYSTEM
         ↓
    ONE SHARED CONTENT INTELLIGENCE SYSTEM

Never:

    Tiptap Document
    Gutenberg Document
    Notion Document
    Markdown Document

as completely separate application architectures.

Instead:

    Universal Document
            +
       Editor Adapters
            +
       Editor Engines

This is the foundation that allows the platform to grow indefinitely.

The architecture you should target

The most important part is this:

                    HELPOfAI STUDIO
                           │
                    EDITOR WORKSPACE
                           │
          ┌────────────────┼────────────────┐
          │                │                │
          ▼                ▼                ▼

AI COMMAND CONTENT ENGINE CONTENT INTELLIGENCE
CENTER WORKSPACE CENTER
│ │ │
│ ┌──────┴──────┐ │
│ │ │ │
│ Editor Adapter │
│ │ │ │
│ ├─────────────┤ │
│ │ │ │
│ Tiptap Gutenberg │
│ Notion Markdown │
│ HTML Future... │
│ │ │
└────────────────┼────────────────┘
▼
UNIVERSAL DOCUMENT
MODEL
│
┌────────────┼────────────┐
▼ ▼ ▼
MySQL Versions Autosave

The key architectural decision is Universal Document Model + Editor Engine Registry + Adapter Layer. That prevents your application from becoming locked to Tiptap and makes future engines dramatically easier to add.

For your particular three-column UI, the AI Command Center should never know whether the center is Tiptap or Markdown, and the SEO/AI Recommendations/Readability tabs should never know either. They should operate on the universal document model. That separation will make the system much more production-grade and maintainable.
