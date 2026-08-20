# AI Content Writer — Laravel 13 Complete Architecture & Development Plan

**WebSite Name: HelpOfAi Studio**
**Short Name: HOA-Studio**

**for mysql database use**
DB_DATABASE=hoa-studio-db;
DB_USERNAME=hoa-studio-db;
DB_PASSWORD=hoa-studio-db;

_modern SPA-like AI SaaS experience_

## Architecture

```text
USER -> Laravel 13 -> Blade/Livewire/Alpine -> MySQL
                         |
                         v
                  Laravel AI SDK
                         |
                         v
                     OmniRoute
                         |
                  Local / Free AI
```

## Frontend

Blade is the rendering foundation. Livewire provides SPA-like dynamic interactions without requiring a Node.js production runtime. Alpine handles lightweight browser interactions. Tailwind provides the design system.

Use reusable components such as `x-glass.card`, `x-glass.button`, `x-ai.model-selector`, `x-ai.generation-panel`, and editor/navigation components.

## AI

Keep the AI layer provider-independent. Use `AiGenerationService`, `AiModelRegistry`, `AiProviderResolver`, `AiUsageService`, and `AiPromptService`. Never expose provider credentials to the browser.

Generation flow:

```text
Input -> Validate -> Authorize -> Quota -> Prompt -> Context -> Model -> Laravel AI SDK -> OmniRoute -> Validate -> Sanitize -> Persist -> UI
```

Support blog/article generation, rewrite, expand, shorten, summarize, paraphrase, grammar, tone, headlines, meta descriptions, SEO, product descriptions, social posts, email, ads, outlines, FAQs, ideas, translation, custom prompts, brand voice, knowledge-grounded generation and regeneration.

## Streaming

Preferred flow:

```text
Browser -> Laravel -> Laravel AI SDK -> OmniRoute -> Model -> stream -> Laravel -> UI
```

For restrictive shared hosting, implement queue-based generation with status polling as a fallback.

## Database

Core tables:
`users`, `profiles`, `projects`, `project_members`, `documents`, `document_versions`, `document_contents`, `templates`, `template_categories`, `generations`, `generation_chunks`, `generation_usage`, `ai_models`, `ai_providers`, `brand_profiles`, `knowledge_sources`, `knowledge_chunks`, `embeddings`, `seo_analyses`, `seo_keywords`, `prompts`, `prompt_versions`, `favorites`, `tags`, `document_tags`, `notifications`, `audit_logs`, `api_tokens`, `settings`.

Documents should be versioned. Track manual saves, AI generation, rewrite, expand, shorten, SEO, restore and import operations.

## Editor

Use Tiptap or another mature ProseMirror-based editor. Support headings, formatting, links, lists, blockquotes, code, tables, images, alignment, slash commands, AI actions, word count and selection-based AI operations.

Selection menu: Rewrite, Improve, Shorten, Expand, Simplify, Professional, Friendly, SEO Optimize, Translate, Ask AI.

## Glassmorphism Design System

Do not use one generic transparent card everywhere. Create four levels: Subtle, Standard, Elevated and Premium.

Premium glass combines transparency, backdrop blur, saturation, thin borders, gradient highlights, inner highlights, soft shadows, optional colored glow, subtle noise and restrained motion.

Components:

```text
resources/views/components/glass/
  card.blade.php
  panel.blade.php
  button.blade.php
  input.blade.php
  modal.blade.php
  sidebar.blade.php
  navbar.blade.php
  dropdown.blade.php
  command.blade.php
  tooltip.blade.php
```

Use semantic gradient borders (cyan, blue, violet, purple, pink, emerald, amber) only for important states. Use colored shadows sparingly. Use ambient radial gradients and blurred background orbs. Respect `prefers-reduced-motion`.

## Main UI

```text
+----------------------------------------------------------+
| Logo      Projects   Templates   Knowledge    Profile    |
+----------------+---------------------------+-------------+
| PROJECT        |      DOCUMENT EDITOR      | AI          |
| Documents      |  Title                    | ASSISTANT   |
| Templates      |  Article content...       | Generate    |
| Knowledge      |                           | Rewrite     |
| Brand Voice    |                           | Expand      |
|                |                           | SEO         |
+----------------+---------------------------+-------------+
| Words | SEO | Saved | Model: Auto                        |
+----------------------------------------------------------+
```

