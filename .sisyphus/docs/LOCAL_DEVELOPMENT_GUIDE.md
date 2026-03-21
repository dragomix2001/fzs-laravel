# Local Development Guide: Laravel Queue Workers

This guide explains how to set up, run, monitor, and debug queue workers in local development for this project.

> Project context detected:
> - Laravel `^13.0`
> - Default queue config supports: `sync`, `database`, `redis`, `sqs`, `beanstalkd`
> - Current local `.env` uses `QUEUE_CONNECTION=redis`
> - Horizon config/package is **not currently installed** in this repository

---

## 1) Ways to run workers locally

## `queue:work` (recommended for most local dev)

Long-running worker process. Fast and closest to production behavior.

```bash
php artisan queue:work
```

Useful options:

```bash
# Process only one job then exit (good for debugging)
php artisan queue:work --once

# Limit runtime/memory for safer local loops
php artisan queue:work --timeout=120 --memory=256 --tries=3

# Process only selected queues in priority order
php artisan queue:work --queue=high,default,low

# Sleep 1s when no jobs exist
php artisan queue:work --sleep=1
```

When code changes affect jobs/services, restart workers:

```bash
php artisan queue:restart
```

---

## `queue:listen` (useful during heavy code editing)

`queue:listen` boots the framework for each job, so changes are picked up automatically. It is slower than `queue:work`.

```bash
php artisan queue:listen
```

Use this when:
- You are rapidly iterating on job code and don’t want frequent manual restarts.
- Throughput is not important.

---

## `sync` mode (no worker process)

In `.env`, set:

```dotenv
QUEUE_CONNECTION=sync
```

Jobs run immediately inside request/command lifecycle. Good for quick feature work, but not representative of real async behavior.

---

## 2) Running multiple queues

Laravel supports queue priority by listing queue names in order:

```bash
php artisan queue:work --queue=critical,high,default,low
```

You can also run dedicated workers per queue (separate terminals):

```bash
# Terminal 1
php artisan queue:work --queue=critical --sleep=1 --tries=3

# Terminal 2
php artisan queue:work --queue=default --sleep=1 --tries=3

# Terminal 3
php artisan queue:work --queue=emails --sleep=1 --tries=3
```

Run multiple workers for the same queue for concurrency:

```bash
# Open multiple terminals and run the same command
php artisan queue:work --queue=default
```

Tips:
- Use dedicated queues for long-running jobs to avoid blocking short jobs.
- Keep queue names consistent between dispatch calls and worker flags.

---

## 3) Laravel Horizon in development (if installed)

Horizon is **not installed in this repo right now** (no `laravel/horizon` package and no `config/horizon.php`).

If you decide to enable it for local development:

```bash
composer require laravel/horizon --dev
php artisan horizon:install
php artisan migrate
php artisan horizon
```

Then open Horizon dashboard at:

```text
/horizon
```

Horizon commands:

```bash
php artisan horizon           # start horizon
php artisan horizon:status    # check status
php artisan horizon:pause     # pause processing
php artisan horizon:continue  # resume processing
php artisan horizon:terminate # gracefully restart masters/workers
```

Use Horizon when you want rich queue metrics and easier multi-worker management in local/staging.

---

## 4) Debugging tips and common issues

## Fast debugging checklist

1. Confirm env:

```bash
php artisan env
php artisan tinker --execute="dump(config('queue.default'))"
```

2. Clear stale cache after env/config changes:

```bash
php artisan optimize:clear
```

3. Restart workers:

```bash
php artisan queue:restart
```

4. Inspect failed jobs:

```bash
php artisan queue:failed
php artisan queue:retry all
```

## Common issues

- **Jobs never run**
  - Worker not started.
  - Wrong connection (`QUEUE_CONNECTION`) or wrong queue name.

- **Code changes not reflected**
  - Using `queue:work` without restart. Run `php artisan queue:restart`.

- **Jobs fail with timeout**
  - Increase worker `--timeout` and align job `$timeout`/`retry_after` strategy.

- **Redis connection errors**
  - Ensure local Redis is running and `.env` Redis settings are correct.

- **Failed jobs table missing**
  - Run migrations (`php artisan migrate`) to ensure `failed_jobs` exists.

- **Serialized model/data errors**
  - Ensure queued payloads are serializable and models still exist at handle time.

---

## 5) Example commands for common scenarios

## Default local async processing (Redis)

```bash
php artisan queue:work redis --queue=default --sleep=1 --tries=3 --timeout=120
```

## High-priority first

```bash
php artisan queue:work --queue=high,default
```

## One-job test run

```bash
php artisan queue:work --once
```

## Listen mode while editing

```bash
php artisan queue:listen --queue=default
```

## Retry and clean failed jobs

```bash
php artisan queue:retry all
php artisan queue:flush
```

---

## 6) Monitoring queue status

Without Horizon, use built-in commands:

```bash
php artisan queue:failed          # list failed jobs
php artisan queue:monitor redis:default --max=100
```

`queue:monitor` can be scheduled for alerts when queue size crosses threshold.

Also monitor:
- App logs: `storage/logs/laravel.log`
- Redis queue depth (if using Redis CLI):

```bash
redis-cli LLEN queues:default
```

If Horizon is installed, prefer `/horizon` dashboard for real-time metrics.

---

## 7) Stopping and restarting workers

## Graceful restart (recommended)

```bash
php artisan queue:restart
```

This signals workers to finish current job and reload.

## Stop foreground worker

Press `Ctrl + C` in terminal running `queue:work` / `queue:listen`.

## If process is stuck

Stop process from OS tools (`ps`, Task Manager, Docker container restart, etc.), then start worker again.

---

## 8) Using Laravel Tinker to test jobs

Tinker is installed in this project (`laravel/tinker`).

## Dispatch a job

```bash
php artisan tinker
```

In Tinker:

```php
App\Jobs\ExampleJob::dispatch();
```

## Dispatch to specific queue/connection

```php
App\Jobs\ExampleJob::dispatch()->onQueue('high')->onConnection('redis');
```

## Test delayed jobs

```php
App\Jobs\ExampleJob::dispatch()->delay(now()->addSeconds(30));
```

## Execute synchronously for quick debugging

```php
App\Jobs\ExampleJob::dispatchSync();
```

---

## Recommended local baseline for this repo

Given current project settings (`QUEUE_CONNECTION=redis`), a practical local setup is:

1. Ensure Redis is running.
2. Run migrations.
3. Start worker:

```bash
php artisan queue:work redis --queue=high,default --sleep=1 --tries=3 --timeout=120
```

4. After job code changes:

```bash
php artisan queue:restart
```

5. Check failures regularly:

```bash
php artisan queue:failed
```

This gives reliable async behavior close to production while staying simple for local development.
