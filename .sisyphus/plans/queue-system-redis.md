# Implement Queue System with Redis for Background Processing

## TL;DR

> **Quick Summary**: Implement a robust background job processing system using Redis as the queue driver to handle time-consuming operations asynchronously, improving application responsiveness and user experience.
> 
> **Deliverables**:
> - Redis queue configuration in .env and config/queue.php
> - Sample job classes for common operations (report generation, notifications, data exports)
> - Queue worker setup and supervision configuration
> - Updated cache and broadcasting configurations to use separate Redis connections
> - Documentation and testing procedures
> 
> **Estimated Effort**: Medium
> **Parallel Execution**: YES - 3 waves
> **Critical Path**: Configure Redis → Create sample jobs → Set up worker supervision

## Context

### Original Request
Analyze the FZS-Laravel application for optimization opportunities and implement a queue system with Redis for background processing.

### Interview Summary
**Key Discussions**:
- Application currently uses sync queue driver, causing HTTP requests to block during long operations
- Need to offload time-consuming tasks like report generation, bulk notifications, and data exports
- Redis is already installed and configured for caching, can be extended for queueing
- Want to maintain loose coupling and improve scalability

**Research Findings**:
- Laravel queues support multiple drivers including Redis, which is already available in the stack
- Redis driver provides good performance and reliability for queueing
- Proper supervision (using Supervisor or systemd) is required for production queue workers
- Need to ensure job serialization works with Laravel's model binding

### Metis Review
**Identified Gaps** (addressed):
- Missing queue worker supervision configuration - Added systemd service templates
- No sample job implementations - Created template jobs for common use cases
- Missing configuration for failed job handling - Added failed job table and monitoring
- No separation of concerns for different queue types - Implemented multiple queues for different priorities

## Work Objectives

### Core Objective
Implement a reliable background job processing system using Redis queue driver to decouple time-consuming operations from HTTP requests, improving application responsiveness and enabling horizontal scaling.

### Concrete Deliverables
- [ ] Redis queue driver configured in .env and config/queue.php
- [ ] Sample job classes for report generation, notifications, and data exports
- [ ] Queue worker systemd service configuration for production
- [ ] Local development queue worker setup instructions
- [ ] Failed job monitoring and retry mechanism
- [ ] Documentation on usage and best practices

### Definition of Done
- [ ] Queue system processes jobs without blocking HTTP requests
- [ ] At least 3 different job types are implemented and functional
- [ ] Workers can be started/stopped via systemd service
- [ ] Failed jobs are logged and can be retried
- [ ] Configuration separates queue connections from cache/broadcasting connections
- [ ] All changes are tested and working in local development environment

### Must Have
- Redis queue driver properly configured and tested
- Systemd service configuration for production deployment
- Clear documentation for developers and DevOps teams

### Must NOT Have (Guardrails)
- [Excluded from Metis review]
- Do not modify existing cache or broadcasting Redis connections - use separate connections
- Do not introduce breaking changes to existing functionality
- Do not use volatile memory storage for critical job data
- [AI slop pattern to avoid]
- Do not create overly complex job chains without clear error handling
- Do not neglect monitoring and alerting for queue health

## Verification Strategy

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.
> Acceptance criteria requiring "user manually tests/confirms" are FORBIDDEN.

### Test Decision
- **Infrastructure exists**: YES (Laravel testing framework, PHPUnit)
- **Automated tests**: TDD
- **Framework**: PHPUnit
- **If TDD**: Each task follows RED (failing test) → GREEN (minimal impl) → REFACTOR

### QA Policy
Every task MUST include agent-executed QA scenarios (see TODO template below).
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **Frontend/UI**: Use Playwright (playwright skill) — Navigate, interact, assert DOM, screenshot
- **TUI/CLI**: Use interactive_bash (tmux) — Run command, send keystrokes, validate output
- **API/Backend**: Use Bash (curl) — Send requests, assert status + response fields
- **Library/Module**: Use Bash (bun/node REPL) — Import, call functions, compare output

## Execution Strategy

### Parallel Execution Waves

> Maximize throughput by grouping independent tasks into parallel waves.
> Each wave completes before the next begins.
> Target: 5-8 tasks per wave. Fewer than 3 per wave (except final) = under-splitting.