Desktop can use three columns; mobile should use drawers/bottom sheets.

## Modules

Dashboard, Projects, Documents, Templates, Brand Voice, Knowledge Base, SEO, Generation History, Usage/Quotas, Notifications, Import/Export, Sharing and Admin.

## Security

Use CSRF, authentication, policies/gates, validation, mass-assignment protection, XSS protection, secure uploads, MIME/file-size limits, rate limiting, audit logs, HTTPS, secure cookies, security headers and least-privilege DB accounts. Sanitize AI-generated HTML. Treat imported knowledge as untrusted content and separate system instructions from retrieved content to reduce prompt-injection risk.

## API

Keep business logic API-ready with `/api/v1/`. Livewire and future API clients should call shared application services. This keeps a future React/Next.js migration possible without rewriting core logic.

## Folder Structure

# FEATURE-NAME-WISE PROJECT ARCHITECTURE PROTOCOL

You are an expert software architect and senior full-stack developer.

Your job is to build, modify, upgrade, refactor, debug, test, and maintain
the project using a STRICT FEATURE-NAME-WISE ARCHITECTURE.

The feature is the primary organizational unit.

Do NOT organize the project only by technical type such as:

    Controllers/
    Services/
    Views/
    JavaScript/
    CSS/

Instead, organize implementation around the feature.

============================================================

1. # CORE PRINCIPLE

EVERY major feature MUST have its own feature directory.

Example:

Dashboard
Authentication
Projects
Documents
Templates
AI Generation
SEO
Knowledge Base
Settings
Notifications
Admin
Billing
etc.

Each feature should contain everything primarily related to that feature.

The goal is:

    Easy to find
    Easy to understand
    Easy to modify
    Easy to upgrade
    Easy to test
    Easy to remove
    Easy to reuse
    Easy for AI agents to maintain
    Low coupling
    High cohesion

# ============================================================ 2. FEATURE-FIRST RULE

If the user says:

    "Build complete Dashboard"

DO NOT immediately start creating random files.

FIRST:

1. Inspect the existing project.
2. Search for existing Dashboard implementation.
3. Identify all Dashboard-related files.
4. Determine whether Dashboard already exists.
5. Determine which parts are complete.
6. Determine which parts are missing.
7. Determine dependencies.
8. Create a feature plan.
9. Then implement the feature.

If Dashboard does not exist, create:

resources/views/dashboard/

and organize everything Dashboard-specific inside it.

# ============================================================ 3. DASHBOARD EXAMPLE

Recommended structure:

resources/views/dashboard/

├── pages/
│ ├── index.blade.php
│ ├── overview.blade.php
│ └── analytics.blade.php
│
├── components/
│ ├── header.blade.php
│ ├── welcome.blade.php
│ ├── stats-card.blade.php
│ ├── quick-actions.blade.php
│ ├── recent-documents.blade.php
│ ├── activity-feed.blade.php
│ ├── usage-card.blade.php
│ └── ai-overview.blade.php
│
├── layout/
│ ├── dashboard-layout.blade.php
│ ├── dashboard-sidebar.blade.php
│ ├── dashboard-navbar.blade.php
│ ├── dashboard-mobile-nav.blade.php
│ └── dashboard-footer.blade.php
│
├── partials/
│ ├── filters.blade.php
│ ├── empty-state.blade.php
│ ├── loading.blade.php
│ └── error-state.blade.php
│
└── states/
├── loading.blade.php
├── empty.blade.php
├── error.blade.php
└── success.blade.php

Backend:

app/Features/Dashboard/

├── Actions/
│ ├── GetDashboardStats.php
│ ├── GetRecentDocuments.php
│ └── GetDashboardActivity.php
│
├── Data/
│ ├── DashboardStatsData.php
│ └── DashboardData.php
│
├── Services/
│ └── DashboardService.php
│
├── Queries/
│ ├── DashboardStatsQuery.php
│ └── DashboardActivityQuery.php
│
├── Policies/
│ └── DashboardPolicy.php
│
├── Livewire/
│ ├── DashboardPage.php
│ ├── DashboardStats.php
│ └── RecentDocuments.php
│
├── Support/
│ └── DashboardFormatter.php
│
└── Tests/
├── Feature/
└── Unit/

Frontend:

resources/js/features/dashboard/

├── dashboard.js
├── dashboard-state.js
├── dashboard-interactions.js
└── components/

