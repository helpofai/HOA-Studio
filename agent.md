# 47. PRODUCTION-GRADE ENGINEERING STANDARD

Production-grade is a mandatory requirement.

The AI agent must never consider a feature complete simply because
the code executes successfully.

Every implementation must be evaluated for:

- Correctness
- Security
- Reliability
- Maintainability
- Scalability
- Performance
- Accessibility
- Observability
- Error recovery
- Data integrity
- Backward compatibility
- Testability
- Deployment safety

The target is:

DEVELOPMENT QUALITY
↓
PRODUCTION QUALITY
↓
PRODUCTION-READY

Never stop at "it works".

---

# 48. PRODUCTION-GRADE DEFINITION

A production-grade feature must satisfy:

FUNCTIONAL

- SECURE
- RELIABLE
- TESTED
- PERFORMANT
- MAINTAINABLE
- ACCESSIBLE
- OBSERVABLE
- RECOVERABLE
- DEPLOYABLE

If one of these areas has a serious unresolved problem,
the feature must NOT be marked production-ready.

---

# 49. PRODUCTION ARCHITECTURE REVIEW

Before completing a major feature, inspect:

Architecture
Database
Backend
Frontend
Security
Performance
Queues
Caching
Logging
Error handling
Testing
Deployment
Backup/recovery
Configuration
Dependencies

Look for:

- architectural coupling
- duplicate logic
- circular dependencies
- hidden dependencies
- unnecessary complexity
- unsafe assumptions
- single points of failure
- data-loss risks

---

# 50. PRODUCTION DATABASE STANDARD

Every database change must consider:

- indexes
- foreign keys
- unique constraints
- nullability
- defaults
- cascading behavior
- query performance
- data volume
- migration safety
- rollback strategy

Never perform destructive migrations casually.

For production-sensitive changes use:

EXPAND
↓
MIGRATE
↓
VERIFY
↓
SWITCH
↓
CLEANUP

Avoid:

DROP
↓
RECREATE

unless explicitly approved.

---

# 51. DATA INTEGRITY

Never allow operations to leave partially corrupted state.

For multi-step operations use:

- database transactions
- idempotency
- validation
- rollback
- retry-safe jobs

Example:

Create Document
↓
Create Content
↓
Create Version
↓
Create Generation Record

If a critical step fails:

ROLLBACK

when transaction semantics are appropriate.

---

# 52. IDEMPOTENCY

Any operation that can be retried must be designed safely.

Especially:

- queues
- AI generation
- payments if added later
- imports
- exports
- webhooks
- scheduled tasks
- notifications

Do not assume:

"this job runs only once."

---

# 53. QUEUE RELIABILITY

Production jobs must consider:

- retries
- backoff
- timeout
- failure handling
- duplicate execution
- partial failure
- logging
- failed jobs
- recovery

Example:

GenerateContentJob

must not create duplicate generation records simply because
the queue retries the job.

---

# 54. AI PRODUCTION RELIABILITY

AI operations must handle:

- provider unavailable
- OmniRoute unavailable
- model unavailable
- timeout
- rate limit
- malformed response
- empty response
- token/context limits
- quota exceeded
- connection failure
- provider switching

Architecture:

Request
↓
Validate
↓
Authorize
↓
Quota
↓
Prompt
↓
Context
↓
Provider
↓
Model
↓
Generation
↓
Validate Response
↓
Sanitize
↓
Persist
↓
Return

Never assume an AI request succeeds.

---

# 55. AI FALLBACK

Where supported:

Primary Model
↓ failure
Fallback Model
↓ failure
Friendly Error

Fallback behavior must be configurable.

Never silently switch models if doing so could materially change
the user's requested result without appropriate handling.

---

# 56. AI COST / RESOURCE PROTECTION

Even when using free/local providers, protect the application against:

- infinite generation
- repeated requests
- accidental loops
- oversized prompts
- excessive context
- abusive users
- duplicate generation

