# Laravel Queue System: Complete Implementation Guide

## Audience and scope

This guide is for **application developers** and **DevOps engineers** working on `fzs-laravel`. It documents the queue-related implementation currently present in this repository, plus practical operational patterns for production.

---

## 1) Queue system overview (current implementation)

### Core components in this project

- **Queue configuration**: `config/queue.php`
  - Supports Laravel drivers: `sync`, `database`, `beanstalkd`, `sqs`, `redis`
  - Default connection comes from `QUEUE_CONNECTION`
- **Environment configuration**:
  - `.env` currently uses `QUEUE_CONNECTION=redis`
  - `.env.example` defaults to `QUEUE_CONNECTION=sync`
- **Redis backend**:
  - Redis connection defined in `config/database.php` under `redis.default`
  - Queue connection `redis` in `config/queue.php` uses Redis connection `default`
- **Failed jobs handling**:
  - `failed_jobs` table migration exists (`2019_08_19_000000_create_failed_jobs_table.php`)
  - Failed job provider configured in `config/queue.php` (`database-uuids`)
- **Custom queue operations command**:
  - `app/Console/Commands/FailedJobsMonitor.php`
  - Registered in `app/Console/Kernel.php`
  - Provides list/show/retry/delete/clear workflow for failed jobs
- **Worker process templates (systemd)**:
  - `.sisyphus/templates/laravel-queue-worker.service`
  - `.sisyphus/templates/laravel-queue-scheduler.service`
- **Async event dispatch usage in application code**:
  - `App\Events\NewNotification` implements `ShouldBroadcast`
  - Dispatched from `App\Services\NotificationService::notifyUser()`
  - Channel authorization in `routes/channels.php`

### Important implementation note

At the time of writing, `app/Jobs` contains only a shared base class:

- `App\Jobs\Job` (abstract, uses `Illuminate\Bus\Queueable`)

There are **no concrete queued job classes** (e.g., `implements ShouldQueue`) committed in this repository yet.

---

## 2) Installation and setup instructions

## Prerequisites

- PHP 8.3+
- Composer dependencies installed
- MySQL available
- Redis available and reachable

## Initial setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Enable Redis queue processing

In `.env`:

```dotenv
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Then clear cached config:

```bash
php artisan optimize:clear
```

## Start a local worker

```bash
php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=90
```

---

## 3) Configuration details (`.env`, `config/queue.php`)

## `.env` keys (relevant)

- `QUEUE_CONNECTION`
  - Defines the default queue driver (`redis` recommended for async processing)
- `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`
  - Redis endpoint for queue and other Redis-backed services
- `DB_CONNECTION`
  - Used for failed jobs storage (`failed_jobs`) and job batches (`job_batches`)
- `CACHE_DRIVER`, `BROADCAST_DRIVER`
  - Presently independent concerns, but can share Redis infrastructure

Current local values observed:

- `QUEUE_CONNECTION=redis`
- `CACHE_DRIVER=redis`
- `BROADCAST_DRIVER=reverb`

## `config/queue.php` explained

### Default

```php
'default' => env('QUEUE_CONNECTION', 'sync')
```

### Connections

- `sync`: executes immediately (no worker)
- `database`: stores queued jobs in DB table `jobs`
- `beanstalkd`: Beanstalk queue backend
- `sqs`: AWS SQS
- `redis`: Redis queue backend (recommended for this project)

Key Redis options:

- `'connection' => 'default'` (maps to `config/database.php` Redis connection)
- `'queue' => env('REDIS_QUEUE', 'default')`
- `'retry_after' => 90`
- `'block_for' => null`
- `'after_commit' => false`

### Failed jobs

```php
'failed' => [
  'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
  'database' => env('DB_CONNECTION', 'mysql'),
  'table' => 'failed_jobs',
]
```

### Batching

Uses `job_batches` table via DB connection from `DB_CONNECTION`.

---

## 4) How to create and dispatch jobs

> Current state: no concrete custom queued jobs exist yet. The project has `App\Jobs\Job` as a reusable base.

## Recommended job template

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExampleAsyncJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 90;

    public function __construct(public int $entityId) {}

    public function handle(): void
    {
        // long-running work
    }
}
```

## Dispatch patterns