resources/css/features/dashboard/

├── dashboard.css
└── dashboard-effects.css

# ============================================================ 4. COMPLETE FEATURE STRUCTURE

For a major feature, use this pattern:

feature-name/

├── pages/
├── components/
├── layout/
├── partials/
├── states/
├── modals/
├── forms/
├── tables/
├── widgets/
├── assets/
├── js/
├── css/
└── README.md

Backend equivalent:

app/Features/FeatureName/

├── Actions/
├── Data/
├── DTOs/
├── Services/
├── Queries/
├── Commands/
├── Jobs/
├── Events/
├── Listeners/
├── Policies/
├── Rules/
├── Exceptions/
├── Support/
├── Livewire/
└── Tests/

IMPORTANT:

Do NOT blindly create every directory.

Only create directories that are actually required.

Avoid empty folders.

Avoid unnecessary abstraction.

# ============================================================ 5. DATABASE FEATURE OWNERSHIP

Database code should also respect feature ownership.

If a feature owns specific database behavior, keep the related
implementation inside the feature.

Example:

app/Features/Documents/

├── Actions/
├── Services/
├── Queries/
├── Livewire/
├── Models/
│ ├── Document.php
│ └── DocumentVersion.php
├── Policies/
└── Tests/

Migrations remain in Laravel's migration system:

database/migrations/

but migration filenames MUST clearly identify the feature.

Example:

2026_xx_xx_create_documents_table.php

2026_xx_xx_create_document_versions_table.php

2026_xx_xx_add_ai_metadata_to_documents_table.php

# ============================================================ 6. MODELS

Prefer feature ownership for feature-specific models.

Example:

app/Features/Documents/Models/Document.php

app/Features/Documents/Models/DocumentVersion.php

app/Features/Projects/Models/Project.php

app/Features/AI/Models/AiGeneration.php

Shared/global models may remain:

app/Models/User.php

Do not move a model into a feature merely for the sake of
organization if it is genuinely shared across the entire application.

# ============================================================ 7. ROUTES

Routes should remain in Laravel's routing system.

Do NOT create hundreds of unrelated route files unless there is
a real architectural reason.

Use clear feature grouping.

Example:

routes/web.php

    Dashboard routes
    Project routes
    Document routes
    Template routes
    AI routes
    Settings routes

If the application becomes large, feature route files may be introduced:

app/Features/Dashboard/routes.php

app/Features/Documents/routes.php

etc.

But only do this when the project size justifies it.

# ============================================================ 8. LIVEWIRE

Livewire components MUST belong to their feature.

Example:

app/Features/Dashboard/Livewire/DashboardPage.php

app/Features/Documents/Livewire/DocumentEditor.php

app/Features/AI/Livewire/GenerationPanel.php

app/Features/SEO/Livewire/SeoAnalyzer.php

Do NOT put every Livewire component into one giant:

app/Livewire/

directory if the project uses feature-first architecture.

# ============================================================ 9. BLADE

Blade views MUST be feature-owned whenever they are feature-specific.

Correct:

resources/views/dashboard/

resources/views/documents/

resources/views/projects/

resources/views/ai/

resources/views/settings/

Avoid:

resources/views/all-dashboard-files/

or unrelated global Blade files.

# ============================================================ 10. JAVASCRIPT

Feature-specific JavaScript belongs to the feature.

Example:

resources/js/features/dashboard/

resources/js/features/documents/

resources/js/features/editor/

resources/js/features/ai/

resources/js/features/seo/

Global JavaScript belongs in:

resources/js/app.js

Do not put Dashboard JavaScript into a giant global app.js
unless it is genuinely global.

# ============================================================ 11. CSS

Feature-specific CSS belongs to the feature.

Example:

resources/css/features/dashboard/

resources/css/features/editor/

resources/css/features/ai/

Global design system:

resources/css/design-system/

├── tokens.css
├── glass.css
├── typography.css
├── animations.css
├── utilities.css
└── components.css

Global application stylesheet:

resources/css/app.css

Do not duplicate global CSS inside every feature.

# ============================================================ 12. SHARED COMPONENTS

If a component is used by multiple features, it MUST NOT be duplicated.

Move it to:

resources/views/components/

Example:

resources/views/components/

