# Auth Feature Module — HelpOfAi Studio (HOA-Studio)

## Purpose
The `Auth` feature encapsulates all authentication, registration, password lifecycle, account profile management, and quota initialization workflows following the Feature-Name-Wise architectural protocol.

## Architecture & Structure
```text
app/Features/Auth/
├── Actions/
│   ├── LoginUser.php             # Authentication validation, rate limiting & session initiation
│   ├── RegisterUser.php          # User creation, starter plan assignment & default quota hydration
│   └── UpdateUserProfile.php     # Account information, password change & preferences updates
├── Livewire/
│   ├── LoginPage.php             # Reactive glass login component
│   ├── RegisterPage.php          # Reactive glass registration component
│   ├── ForgotPasswordPage.php    # Password recovery dispatch component
│   └── ProfilePage.php           # User preferences, quota usage meter & settings
└── README.md                     # Feature specification and technical documentation

resources/views/auth/
├── login.blade.php               # Elevated glassmorphism sign-in interface
├── register.blade.php            # Elevated glassmorphism registration interface
├── forgot-password.blade.php     # Elevated glassmorphism reset link interface
└── profile.blade.php             # Multi-tier profile and quota consumption meter
```

## Database Schema & Quotas
- **User Attributes**: `role` (`admin`, `member`, `user`), `plan` (`starter`, `pro`, `enterprise`), `monthly_word_quota` (default `15000`), `used_word_quota`, `preferences` (`json`), `avatar_url`, `is_active`.
- **Default Preferences**: Theme (`dark`), default model (`OmniRoute: DeepSeek-V3`), autosave interval (`30s`).

## Security Considerations
- Rate limiting on login via `Str::transliterate(email|ip)` throttled to 5 attempts before lockout.
- Password hashing with Bcrypt rounds = 12.
- Session fixation protection with `session()->regenerate()`.
- Password confirmation required on registration and updates.
- Strict mass assignment protection on `User` model.

## Testing & Verification
- Unit & Feature tests in `tests/Feature/AuthTest.php` validating registration, login, profile updates, and session termination with 100% test coverage.