```php
ExampleAsyncJob::dispatch($id);                    // default queue/connection
ExampleAsyncJob::dispatch($id)->onQueue('high');  // target queue
ExampleAsyncJob::dispatch($id)->delay(now()->addSeconds(30));
ExampleAsyncJob::dispatchSync($id);               // synchronous, debugging only
```

## Existing async dispatch in codebase

`NotificationService::notifyUser()` dispatches event:

```php
NewNotification::dispatch($userId, $title, $message, $type, $data);
```

Because `NewNotification` implements `ShouldBroadcast`, broadcast delivery can be queued depending on broadcaster/queue configuration.

---

## 5) Worker management (systemd, local development, Horizon)

## Local development

Use foreground worker during development:

```bash
php artisan queue:work redis --queue=high,default --sleep=1 --tries=3 --timeout=120
```

After code updates to job/event handlers:

```bash
php artisan queue:restart
```

Reference: `.sisyphus/docs/LOCAL_DEVELOPMENT_GUIDE.md`

## systemd (production)

Two templates are provided:

1. `laravel-queue-worker.service` (general/default queues)
2. `laravel-queue-scheduler.service` (dedicated `scheduler` queue)

Install:

```bash
sudo cp /home/dragomix/fzs-laravel/.sisyphus/templates/laravel-queue-worker.service /etc/systemd/system/
sudo cp /home/dragomix/fzs-laravel/.sisyphus/templates/laravel-queue-scheduler.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-queue-worker.service
sudo systemctl enable --now laravel-queue-scheduler.service
```

Check status and logs:

```bash
sudo systemctl status laravel-queue-worker.service
sudo journalctl -u laravel-queue-worker.service -f
sudo journalctl -u laravel-queue-scheduler.service -f
```

## Horizon

Horizon is **not currently installed** in this repository (`composer.json` has no `laravel/horizon`, no `config/horizon.php`).

