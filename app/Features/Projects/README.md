# Projects Feature Module — HelpOfAi Studio (HOA-Studio)

## Purpose
The `Projects` feature handles multi-tenant workspace partitioning, grouping documents, brand voices, and knowledge sources under dedicated folders with custom accent colors and metadata.

## Structure
```text
app/Features/Projects/
├── Models/
│   ├── Project.php               # Workspace domain model with SoftDeletes
│   └── ProjectMember.php         # Access control & workspace membership
├── Actions/
│   ├── CreateProject.php         # Slug generation, color assigning, and user binding
│   ├── UpdateProject.php         # Workspace mutation & metadata updates
│   └── DeleteProject.php         # Soft-deletion of project resources
├── Livewire/
│   └── ProjectsPage.php          # Interactive glass workspace management interface
└── README.md
```