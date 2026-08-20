# Documents Feature Module — HelpOfAi Studio (HOA-Studio)

## Purpose
The `Documents` feature manages the creation, versioning, delta tracking, and rollback mechanics of all content generated within HelpOfAi Studio.

## Structure
```text
app/Features/Documents/
├── Models/
│   ├── Document.php              # Core document entity with reading time, word count & SEO scores
│   ├── DocumentContent.php       # Raw HTML, Markdown & ProseMirror AST JSON storage
│   └── DocumentVersion.php       # Immutable chronological snapshot history
├── Actions/
│   ├── CreateDocument.php        # Atomically creates document, initial content, and Version #1
│   ├── SaveDocumentVersion.php   # Creates snapshot with operation tagging (manual_save, ai_expand, etc.)
│   ├── RestoreDocumentVersion.php# Restores previous snapshot as a new version without data loss
│   └── DeleteDocument.php        # Soft deletion of document and associated entities
├── Livewire/
│   └── DocumentsPage.php         # Searchable, filterable document catalog with live modals
└── README.md
```