If adopting Horizon:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
php artisan horizon
```

Then operate with `horizon:status`, `horizon:pause`, `horizon:continue`, `horizon:terminate`.

---

## 6) Monitoring and debugging

## Built-in commands

```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
php artisan queue:monitor redis:default --max=100
```

## Custom command: `failed-jobs:monitor`

Examples:

```bash
php artisan failed-jobs:monitor list
php artisan failed-jobs:monitor list --connection=redis --queue=emails --search=SendInvoice
php artisan failed-jobs:monitor show <uuid>
php artisan failed-jobs:monitor retry <uuid>
php artisan failed-jobs:monitor retry-all
php artisan failed-jobs:monitor delete <uuid>
php artisan failed-jobs:monitor delete-all
php artisan failed-jobs:monitor clear
```

## Operational telemetry sources

- `storage/logs/laravel.log`
- `journalctl` for systemd workers
- Redis queue depth (example): `redis-cli LLEN queues:default`

## Fast debugging flow

1. Check effective queue connection:
   - `php artisan tinker --execute="dump(config('queue.default'))"`
2. Clear stale config cache:
   - `php artisan optimize:clear`
3. Restart workers:
   - `php artisan queue:restart`
4. Inspect failed jobs:
   - `php artisan failed-jobs:monitor list`

---

## 7) Best practices and performance tips

## For developers

- Keep jobs **small and idempotent**.
- Avoid huge payloads; pass IDs, load data in `handle()`.
- Set explicit `$tries`, `$timeout`, and optional backoff policy.
- Separate queues by workload type (`high`, `default`, `emails`, `reports`).
- Use `after_commit` strategy consciously when jobs depend on committed DB state.

## For DevOps

- Run multiple workers for parallelism (per queue priority where needed).
- Keep `--timeout` and `retry_after` aligned:
  - `retry_after` should generally be greater than max job execution time.
- Use dedicated worker groups for long-running jobs.
- Monitor queue depth and failed job rate; alert on sustained growth.
- Use graceful restarts (`queue:restart`) during deploys.

## Suggested queue design

- `high`: user-facing latency sensitive tasks
- `default`: normal background work
- `low`/`batch`: heavy offline jobs

---

## 8) Troubleshooting common issues

## Jobs are not being processed

- Worker not running
- Wrong `QUEUE_CONNECTION`
- Worker listening to wrong queue name

Checks:

```bash
php artisan env
php artisan tinker --execute="dump(config('queue.default'))"
```

## Jobs fail repeatedly / reappear

- Timeout too low for workload
- Non-idempotent logic causing retries to fail
- External dependency instability (SMTP/API/Redis)

Actions:

- Increase worker/job timeout
- Improve retry/backoff strategy
- Inspect exception via `failed-jobs:monitor show <uuid>`

## Config changes not applied

```bash
php artisan optimize:clear
php artisan queue:restart
```

## Redis errors

- Validate host/port/password
- Validate Redis service health
- Confirm PHP Redis extension/client compatibility

## Missing failed jobs table

```bash
php artisan migrate
```

---

## 9) API reference for custom job classes created

## `App\Jobs\Job` (base class)

**Path**: `app/Jobs/Job.php`

**Type**: `abstract class`

**Traits**:

- `Illuminate\Bus\Queueable`

**Purpose**:

- Common base for project job classes
- Enables helper methods like `onQueue()` and `delay()` through `Queueable`

**Current methods/properties**:

- No custom methods/properties defined in this class

## Current concrete custom job classes

- None present in `app/Jobs` at this moment.

## Queue-adjacent custom APIs in this implementation

### `App\Console\Commands\FailedJobsMonitor`

- **Command**: `failed-jobs:monitor`
- **Actions**: `list`, `show`, `retry`, `retry-all`, `delete`, `delete-all`, `clear`
- **Options**:
  - `--page`, `--per-page`, `--connection`, `--queue`, `--search`

### `App\Events\NewNotification`

- Implements `Illuminate\Contracts\Broadcasting\ShouldBroadcast`
- Constructor payload:
  - `userId`, `title`, `message`, `type`, `data`
- Broadcast channel:
  - `notifications.{userId}`
- Broadcast alias:
  - `notification.new`

---

## 10) Usage examples in controllers and services

## Service example (existing): `NotificationService`

**File**: `app/Services/NotificationService.php`

- `notifyUser(...)` dispatches `NewNotification`
- `notifyAdmins(...)` iterates admin users and dispatches notifications
- `broadcastObavestenje(...)` fans out notifications by target role/type

Example pattern:

```php
public function notifyUser(int $userId, string $title, string $message, string $type = 'info', ?array $data = null): void
{
    NewNotification::dispatch($userId, $title, $message, $type, $data);
}
```

## Controller example (existing): `ObavestenjeController`

**File**: `app/Http/Controllers/ObavestenjeController.php`

- Injects `NotificationService`
- Invokes notification workflow after creating an announcement

Excerpt:

```php
if ($request->boolean('posalji_email')) {
    $this->notificationService->sendObavestenjeToAllStudents(
        $obavestenje->naslov,
        $obavestenje->sadrzaj,
        $obavestenje->tip
    );
}
```

> Note: `sendObavestenjeToAllStudents()` is invoked by the controller but is not currently found in `NotificationService`. This should be reconciled to avoid runtime errors.

## Suggested controller->job dispatch pattern (for future concrete jobs)

```php
use App\Jobs\GenerateReportJob;

public function generateReport(int $reportId)
{
    GenerateReportJob::dispatch($reportId)->onQueue('reports');

    return back()->with('status', 'Report queued for background processing.');
}
```

---

## Production readiness checklist

- [ ] `QUEUE_CONNECTION=redis` configured in production env
- [ ] Redis reachable from app hosts
- [ ] `failed_jobs` and `job_batches` tables migrated
- [ ] systemd workers installed and enabled
- [ ] deploy pipeline issues `php artisan queue:restart`
- [ ] alerting in place for queue depth and failed jobs
- [ ] runbook includes `failed-jobs:monitor` procedures

---

## File reference index

- `config/queue.php`
- `config/database.php`
- `.env` / `.env.example`
- `app/Jobs/Job.php`
- `app/Console/Commands/FailedJobsMonitor.php`
- `app/Console/Kernel.php`
- `app/Services/NotificationService.php`
- `app/Events/NewNotification.php`
- `app/Http/Controllers/ObavestenjeController.php`
- `routes/channels.php`
- `.sisyphus/templates/laravel-queue-worker.service`
- `.sisyphus/templates/laravel-queue-scheduler.service`
- `.sisyphus/docs/LOCAL_DEVELOPMENT_GUIDE.md`