├── glass/
│ ├── card.blade.php
│ ├── button.blade.php
│ ├── modal.blade.php
│ ├── input.blade.php
│ └── panel.blade.php
│
├── navigation/
├── forms/
├── feedback/
└── data/

Feature-specific component:

resources/views/dashboard/components/stats-card.blade.php

Shared component:

resources/views/components/glass/card.blade.php

# ============================================================ 13. AI FEATURE EXAMPLE

If user says:

    "Build complete AI generation system"

FIRST inspect existing:

app/Features/AI/

resources/views/ai/

resources/js/features/ai/

resources/css/features/ai/

Then create only missing pieces.

Possible structure:

app/Features/AI/

├── Actions/
│ ├── GenerateContent.php
│ ├── RewriteContent.php
│ ├── ExpandContent.php
│ ├── ShortenContent.php
│ └── SummarizeContent.php
│
├── Contracts/
│ ├── AiProvider.php
│ └── AiGenerator.php
│
├── Data/
│ ├── GenerationRequestData.php
│ └── GenerationResultData.php
│
├── Services/
│ ├── AiGenerationService.php
│ ├── AiProviderResolver.php
│ ├── AiModelRegistry.php
│ └── AiUsageService.php
│
├── Prompts/
│ ├── ArticlePrompt.php
│ ├── RewritePrompt.php
│ └── SeoPrompt.php
│
├── Livewire/
│ ├── GenerationPanel.php
│ ├── ModelSelector.php
│ └── GenerationHistory.php
│
├── Models/
│ └── AiGeneration.php
│
├── Jobs/
│ └── GenerateContentJob.php
│
├── Exceptions/
│ └── AiGenerationException.php
│
└── Tests/

Frontend:

resources/views/ai/

├── pages/
├── components/
├── panels/
├── modals/
├── states/
└── partials/

resources/js/features/ai/

├── ai.js
├── generation.js
└── streaming.js

resources/css/features/ai/

├── ai.css
└── generation.css

# ============================================================ 14. FEATURE DEPENDENCIES

A feature may depend on another feature.

Example:

AI Generation
↓
Documents
↓
Projects
↓
Users

Do NOT create circular dependencies.

Bad:

Dashboard → AI → Dashboard

Good:

Dashboard
↓
Application Services

AI
↓
AI Services

Documents
↓
Document Services

Shared functionality should move into a neutral shared layer.

# ============================================================ 15. SHARED / CORE LAYER

Only genuinely cross-feature functionality belongs here.

Example:

app/Core/

├── Contracts/
├── Exceptions/
├── Support/
├── Security/
├── Http/
└── Infrastructure/

Do NOT put feature-specific business logic here.

If a function only belongs to Documents,
keep it in Documents.

If it only belongs to AI,
keep it in AI.

If it is genuinely shared,
place it in Core/Shared.

# ============================================================ 16. FEATURE README

Every major feature SHOULD have:

README.md

Example:

app/Features/Documents/README.md

The README should contain:

- Feature purpose
- Architecture
- Dependencies
- Main files
- Database tables
- Routes
- Livewire components
- Frontend components
- Events
- Jobs
- External integrations
- Security considerations
- Testing instructions
- Known limitations

# ============================================================ 17. WHEN ADDING A NEW FEATURE

When user says:

    "Build complete Authentication"

Follow this exact sequence:

STEP 1
Inspect the entire existing project.

STEP 2
Search for:

    authentication
    login
    register
    logout
    password
    session
    user
    email verification
    2FA
    OAuth
    middleware
    policies
    guards

STEP 3
Map existing implementation.

STEP 4
DO NOT overwrite working code.

STEP 5
Create:

app/Features/Auth/

resources/views/auth/

resources/js/features/auth/

resources/css/features/auth/

only when required.

STEP 6
Implement:

Frontend
↓
Validation
↓
Livewire/backend interaction
↓
Application logic
↓
Database
↓
Authorization
↓
Security
↓
Events/jobs/notifications
↓
Tests

STEP 7
Run static analysis/tests.

STEP 8
Fix discovered problems.

STEP 9
Verify the complete feature.

STEP 10
Report exactly what was created, modified and tested.

# ============================================================ 18. FEATURE CREATION ORDER

For any major feature, prefer this order:

1. Inspect existing system

2. Architecture/design

3. Database requirements

4. Backend domain/application logic

5. Validation and authorization

6. Frontend structure

7. Livewire integration

8. JavaScript interactions

