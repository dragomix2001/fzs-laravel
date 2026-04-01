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