Implement appropriate:

- quotas
- rate limits
- request limits
- maximum input size
- timeout
- generation limits

---

# 57. SECURITY ENGINEERING STANDARD

Security is part of implementation, not a final optional step.

For every feature ask:

WHO can access this?

WHAT can they access?

WHAT can they modify?

WHAT can they delete?

WHAT can they upload?

WHAT can they execute?

WHAT data can they see?

WHAT happens if they manipulate the request?

---

# 58. TRUST BOUNDARY

Treat all external/user-controlled data as untrusted.

Including:

- HTTP requests
- query parameters
- form data
- Livewire properties
- uploaded files
- imported documents
- URLs
- AI output
- RAG documents
- browser storage
- cookies
- external API responses

Validate at trust boundaries.

---

# 59. AUTHORIZATION STANDARD

Every sensitive operation must perform server-side authorization.

Example:

User
↓
Authenticated?
↓
Authorized?
↓
Owns resource?
↓
Allowed operation?
↓
Execute

Never rely on hiding a button.

---

# 60. FILE UPLOAD SECURITY

For upload features:

Validate:

- MIME type
- extension
- file size
- filename
- storage path
- ownership

Never trust the original filename.

Never execute uploaded files.

Store private files outside publicly accessible paths when appropriate.

---

# 61. API SECURITY

For APIs:

- authentication
- authorization
- validation
- rate limiting
- pagination
- consistent errors
- versioning
- request limits

API version:

/api/v1/

Business logic must remain reusable between:

Livewire
API
CLI
Jobs

where appropriate.

---

# 62. SECRETS MANAGEMENT

Never hardcode:

- API keys
- passwords
- tokens
- encryption keys
- database passwords
- OmniRoute credentials

Use environment configuration.

Never commit:

.env

Use:

.env.example

with safe placeholders.

---

# 63. LOGGING STANDARD

Production logs should provide enough information to diagnose failures
without exposing sensitive information.

Log:

- operation
- feature
- user context where appropriate
- request correlation ID
- failure type
- provider
- model
- duration
- job ID

Never log:

- passwords
- API keys
- authentication tokens
- sensitive user content unnecessarily

---

# 64. OBSERVABILITY

For important operations track:

- success
- failure
- duration
- retry count
- queue state
- AI provider
- model
- usage
- quota

The system should make failures diagnosable.

---

# 65. ERROR TAXONOMY

Do not use generic errors everywhere.

Differentiate:

ValidationError
AuthorizationError
NotFoundError
ConflictError
RateLimitError
ProviderError
TimeoutError
QuotaExceededError
ProcessingError
StorageError

Users should receive appropriate messages.

Developers should receive useful diagnostic information.

---

# 66. USER EXPERIENCE FOR ERRORS

Every important operation should support:

Loading
Success
Failure
Retry
Empty
Disabled
Unauthorized

Example:

AI generation:

Generating...
↓
Success

or:

Generating...
↓
Provider unavailable
↓
Retry

Never leave the interface permanently stuck in:

"Loading..."

---

# 67. PERFORMANCE STANDARD

Production performance must consider:

Backend:

- database indexes
- N+1 queries
- query count
- pagination
- caching
- eager loading
- transactions

Frontend:

- bundle size
- DOM size
- Livewire payloads
- lazy loading
- deferred loading
- image optimization
- animation performance

AI:

- prompt size
- context size
- duplicate requests
- caching where appropriate
- streaming/queue strategy

---

# 68. CACHING

Use caching only when there is a measurable benefit.

Potential candidates:

- configuration
- expensive calculations
- AI model registry
- template lists
- settings
- permissions
- frequently accessed metadata

Cache invalidation must be considered.

Never cache sensitive user-specific data incorrectly.

---

# 69. CONCURRENCY

Consider multiple requests happening simultaneously.

Examples:

Two browser tabs editing the same document.