```
Wave 1 (Start Immediately — foundation + configuration):
├── Task 1: Configure Redis queue driver in .env [quick]
├── Task 2: Update config/queue.php for separate queue connection [quick]
├── Task 3: Create migration for failed_jobs table [quick]
├── Task 4: Run migration to create failed_jobs table [quick]
├── Task 5: Create base job classes and interfaces [quick]
└── Task 6: Configure cache and broadcasting to use separate Redis connections [quick]

Wave 2 (After Wave 1 — job implementations, MAX PARALLEL):
├── Task 7: Create ReportGenerationJob [deep]
├── Task 8: Create BulkNotificationJob [deep]
├── Task 9: Create DataExportJob [deep]
├── Task 10: Create WelcomeEmailJob [unspecified-high]
├── Task 11: Create DatabaseCleanupJob [unspecified-high]
├── Task 12: Create QueueServiceFacade for easy job dispatching [quick]
└── Task 13: Update existing services to use queued operations [unspecified-high]

Wave 3 (After Wave 2 — supervision, testing, documentation):
├── Task 14: Create systemd service template for queue workers [deep]
├── Task 15: Create local development queue worker instructions [deep]
├── Task 16: Implement failed job monitoring command [deep]
├── Task 17: Write comprehensive documentation [deep]
├── Task 18: Create unit tests for job classes [deep]
└── Task 19: Create integration tests for queue processing [deep]

Wave FINAL (After ALL tasks — 2 parallel validations, then user okay):
├── Task F1: Verify queue system processes jobs correctly (integration test)
├── Task F2: Verify failed job handling and retry mechanism
-> Present results -> Get explicit user okay

Critical Path: Task 1 → Task 2 → Task 3 → Task 4 → Task 5 → Task 14 → F1-F2 → user okay
Parallel Speedup: ~65% faster than sequential
Max Concurrent: 5 (Waves 1 & 2)
```

### Dependency Matrix (abbreviated — show ALL tasks in your generated plan)

- **1-6**: — — 7-19, 1
- **7**: 1, 2, 5 — 10, 13, 2
- **10**: 2, 5, 7 — 13, 3
- **13**: 2, 5, 7, 10 — 15, 4
- **15**: 13 — 16-19, 5
- **19**: 15 — 20, 6

> This is abbreviated for reference. YOUR generated plan must include the FULL matrix for ALL tasks.

### Agent Dispatch Summary

- **1**: **5** — T1-T3 → `quick`, T4 → `quick`, T5 → `quick`, T6 → `quick`
- **2**: **5** — T7-T9 → `deep`, T10-T11 → `unspecified-high`, T12 → `quick`, T13 → `unspecified-high`
- **3**: **4** — T14 → `deep`, T15 → `deep`, T16 → `deep`, T17 → `deep`
- **FINAL**: **2** — F1 → `deep`, F2 → `deep`

---

## TODOs

> Implementation + Test = ONE Task. Never separate.
> EVERY task MUST have: Recommended Agent Profile + Parallelization info + QA Scenarios.
> **A task WITHOUT QA Scenarios is INCOMPLETE. No exceptions.**

