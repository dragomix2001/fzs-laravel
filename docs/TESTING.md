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

The default seeded administrator is:

```text
Email: fzs@fzs.rs
Password: fzs123
```

## PHPUnit

Run the complete backend suite with the Composer script:

```bash
composer test
```

To run PHPUnit against the Docker MySQL test database explicitly:

```bash
DB_HOST=127.0.0.1 \
DB_PORT=3307 \
DB_DATABASE=fzs_testing \
DB_USERNAME=root \
DB_PASSWORD=root123 \
./vendor/bin/phpunit --testdox --do-not-fail-on-warning
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
npm run test:e2e
```

The suite authenticates once in `tests/e2e/specs/auth.setup.ts` and reuses the
browser storage state. The invalid-login test uses an empty state in
`tests/e2e/specs/auth-negative.spec.ts`.

Useful Playwright commands:

```bash
npx playwright test --list
npx playwright test functional.spec.ts --grep "zapisnik"
npm run test:e2e:ui
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

## CI/CD

GitHub Actions is defined in `.github/workflows/laravel.yml` and runs on pushes
and pull requests targeting `main` or `master`.

The workflow has three jobs:

1. `laravel-tests` starts MySQL, creates `fzs_testing`, migrates and seeds the application database, then runs PHPUnit with coverage.
2. `e2e` starts a clean MySQL service, installs Chromium, starts Laravel on port `8000`, and runs the Playwright suite with `E2E_BASE_URL` set to that server.
3. `lint` runs Pint and PHPStan.

The E2E job uses file sessions, array cache and the synchronous queue so it does
not require Redis in CI. The application-level queue job is covered by the
PHPUnit queue and job tests.

## Current verified baseline

The current local baseline is:

```text
PHPUnit: 1814 tests, 4640 assertions, 0 failures, 0 errors
Playwright: 39 E2E tests passed
```

When adding a new user-facing workflow, add a focused Playwright test and keep
business logic assertions in PHPUnit tests. Avoid relying on fixed database IDs;
select records from the seeded UI or create them through the tested workflow.