Two autosave requests.

Two AI generation requests.

Two queue jobs.

Use appropriate:

- database constraints
- transactions
- locks
- version numbers
- optimistic concurrency
- idempotency

where required.

---

# 70. AUTOSAVE SAFETY

For editors:

Do not blindly overwrite newer content.

Consider:

version
timestamp
document revision
client revision

When conflict occurs:

detect
↓
notify
↓
resolve safely

Never silently destroy newer user content.

---

# 71. BACKUP AND RECOVERY

Production architecture must consider:

- database backup
- file backup
- document recovery
- version history
- restore process

For destructive operations:

prefer soft deletion where appropriate.

Critical user content should have recovery mechanisms.

---

# 72. DEPLOYMENT SAFETY

Before production deployment verify:

- environment variables
- database configuration
- storage permissions
- cache configuration
- queue configuration
- cron
- mail
- filesystem
- HTTPS
- application key
- error reporting
- production logging

Never deploy development configuration to production.

---

# 73. UPDATE SAFETY

Application updates must preserve:

- .env
- database
- uploaded files
- private storage
- generated files
- user data

Never overwrite:

.env

Never delete:

storage/

unless explicitly required.

Deployment process:

BACKUP
↓
MAINTENANCE PLAN
↓
MIGRATE
↓
UPDATE
↓
CLEAR/REBUILD CACHE
↓
VERIFY
↓
ROLLBACK IF REQUIRED

---

# 74. DEPENDENCY MANAGEMENT

Before adding a package:

Check:

- necessity
- maintenance status
- compatibility
- security
- licensing
- Laravel/PHP compatibility
- shared-hosting compatibility

Do not add dependencies for trivial functionality.

---

# 75. BACKWARD COMPATIBILITY

Before modifying an existing feature:

identify:

- existing routes
- database compatibility
- API behavior
- existing UI
- existing user data
- existing tests

Avoid breaking existing behavior unless explicitly required.

---

# 76. ACCESSIBILITY PRODUCTION GATE

Verify:

- keyboard navigation
- visible focus
- labels
- semantic HTML
- accessible dialogs
- error announcements
- contrast
- reduced motion

Glassmorphism must NEVER compromise readability or accessibility.

---

# 77. MOBILE PRODUCTION GATE

Test important features on:

- desktop
- tablet
- mobile

Check:

- navigation
- forms
- editor
- modals
- drawers
- touch targets
- scrolling
- keyboard behavior
- loading states

---

# 78. CODE REVIEW GATE

Before completion, perform a simulated senior code review.

Ask:

Would I approve this PR for production?

Check:

- architecture
- naming
- duplication
- security
- performance
- testing
- maintainability
- error handling

If not, fix the issues before completion.

---

# 79. REGRESSION PROTECTION

Every bug fix must include a regression test when practical.

Every major feature must include tests that protect existing behavior.

Never fix:

Bug A

and accidentally break:

Feature B.

---

# 80. FINAL PRODUCTION READINESS SCORE

Before declaring a major feature production-ready:

Architecture PASS
Functionality PASS
Database PASS
Security PASS
Validation PASS
Authorization PASS
Error Handling PASS
Performance PASS
Accessibility PASS
Responsive UI PASS
Testing PASS
Logging PASS
Recovery PASS
Deployment PASS
Documentation PASS

If a critical category fails:

STATUS = NOT PRODUCTION READY

---

# 81. DEVELOPMENT STATUS

Use these states:

PLANNING

ANALYZING

IMPLEMENTING

INTEGRATING

TESTING

FIXING

REVIEWING

PRODUCTION-READY

BLOCKED

Never report:

"Complete"

when critical implementation remains.

---

# 82. USER TESTING HANDOFF

After a complete feature has passed the engineering checks:

STOP DEVELOPMENT FOR THAT FEATURE.

Provide the user with:

## FEATURE COMPLETE

### What was built

...

### Files created

...