9. CSS/design

10. Loading states

11. Empty states

12. Error states

13. Success states

14. Accessibility

15. Security

16. Performance

17. Tests

18. Documentation

19. Final verification

# ============================================================ 19. CREATE / UPGRADE / FIX PROTOCOL

The AI MUST identify the requested operation.

Possible operations:

BUILD
UPGRADE
FIX
REFACTOR
OPTIMIZE
MIGRATE
REMOVE
REPLACE
EXTEND

BUILD:

Create the feature from the existing project state.

UPGRADE:

Inspect current implementation first.

Never rebuild everything blindly.

Preserve:

- existing database data
- existing user data
- existing configuration
- existing environment variables
- existing integrations
- working functionality

FIX:

First reproduce or identify the problem.

Then determine:

- root cause
- affected feature
- affected files
- dependencies
- regression risks

Fix the root cause rather than hiding the symptom.

REFACTOR:

Do not change behavior unless explicitly required.

Improve:

- architecture
- readability
- maintainability
- duplication
- performance
- testability

# ============================================================ 20. FILE CREATION RULE

Before creating ANY file:

ASK:

    Does this file already exist?

If yes:

    inspect it.

If no:

    determine whether it is actually required.

Never create duplicate files such as:

    DashboardService2.php
    DashboardServiceNew.php
    DashboardServiceFinal.php
    DashboardServiceUpdated.php

Use one authoritative implementation.

# ============================================================ 21. FILE NAMING

Names MUST clearly communicate their purpose.

Good:

DashboardService.php
DashboardStats.php
DashboardPage.php
DocumentEditor.php
GenerateContent.php
GenerationPanel.php

Bad:

Helper.php
Manager.php
Common.php
Utils.php
Misc.php
Temp.php
NewService.php

Avoid vague filenames.

# ============================================================ 22. COMPONENT REUSE

Before creating a component:

SEARCH for an existing reusable component.

If one exists:

    reuse it.

If it is almost suitable:

    improve the shared component only if doing so
    will not break existing features.

If the component is genuinely feature-specific:

    keep it inside the feature.

# ============================================================ 23. FRONTEND/BACKEND CONTRACT

Frontend and backend must have clearly defined contracts.

Example:

UI
↓
Livewire Action
↓
Application Action
↓
Domain/Service
↓
Repository/Query
↓
Database

Do not put large amounts of business logic inside:

Blade
Alpine
JavaScript
Livewire render methods

Keep business logic in backend application/domain services.

# ============================================================ 24. PRODUCTION-GRADE REQUIREMENTS

Every feature must consider:

Security
Validation
Authorization
Authentication
CSRF
XSS
SQL injection
Mass assignment
Rate limiting
File upload security
Error handling
Logging
Auditability
Performance
Caching
Database indexes
Transactions
Concurrency
Accessibility
Responsive design
Browser compatibility
Failure states
Recovery
Testing

# ============================================================ 25. UI REQUIREMENTS

Every major UI feature should support:

Loading state
Empty state
Error state
Success state
Disabled state
Permission denied state
Mobile layout
Tablet layout
Desktop layout
Keyboard navigation
Accessible labels
Focus states
Reduced motion

For the HelpOfAi Studio design system:

Use:

Glassmorphism
Layered glass
Colored borders
Gradient borders
Colored shadows
Ambient gradients
Soft glow
Micro-interactions
Hover effects
Loading animations
Background animation
Subtle noise
Depth
Blur
Saturation

BUT:

Do not overuse effects.

Performance and readability always have priority.

# ============================================================ 26. RESPONSIVE ARCHITECTURE

Do not design desktop-only interfaces.

Every feature must be evaluated at:

Mobile
Tablet
Desktop
Large desktop

Example:

Desktop:

Sidebar + Main + AI Panel

Tablet:

Collapsible sidebar + Main

Mobile:

Main + drawers/bottom sheets

# ============================================================ 27. TESTING

Every feature must have appropriate tests.

Backend:

Unit tests
Feature tests
Authorization tests
Validation tests
Database tests

Frontend:

Component behavior
Interaction tests
Responsive checks
Accessibility checks

Critical workflows should have browser/E2E tests.

# ============================================================ 28. FINAL VERIFICATION

Before declaring a feature complete:

CHECK:

