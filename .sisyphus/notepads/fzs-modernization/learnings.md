# Learnings from FZS-Laravel Modernization

## Patterns Established
- N+1 optimization: Model::whereIn($ids)->get()->keyBy('id')
- Storage facade: Storage::disk('uploads')->putFileAs()
- Service layer: Controllers inject services, delegate business logic
- Form Requests: Type-hint specific request classes in controller methods
- Bridge aliases: use App\ModelName works via bridge.php class aliasing

## Code Conventions
- Serbian variable names preserved
- Cyrillic error messages maintained
- AndroModel base class used by 57 models (to be removed in Phase 3.1)
- Non-standard namespace App\ for models (not App\Models\) via bridge.php

## Environment
- PHP 8.5.3, Laravel 13.0
- MySQL database
- WSL2 without Docker
- Redis for cache and queue


## FAZA 2.6: Queued Jobs (completed)

### Job Creation Pattern
- Use `php artisan make:job JobName` — generates with `ShouldQueue` + `Queueable` trait
- Laravel 13 generates jobs using `Illuminate\Foundation\Queue\Queueable` (not the old Bus/Queueable split)
- Promoted constructor properties work perfectly for job payloads (serialized via SerializesModels when Eloquent models)
- `public int $tries` and `public int $timeout` as class properties set retry/timeout behavior

### Job Implementation Notes
- BroadcastNotificationJob: user ID resolution stays in service (before dispatch), notification sending in job
- GenerateZapisnikPdfJob: `zapisnikStampa()` uses `PDF::Output()` which sends to stdout — capture with `ob_start()`/`ob_get_clean()` for file storage
- MassEnrollmentJob: `$tries = 1` to prevent duplicate enrollments; inner try-catch continues loop on per-kandidat failure
- `failed()` method uses Cyrillic error messages consistent with project convention

### Service Update Pattern
- Sync methods preserved untouched (backward compatibility)
- New async methods added with `Async` suffix: `generatePdfAsync()`, `masovniUpisAsync()`
- `broadcastObavestenje()` updated in-place (was already the recommended async entry point)

### Queue Infrastructure
- QUEUE_CONNECTION=redis already in .env
- Supervisor config at `.sisyphus/notepads/fzs-modernization/supervisor-config.ini`
- Run worker: `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600`


## FAZA 3.6: Scheduled Tasks (completed - commit 6c584a9)

### Command Creation Pattern
- Use `php artisan make:command CommandName` with promoted constructor properties
- Set `$signature` with optional flags: `'backup:run {--compress}'`
- Set `$description` for artisan list help text
- Return `Command::SUCCESS` or `Command::FAILURE` from handle() method

### Scheduled Task Implementation Notes
- **BackupDatabase**: Uses mysqldump via shell_exec(), stores in storage/app/backups/
  - Optional --compress flag for gzip compression
  - Environment vars extracted from config/database.php
  - Output captures both stdout and stderr for error reporting
- **CleanupOldNotifications**: Deletes notifications older than 90 days (configurable via --days option)
  - Uses DatabaseNotification model, not custom notification tables
- **ArchiveCompletedZapisnici**: Archives exam records older than 6 months (configurable via --months)
  - Sets arhivirano=1 on ZapisnikOPolaganjuIspita records

### Kernel Schedule Configuration
- `dailyAt('02:00')` for daily backups (off-peak hours)
- `weekly()->sundays()->at('03:00')` for weekly cleanup
- `monthlyOn(1, '04:00')` for first-of-month archiving
- All scheduled tasks added to `app/Console/Kernel.php` protected schedule() method

### Production Setup
- Cron entry required: `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`
- Verify with: `php artisan schedule:list` (Laravel 13+)
- Manual run: `php artisan backup:run`, `php artisan notifications:cleanup`, `php artisan zapisnici:archive`
## FAZA 3.4 Frontend Modernization Learnings
- **jQuery Dependency**: Cannot be entirely removed because `DataTables` and `jQuery UI Autocomplete` are heavily used across multiple views (e.g. `kandidat/indeks.blade.php`, `sifarnici/editProfesor.blade.php`). Kept the CDN imports for jQuery and jQuery UI.
- **Bootstrap 5 Package**: Removed `bootstrap`, `@popperjs/core`, and `datatables.net-bs5` from `package.json`. Replaced with `alpinejs` and `datatables.net-dt`.
- **Layouts**: The app uses multiple layouts (`app.blade.php` and `layout.blade.php`). Migrated `app.blade.php` fully to Tailwind CSS and AlpineJS.
- **Components**: Created reusable Alpine.js + Tailwind components (`modal.blade.php` and `alert.blade.php`).

## FIX: Main Layout Corrected
- **layout.blade.php**: Discovered that the true main layout used by most views (`kandidat`, `ispit`, `prijava`) is `layout.blade.php`, not `app.blade.php`.
- **CDN Removal**: Successfully removed the Bootstrap 5 CSS/JS CDN imports from `layout.blade.php`.
- **Alpine.js Sidebar**: Replaced the Vanilla JS event listeners for the sidebar toggle with Alpine.js `x-data` state to manage the mobile overlay and sidebar visibility gracefully.
- **Top Header**: Converted the top user dropdown menu to use Alpine.js `x-data="{ open: false }"`, removing reliance on Bootstrap's native dropdown JS.
- **jQuery Maintenance**: Retained `jquery.min.js` and `jquery-ui.min.js` in `layout.blade.php` to ensure DataTables and autocomplete remain operational.
