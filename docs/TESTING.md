# Testing

The project has three complementary test layers:

- PHPUnit unit and feature tests for services, controllers, jobs, requests, models and database workflows.
- Playwright functional E2E tests for real browser flows.
- PHPStan and Pint checks for static analysis and code style.

## Local prerequisites

For the Docker-based setup, start the application stack first:

```bash
docker compose up -d app mysql redis queue
```

The local application is available at `http://localhost:8080`.

## Database configuration by execution context

The same MySQL instance has different addresses depending on where the command
runs. Do not copy values between these contexts.

| Command location | Host | Port | Database | Username | Password |
| --- | --- | --- | --- | --- | --- |
| Host machine PHPUnit | `127.0.0.1` | `3307` | `fzs_testing` | `root` | `root123` |
| Docker application and local Playwright | `mysql` | `3306` | `fzs` | `fzs` | `fzs123` |
| GitHub Actions PHPUnit | `127.0.0.1` | GitHub service port | `fzs_testing` | `root` | `root` |

`mysql:3306` is reachable only from containers on the Docker Compose network.
`127.0.0.1:3307` is the host-machine mapping for that same local MySQL
container. PHPUnit defaults are defined in `phpunit.xml`; use the explicit
environment below when running tests from the host to avoid inheriting values
from `.env`.

The default seeded administrator is:

```text
Email: fzs@fzs.rs
Password: fzs123
```

## PHPUnit

Run the complete backend suite with the Composer script:

```bash
composer test
composer test:unit
composer test:feature
```

To run PHPUnit against the Docker MySQL test database explicitly:

```bash
DB_HOST=127.0.0.1 \
DB_PORT=3307 \
DB_DATABASE=fzs_testing \
DB_USERNAME=root \
DB_PASSWORD=root123 \
./vendor/bin/phpunit --no-coverage --testdox --do-not-fail-on-warning --do-not-fail-on-deprecation
```

The test harness migrates a clean database for tests that do not use Laravel's
`RefreshDatabase` or `DatabaseTransactions` traits. The development demo data
is not loaded into every PHPUnit test. `DemoDataSeederTest` seeds the demo data
explicitly when it tests the seeder itself.

Useful focused commands:

```bash
./vendor/bin/phpunit tests/Unit/Jobs/GenerateZapisnikPdfJobTest.php
./vendor/bin/phpunit tests/Feature/QueueTest.php
./vendor/bin/phpunit tests/Unit/Services/IspitPdfServiceTest.php
```

The queue/PDF tests verify dispatching, job arguments, PDF output storage and
the Redis-compatible queue contract without requiring a running worker.

## Playwright E2E

The standard browser suite contains authenticated and unauthenticated flows,
zapisnik and exam registration workflows, PDF printing, grade saving, mobile
coverage, dashboard pages, operational pages and reference screens.

Run it against the Docker application:

```bash
docker compose up -d app mysql redis queue
docker compose exec -T app php artisan cache:clear
E2E_BASE_URL=http://127.0.0.1:8080 npm run test:e2e
```

The suite authenticates once in `tests/e2e/specs/auth.setup.ts` and reuses the
browser storage state. The invalid-login test uses an empty state in
`tests/e2e/specs/auth-negative.spec.ts`.

Useful Playwright commands:

```bash
npx playwright test --list
npx playwright test functional.spec.ts --grep "zapisnik"
npm run test:e2e:ui
npm run test:e2e:smoke
npx playwright show-report
```

The default base URL is `http://localhost:8080`. Override it when testing a
different server:

```bash
E2E_BASE_URL=http://127.0.0.1:8000 npm run test:e2e
```

Playwright authentication state is generated in `playwright/.auth/` and is
ignored by Git. Browser artifacts are written to `test-results/` and
`playwright-report/`.

The logout test runs last because logout invalidates the server-side session
stored in the shared Playwright authentication state. Keep destructive
authentication scenarios last or give them a distinct test user/session.

Document review routes use `/kandidat/documents/incomplete` for the admin list
and `/kandidat/{id}/documents/review` for an individual candidate. File storage
and document metadata are covered by PHPUnit service tests; browser tests cover
the review page and authorization boundary.

## CI/CD

GitHub Actions is defined in `.github/workflows/laravel.yml` and runs on pushes
and pull requests targeting `main` or `master`.

The workflow has three job groups:

1. `laravel-tests` runs a Unit/Feature matrix. Each job starts MySQL, creates `fzs_testing`, migrates it, builds Vite assets, and runs PHPUnit without coverage.
2. `e2e` starts a clean MySQL service, installs Chromium, starts Laravel on port `8000`, and runs the Playwright suite with `E2E_BASE_URL` set to that server.
3. `lint` runs Pint and PHPStan.

The E2E job uses file sessions, array cache and the synchronous queue so it does
not require Redis in CI. The application-level queue job is covered by the
PHPUnit queue and job tests.

The PHPUnit matrix explicitly overrides the global workflow variables with the
CI test database values. This is required because process environment variables
take precedence over `.env`, and migrations and PHPUnit must target the same
`fzs_testing` database. Vite must be built before Feature tests because Blade
views load `public/build/manifest.json`.

On E2E failure, CI uploads `playwright-report/`, `test-results/` and the local
Laravel server log as the `playwright-diagnostics` artifact.

## Current verified baseline

The current local baseline is:

```text
Unit PHPUnit: 335 tests, 1092 assertions, exit 0
Feature PHPUnit: 1482 tests, 3551 assertions, exit 0
Playwright: 89 E2E tests discovered and passed in the full local suite
```

When adding a new user-facing workflow, add a focused Playwright test and keep
business logic assertions in PHPUnit tests. Avoid relying on fixed database IDs;
select records from the seeded UI or create them through the tested workflow.
