# Admin Control Center Module — HelpOfAi Studio (HOA-Studio)

## Purpose
The `Admin` feature module provides administrators with platform governance, user management, role assignment, monthly word quota adjustments, OmniRoute gateway configuration, and live generation audit logs.

## Structure
```text
app/Features/Admin/
├── Actions/
│   ├── GetAdminStats.php            # Platform analytics, words consumed & OmniRoute gateway health
│   ├── UpdateUserQuotaAndRole.php   # User profile, role, plan, and word quota mutations
│   └── SaveSystemSettings.php       # System settings & gateway configuration persistence
├── Livewire/
│   ├── AdminDashboardPage.php       # System overview, key metrics, and gateway diagnostics
│   ├── AdminUsersPage.php           # User & quota management with search and modal editor
│   ├── AdminUsageLogsPage.php       # AI generation audit logs with model filtering
│   └── AdminSettingsPage.php        # OmniRoute gateway, compression & default quota settings
└── README.md

resources/views/
├── layouts/admin.blade.php          # Dedicated elevated glassmorphism admin layout
└── admin/
    ├── dashboard.blade.php          # System overview and gateway health status
    ├── users.blade.php              # User & quota management table with edit modal
    ├── usage-logs.blade.php         # Real-time token and word consumption logs
    └── settings.blade.php           # Gateway configuration and platform parameters
```

## Security & Access Control
- All `/admin/*` routes are strictly guarded by `['auth', 'role:admin']` middleware.
- Self-deactivation protection: Admins cannot inadvertently ban or deactivate their own accounts.
- Password resets, plan upgrades, and role adjustments are validated with strict mass-assignment guards.

## Testing & Quality Gates
- `tests/Feature/AdminDashboardTest.php` validating:
  - Admin access authorization vs standard user 403 blocks.
  - User role and quota updates.
  - Status toggles (Active / Banned).
  - System settings synchronization.
  - AI generation usage audit logs.