- [ ] 1. Configure Redis queue driver in .env

  **What to do**:
  - Set QUEUE_CONNECTION=redis in .env
  - Add REDIS_QUEUE_CONNECTION=default (or separate connection if needed)
  - Verify .env syntax is correct

  **Must NOT do**:
  - Do not overwrite existing REDIS_HOST, REDIS_PORT, REDIS_PASSWORD settings
  - Do not modify CACHE_DRIVER or BROADCAST_DRIVER settings

  **Recommended Agent Profile**:
  > Select category + skills based on task domain. Justify each choice.
  - **Category**: `quick`
    - Reason: Simple environment variable configuration task
  - **Skills**: [`env-config`]
    - `env-config`: Domain expertise in environment variable management and Laravel configuration patterns
  - **Skills Evaluated but Omitted**:
    - `database-admin`: Not needed as we're not modifying database structure
    - `devops`: Not needed as this is purely configuration, not deployment

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3, 4, 5, 6)
  - **Blocks**: [Tasks that depend on this task completing: 2, 7, 8, 9, 10, 11, 12, 13]
  - **Blocked By**: None (can start immediately)

  **References** (CRITICAL - Be Exhaustive):

  > The executor has NO context from your interview. References are their ONLY guide.
  > Each reference must answer: "What should I look at and WHY?"

  **Pattern References** (existing code to follow):
  - `/home/dragomix/fzs-laravel/.env:27-29` - Existing Redis configuration pattern (REDIS_HOST, REDIS_PORT, REDIS_PASSWORD)
  - `/home/dragomix/fzs-laravel/.env:18-19` - Existing queue configuration pattern (QUEUE_CONNECTION)

  **API/Type References** (contracts to implement against):
  - `Illuminate\Contracts\Queue\Factory` - Queue factory contract for dependency injection
  - `Illuminate\Contracts\Queue\Queue` - Queue interface for implementation details

  **Test References** (testing patterns to follow):
  - `/home/dragomix/fzs-laravel/tests/TestCase.php:10-15` - Base test case setup pattern
  - `/home/dragomix/fzs-laravel/tests/Feature/ExampleTest.php:10-20` - Feature test pattern

  **External References** (libraries and frameworks):
  - Laravel Queues Documentation: `https://laravel.com/docs/13.x/queues` - Official queue documentation
  - Redis Queue Driver: `https://laravel.com/docs/13.x/queues#redis-queue` - Specific Redis queue driver docs

  **WHY Each Reference Matters** (explain the relevance):
  - Don't just list files - explain what pattern/information the executor should extract
  - Bad: `.env` (vague, which vars? why?)
  - Good: `/home/dragomix/fzs-laravel/.env:27-29` - Use this existing Redis configuration as template for adding queue-specific settings
  - Good: `/home/dragomix/fzs-laravel/.env:18-19` - Follow this pattern for QUEUE_CONNECTION variable

  **Acceptance Criteria**:

  > **AGENT-EXECUTABLE VERIFICATION ONLY** — No human action permitted.
  > Every criterion MUST be verifiable by running a command or using a tool.

  **If TDD (tests enabled):**
  - [ ] Test file created: tests/Queue/QueueConfigurationTest.php
  - [ ] phpunit tests/Queue/QueueConfigurationTest.php → PASS (2 tests, 0 failures)

  **QA Scenarios (MANDATORY — task is INCOMPLETE without these):**

  > **This is NOT optional. A task without QA scenarios WILL BE REJECTED.**
  >
  > Write scenario tests that verify the ACTUAL BEHAVIOR of what you built.
  > Minimum: 1 happy path + 1 failure/edge case per task.
  > Each scenario = exact tool + exact steps + exact assertions + evidence path.
  >
  > **The executing agent MUST run these scenarios after implementation.**
  > **The orchestrator WILL verify evidence files exist before marking task complete.**

  ```
  Scenario: [Happy path — what SHOULD work]
    Tool: [Bash (grep)]
    Preconditions: [.env file exists at /home/dragomix/fzs-laravel/.env]
    Steps:
      1. [Exact action — grep for QUEUE_CONNECTION in .env file]
      2. [Next action — verify it's set to redis]
      3. [Assertion — exact expected value "QUEUE_CONNECTION=redis"]
    Expected Result: [Concrete, observable, binary pass/fail]
    Failure Indicators: [What specifically would mean this failed — grep returns no results or wrong value]
    Evidence: .sisyphus/evidence/task-1-queue-connection-setting.txt

  Scenario: [Failure/edge case — what SHOULD fail gracefully]
    Tool: [same format]
    Preconditions: [.env file exists]
    Steps:
      1. [Trigger the error condition — attempt to set QUEUE_CONNECTION to invalid value]
      2. [Assert error is handled correctly — Laravel should throw clear error during boot]
    Expected Result: [Graceful failure with clear error message about invalid queue connection]
    Evidence: .sisyphus/evidence/task-1-invalid-queue-connection-error.txt
  ```

  **Evidence to Capture:**
  - [ ] Each evidence file named: task-{N}-{scenario-slug}.{ext}
  - [ ] Screenshots for UI, terminal output for CLI, response bodies for API

  **Commit**: YES | NO (groups with N)
  - Message: `type(scope): desc`
  - Files: `path/to/file`
  - Pre-commit: `test command`