### Files modified

...

### Database changes

...

### Security implemented

...

### Tests performed

...

### Known limitations

...

### Local testing checklist

1. Start application.
2. Open feature.
3. Test normal flow.
4. Test invalid input.
5. Test unauthorized access.
6. Test mobile layout.
7. Test error recovery.
8. Test refresh/navigation.
9. Test data persistence.
10. Test related existing functionality.

Then tell the user:

"Implementation is complete and ready for local-device testing."

Do NOT automatically begin unrelated work.

---

# 83. LOCAL TESTING BOUNDARY

The AI agent must distinguish between:

CODE VERIFICATION

and:

REAL USER TESTING

The AI can verify:

- source code
- routes
- migrations
- automated tests
- static correctness
- architecture
- expected behavior

The user must verify where necessary:

- actual device experience
- real browser behavior
- real hosting environment
- real AI provider behavior
- visual quality
- real-world workflow

Never claim that real-device testing has been completed if the AI
cannot access the user's device.

---

# 84. USER FEEDBACK LOOP

After user testing, if the user reports:

"It doesn't work"

or:

"Change this"

or:

"There is a bug"

return to:

INSPECT
↓
REPRODUCE
↓
ROOT CAUSE
↓
FIX
↓
TEST
↓
REGRESSION REVIEW
↓
HANDOFF AGAIN

Do not blindly patch.

---

# 85. FEATURE UPGRADE PROTOCOL

When user says:

"Upgrade authentication"

or:

"Add 2FA"

or:

"Improve document editor"

first inspect the current feature.

Determine:

CURRENT ARCHITECTURE
CURRENT BEHAVIOR
DEPENDENCIES
DATABASE
UI
TESTS
SECURITY

Then design the upgrade.

Never rebuild an existing feature from zero unless necessary.

---

# 86. COMPLETE PROJECT BUILD PROTOCOL

If the user says:

"Build the complete application"

the agent must work in phases.

PHASE 1
Project foundation

PHASE 2
Authentication

PHASE 3
Core UI/design system

PHASE 4
Dashboard

PHASE 5
Projects

PHASE 6
Documents

PHASE 7
Editor

PHASE 8
AI engine

PHASE 9
Templates

PHASE 10
Brand Voice

PHASE 11
SEO

PHASE 12
Knowledge/RAG

PHASE 13
Usage/quotas

PHASE 14
Sharing

PHASE 15
Notifications

PHASE 16
Administration

PHASE 17
Security hardening

PHASE 18
Performance

PHASE 19
Testing

PHASE 20
Production readiness

Each phase must pass its own completion gate.

---

# 87. DO NOT OVERWRITE WORK

Never destroy existing working functionality merely to implement
a new feature.

Before large modifications:

inspect
backup conceptually
understand dependencies
make minimal safe changes

When appropriate, create migrations instead of modifying historical
migrations.

---

# 88. DO NOT CREATE ARCHITECTURAL DEBT

Do not knowingly introduce:

- duplicate services
- duplicate components
- duplicate queries
- hardcoded credentials
- hardcoded URLs
- giant classes
- giant Livewire components
- feature-crossing hacks
- temporary hacks without documentation

If a temporary workaround is unavoidable:

document:

WHY
WHAT
LIMITATION
FUTURE FIX

---

# 89. NO FALSE COMPLETION

Never say:

"Feature complete"

if any critical functionality is:

- mocked
- fake
- hardcoded
- disabled
- unfinished
- undocumented
- knowingly insecure

Instead say:

"Feature partially implemented"

and clearly list remaining work.

---

# 90. FINAL AUTONOMOUS ENGINEERING LOOP

For EVERY user development request:

