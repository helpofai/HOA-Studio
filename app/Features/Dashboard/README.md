# Dashboard Feature Module — HelpOfAi Studio (HOA-Studio)

## Purpose
The `Dashboard` feature provides users with an actionable overview of workspace analytics, word quota consumption, active projects, recent documents, and chronological AI version snapshots.

## Structure
```text
app/Features/Dashboard/
├── Actions/
│   └── GetDashboardStats.php     # Aggregates document counts, word totals, quota limits, and activity
├── Livewire/
│   └── DashboardPage.php         # Reactive workspace dashboard component
└── README.md
```