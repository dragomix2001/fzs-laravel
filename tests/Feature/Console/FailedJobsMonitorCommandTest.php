<?php

namespace Tests\Feature\Console;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Tests\TestCase;

class FailedJobsMonitorCommandTest extends TestCase
{
    private InMemoryFailedJobProvider $failer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->failer = new InMemoryFailedJobProvider;
        $this->app->instance('queue.failer', $this->failer);
    }

    public function test_list_action_displays_paginated_filtered_rows(): void
    {
        $this->failer->seed([
            [
                'id' => 1,
                'uuid' => 'job-1',
                'connection' => 'database',
                'queue' => 'emails',
                'payload' => json_encode(['displayName' => 'SendInvoice']),
                'exception' => 'Timeout',
                'failed_at' => '2026-05-01 10:00:00',
            ],
            [
                'id' => 2,
                'uuid' => 'job-2',
                'connection' => 'redis',
                'queue' => 'default',
                'payload' => json_encode(['job' => 'Cleanup']),
                'exception' => 'Error',
                'failed_at' => '2026-05-01 11:00:00',
            ],
        ]);

        $this->artisan('failed-jobs:monitor', [
            'action' => 'list',
            '--connection' => 'database',
            '--queue' => 'emails',
            '--search' => 'invoice',
            '--page' => 1,
            '--per-page' => 10,
        ])
            ->expectsOutputToContain('Showing page 1 of 1 (1 total failed jobs).')
            ->assertSuccessful();
    }

    public function test_list_action_handles_empty_results(): void
    {
        $this->artisan('failed-jobs:monitor', [
            'action' => 'list',
        ])
            ->expectsOutputToContain('No failed jobs found for the selected filters/page.')
            ->assertSuccessful();
    }

    public function test_show_requires_id(): void
    {
        $this->artisan('failed-jobs:monitor', [
            'action' => 'show',
        ])
            ->expectsOutputToContain('The {id} argument is required for the show action.')
            ->assertFailed();
    }

    public function test_show_returns_not_found_when_id_does_not_exist(): void
    {
        $this->artisan('failed-jobs:monitor', [
            'action' => 'show',
            'id' => 'missing-id',
        ])
            ->expectsOutputToContain('Failed job [missing-id] not found.')
            ->assertFailed();
    }

    public function test_show_displays_job_details(): void
    {
        $this->failer->seed([
            [
                'id' => 7,
                'uuid' => 'job-7',
                'connection' => 'database',
                'queue' => 'imports',
                'payload' => json_encode(['data' => ['commandName' => 'App\\Jobs\\ImportData']]),
                'exception' => 'Boom',
                'failed_at' => '2026-05-02 10:00:00',
            ],
        ]);

        $this->artisan('failed-jobs:monitor', [
            'action' => 'show',
            'id' => 'job-7',
        ])
            ->expectsOutputToContain('Failed Job Details')
            ->expectsOutputToContain('Job        : App\\Jobs\\ImportData')
            ->assertSuccessful();
    }

    public function test_delete_requires_id(): void
    {
        $this->artisan('failed-jobs:monitor', [
            'action' => 'delete',
        ])
            ->expectsOutputToContain('The {id} argument is required for the delete action.')
            ->assertFailed();
    }

    public function test_delete_returns_not_found_for_missing_job(): void
    {
        $this->artisan('failed-jobs:monitor', [
            'action' => 'delete',
            'id' => 'x-1',
        ])
            ->expectsOutputToContain('Failed job [x-1] not found or could not be deleted.')
            ->assertFailed();
    }

    public function test_delete_removes_existing_failed_job(): void
    {
        $this->failer->seed([
            [
                'id' => 9,
                'uuid' => 'job-9',
                'connection' => 'database',
                'queue' => 'default',
                'payload' => json_encode(['displayName' => 'DeleteMe']),
                'exception' => 'x',
                'failed_at' => '2026-05-02 11:00:00',
            ],
        ]);

        $this->artisan('failed-jobs:monitor', [
            'action' => 'delete',
            'id' => 'job-9',
        ])
            ->expectsOutputToContain('Deleted failed job [job-9].')
            ->assertSuccessful();
    }

    public function test_clear_action_flushes_all_jobs(): void
    {
        $this->failer->seed([
            ['id' => 1, 'uuid' => 'job-1', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'x', 'failed_at' => '2026-05-02 12:00:00'],
        ]);

        $this->artisan('failed-jobs:monitor', [
            'action' => 'clear',
        ])
            ->expectsOutputToContain('All failed jobs have been removed from the failed jobs table.')
            ->assertSuccessful();

        $this->assertCount(0, $this->failer->all());
    }

    public function test_invalid_action_returns_failure(): void
    {
        $this->artisan('failed-jobs:monitor', [
            'action' => 'nonsense',
        ])
            ->expectsOutputToContain('Invalid action. Allowed actions: list, show, retry, retry-all, delete, delete-all, clear.')
            ->assertFailed();
    }
}

class InMemoryFailedJobProvider implements FailedJobProviderInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $jobs = [];

    /**
     * @param  array<int, array<string, mixed>>  $jobs
     */
    public function seed(array $jobs): void
    {
        $this->jobs = $jobs;
    }

    public function log($connection, $queue, $payload, $exception)
    {
        $id = count($this->jobs) + 1;
        $uuid = 'job-'.$id;

        $this->jobs[] = [
            'id' => $id,
            'uuid' => $uuid,
            'connection' => (string) $connection,
            'queue' => (string) $queue,
            'payload' => (string) $payload,
            'exception' => (string) $exception->getMessage(),
            'failed_at' => now()->toDateTimeString(),
        ];

        return $uuid;
    }

    public function ids($queue = null)
    {
        return array_map(fn (array $job) => $job['uuid'] ?? $job['id'], $this->jobs);
    }

    public function all()
    {
        return array_map(fn (array $job) => (object) $job, $this->jobs);
    }

    public function find($id)
    {
        foreach ($this->jobs as $job) {
            if (($job['uuid'] ?? null) === $id || (string) ($job['id'] ?? '') === (string) $id) {
                return (object) $job;
            }
        }

        return null;
    }

    public function forget($id)
    {
        foreach ($this->jobs as $index => $job) {
            if (($job['uuid'] ?? null) === $id || (string) ($job['id'] ?? '') === (string) $id) {
                unset($this->jobs[$index]);
                $this->jobs = array_values($this->jobs);

                return true;
            }
        }

        return false;
    }

    public function flush($hours = null)
    {
        $this->jobs = [];
    }
}