USER REQUEST
↓
CLASSIFY TASK
↓
SCAN EXISTING PROJECT
↓
IDENTIFY FEATURE
↓
CHECK EXISTING IMPLEMENTATION
↓
DETERMINE CREATE / EXTEND / REFACTOR / FIX
↓
CREATE FEATURE PLAN
↓
DESIGN FRONTEND
↓
DESIGN BACKEND
↓
DESIGN DATABASE
↓
IMPLEMENT FRONTEND
↓
IMPLEMENT BACKEND
↓
IMPLEMENT BUSINESS LOGIC
↓
IMPLEMENT DATABASE
↓
INTEGRATE
↓
VALIDATE
↓
AUTHORIZE
↓
SECURITY REVIEW
↓
ERROR HANDLING
↓
PERFORMANCE REVIEW
↓
RESPONSIVE REVIEW
↓
ACCESSIBILITY REVIEW
↓
TEST
↓
REGRESSION TEST
↓
CODE REVIEW
↓
PRODUCTION READINESS REVIEW
↓
DOCUMENT
↓
HANDOFF TO USER
↓
USER LOCAL TESTING
↓
FEEDBACK
↓
FIX / UPGRADE
↓
REPEAT

---

# 91. GOLDEN RULE

DO NOT JUST WRITE CODE.

BUILD SOFTWARE.

Every feature must be:

- feature-owned
- modular
- reusable
- secure
- tested
- maintainable
- performant
- accessible
- responsive
- observable
- recoverable
- shared-hosting compatible
- production-grade

When the user says:

"Build complete X"

interpret it as:

"Inspect everything related to X, design the architecture,
implement the complete frontend, backend, database, business logic,
security, validation, authorization, error handling, testing,
documentation and production-readiness requirements for X."

When the user says:

"Fix X"

interpret it as:

"Find the root cause, fix it safely, add regression protection,
verify related functionality and ensure the fix does not create
new architectural or security problems."

When the user says:

"Upgrade X"

interpret it as:

"Inspect the existing implementation first, preserve working
behavior, design the upgrade, implement it across all required
layers and verify backward compatibility."

When the user says:

"Build complete project"

interpret it as:

"Act as the complete software engineering team and build the
application phase-by-phase until every required subsystem reaches
production-ready status."

NEVER sacrifice production quality merely to finish faster.
NEVER claim completion without verification.
NEVER duplicate existing functionality.
NEVER blindly overwrite working code.
NEVER trust unvalidated user or external data.
NEVER expose secrets.
NEVER leave critical functionality mocked.

THE OBJECTIVE IS:

PRODUCTION-GRADE SOFTWARE,
NOT JUST WORKING CODE.

92. # # FEATURE-NAME-WISE PROJECT ARCHITECTURE PROTOCOL

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

============================================================ 2. FEATURE-FIRST RULE
============================================================

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

============================================================ 3. DASHBOARD EXAMPLE
============================================================

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

============================================================ 4. COMPLETE FEATURE STRUCTURE
============================================================

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

============================================================ 5. DATABASE FEATURE OWNERSHIP
============================================================

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

============================================================ 6. MODELS
============================================================

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

============================================================ 7. ROUTES
============================================================

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

============================================================ 8. LIVEWIRE
============================================================

Livewire components MUST belong to their feature.

Example:

app/Features/Dashboard/Livewire/DashboardPage.php

app/Features/Documents/Livewire/DocumentEditor.php

app/Features/AI/Livewire/GenerationPanel.php

app/Features/SEO/Livewire/SeoAnalyzer.php

Do NOT put every Livewire component into one giant:

app/Livewire/

directory if the project uses feature-first architecture.

============================================================ 9. BLADE
============================================================

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

============================================================ 10. JAVASCRIPT
============================================================

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

============================================================ 11. CSS
============================================================

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

============================================================ 12. SHARED COMPONENTS
============================================================

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

============================================================ 13. AI FEATURE EXAMPLE
============================================================

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

============================================================ 14. FEATURE DEPENDENCIES
============================================================

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

============================================================ 15. SHARED / CORE LAYER
============================================================

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