[ ] Existing implementation inspected
[ ] No duplicate implementation
[ ] Feature directory created correctly
[ ] Frontend implemented
[ ] Backend implemented
[ ] Database implemented
[ ] Validation implemented
[ ] Authorization implemented
[ ] Error handling implemented
[ ] Loading states implemented
[ ] Empty states implemented
[ ] Mobile UI implemented
[ ] Accessibility reviewed
[ ] Security reviewed
[ ] Performance reviewed
[ ] Tests written
[ ] Tests passed
[ ] Existing features still work
[ ] Documentation updated

# ============================================================ 29. CHANGE REPORT

After completing work, provide:

FEATURE:
Dashboard

OPERATION:
BUILD

CREATED:

    app/Features/Dashboard/...
    resources/views/dashboard/...
    resources/js/features/dashboard/...
    resources/css/features/dashboard/...

MODIFIED:

    ...

DATABASE:

    ...

ROUTES:

    ...

TESTS:

    ...

SECURITY:

    ...

PERFORMANCE:

    ...

KNOWN LIMITATIONS:

    ...

NEXT RECOMMENDED STEP:

    ...

# ============================================================ 30. GOLDEN RULE

NEVER treat the project as a collection of random files.

Treat it as:

    APPLICATION
        ↓
    FEATURES
        ↓
    FEATURE MODULES
        ↓
    COMPONENTS
        ↓
    SERVICES / ACTIONS
        ↓
    DATA / DATABASE
        ↓
    TESTS

The feature is the primary unit of development.

When the user asks for a feature,
the AI must be able to locate, understand,
build, upgrade, test and maintain that feature
without searching through unrelated parts of the application.

The final architecture must remain:

    FEATURE-FIRST
    MODULAR
    REUSABLE
    MAINTAINABLE
    TESTABLE
    SECURE
    PERFORMANT
    PRODUCTION-GRADE
    SHARED-HOSTING-COMPATIBLE
    AI-MAINTAINABLE

````

## Shared Hosting

Target PHP 8.3+, MySQL 8+/compatible MariaDB, Apache, HTTPS, Composer and Cron. Avoid requiring Node.js production runtime, Docker, Kubernetes, nginx-specific configuration, permanent WebSocket servers or long-running Node processes. Compile assets before deployment.

Preserve `.env`, database, uploads, private files, storage and runtime-generated data during updates. Never commit `.env`; use `.env.example`.

## OmniRoute

Prefer a separate VPS/AI server for OmniRoute:

```text
Shared Hosting --HTTPS--> OmniRoute Server --> Local/Free/Remote AI
````

Protect OmniRoute with authentication, HTTPS, firewall/network restrictions and rate limiting. Do not expose it publicly without controls.

## Queues and Scheduling

Jobs: `GenerateContentJob`, `AnalyzeSeoJob`, `CreateEmbeddingJob`, `ProcessKnowledgeSourceJob`, `GenerateSummaryJob`, `CleanupOldVersionsJob`. Use cron-driven workers if shared hosting does not support persistent workers.

## Testing

Use Pest/PHPUnit and Playwright. Test authentication, authorization, documents, autosave, version restore, AI success/failure/timeout/quota errors, SEO, mobile UI, command palette and accessibility. Mock AI responses for deterministic tests.

## Performance

Optimize Livewire payloads, database queries, N+1 queries, editor initialization, CSS, JS, images, fonts and AI requests. Use lazy loading, deferred components, pagination, caching, debouncing and batching. Avoid huge animated DOM trees.

## Development Phases

1. Foundation
2. Database/authentication
3. Design tokens and glass UI library
4. Dashboard/projects/documents
5. Tiptap editor/autosave/versioning
6. Laravel AI SDK + OmniRoute
7. AI generation and editor actions
8. Templates/brand voice
9. SEO
10. Knowledge/RAG
11. Usage/quotas
12. Import/export/sharing
13. Admin
14. Security/performance/accessibility
15. Testing/backup/recovery
16. Production deployment

## Final Decision

The preferred stack for the current requirements is **Laravel 13 + Blade + Livewire 4 + Alpine.js + Tailwind CSS 4 + Blade-native shadcn-inspired components + Tiptap + MySQL 8+ + Laravel AI SDK + OmniRoute**.

This provides modern SPA-like UX, reusable dynamic components, high-end glassmorphism, advanced AI workflows, provider independence, mobile responsiveness, strong security and shared-hosting compatibility.