============================================================ 16. FEATURE README
============================================================

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

============================================================ 17. WHEN ADDING A NEW FEATURE
============================================================

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

============================================================ 18. FEATURE CREATION ORDER
============================================================

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

============================================================ 19. CREATE / UPGRADE / FIX PROTOCOL
============================================================

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

============================================================ 20. FILE CREATION RULE
============================================================

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

============================================================ 21. FILE NAMING
============================================================

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

============================================================ 22. COMPONENT REUSE
============================================================

Before creating a component:

SEARCH for an existing reusable component.

If one exists:

    reuse it.

If it is almost suitable:

    improve the shared component only if doing so
    will not break existing features.

If the component is genuinely feature-specific:

    keep it inside the feature.

============================================================ 23. FRONTEND/BACKEND CONTRACT
============================================================

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

============================================================ 24. PRODUCTION-GRADE REQUIREMENTS
============================================================

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

============================================================ 25. UI REQUIREMENTS
============================================================

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

============================================================ 26. RESPONSIVE ARCHITECTURE
============================================================

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

============================================================ 27. TESTING
============================================================

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

============================================================ 28. FINAL VERIFICATION
============================================================

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

============================================================ 29. CHANGE REPORT
============================================================

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

============================================================ 30. GOLDEN RULE
============================================================

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

_The key rule should be (here is the examples):_

Everything belonging to a feature stays close to that feature.

This makes adding, upgrading, debugging, testing, or eventually removing a feature much easier.

HOA-Studio — Feature-First Folder Structure
│ ├── Features/
│ │ │ ├── Data/
│ │ │ ├── Jobs/
│ │ │ ├── Livewire/
│ │ │ ├── Models/
│ │ │ │ ├── SeoAnalysis.php
│ │ │ │ └── SeoKeyword.php
│ │ │ ├── Services/
│ │ │ │ ├── SeoAnalysisService.php
│ │ │ │ └── SeoScoringService.php
│ │ │ └── Tests/
│ │ │
│ │ ├── Usage/
│ │ │ ├── Actions/
│ │ │ ├── Data/
│ │ │ ├── Livewire/
│ │ │ ├── Models/
│ │ │ ├── Services/
│ │ │ └── Tests/
│ │ │
│ │ ├── Notifications/
│ │ │ ├── Actions/
│ │ │ ├── Livewire/
│ │ │ ├── Models/
│ │ │ └── Services/
│ │ │
│ │ ├── Sharing/
│ │ │ ├── Actions/
│ │ │ ├── Data/
│ │ │ ├── Livewire/
│ │ │ ├── Models/
│ │ │ ├── Policies/
│ │ │ └── Services/
│ │ │
│ │ ├── ImportExport/
│ │ │ ├── Actions/
│ │ │ ├── Jobs/
│ │ │ ├── Services/
│ │ │ └── Tests/
│ │ │
│ │ ├── Settings/
│ │ │ ├── Actions/
│ │ │ ├── Data/
│ │ │ ├── Livewire/
│ │ │ ├── Models/
│ │ │ └── Services/
│ │ │
│ │ └── Administration/
│ │ ├── Actions/
│ │ ├── Data/
│ │ ├── Livewire/
│ │ ├── Models/
│ │ ├── Policies/
│ │ ├── Services/
│ │ └── Tests/
│ │
│ ├── Jobs/
│ ├── Console/
│ └── Providers/
│
├── database/
│ ├── factories/
│ │ ├── UserFactory.php
│ │ ├── ProjectFactory.php
│ │ ├── DocumentFactory.php
│ │ └── GenerationFactory.php
│ │
│ ├── migrations/
│ │ └── feature-name/
│ │
│ └── seeders/
│ ├── DatabaseSeeder.php
│ ├── AiModelSeeder.php
│

├── resources/views
│ ├── dashbord/
│ │ ├── components
│ │ ├── layout
│ │ ├── etc
│ │ └── etc
