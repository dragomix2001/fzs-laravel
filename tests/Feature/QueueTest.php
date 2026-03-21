<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\TestFailingJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Bus;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_queue_can_dispatch_job()
    {
        Queue::fake();

        // Dispatch a job
        TestFailingJob::dispatch();

        // Assert the job was pushed to the queue
        Queue::assertPushed(TestFailingJob::class);
    }

    /** @test */
    public function test_failing_job_goes_to_failed_queue_after_max_attempts()
    {
        Queue::fake();

        // Configure queue to fail quickly for testing
        config(['queue.default' => 'database']);
        
        // Dispatch the failing job
        TestFailingJob::dispatch();

        // Process the queue
        $this->artisan('queue:work', ['--once' => true, '--sleep' => 0]);

        // Check that job exists in failed jobs table
        $this->assertDatabaseHas('failed_jobs', [
            'payload' => $this->stringContains('App\\Jobs\\TestFailingJob'),
        ]);
    }

    /** @test */
    public function test_failed_job_can_be_retried()
    {
        Queue::fake();

        // Dispatch the failing job
        TestFailingJob::dispatch();

        // Process the queue to make it fail
        $this->artisan('queue:work', ['--once' => true, '--sleep' => 0]);

        // Check it's in failed jobs
        $this->assertDatabaseHas('failed_jobs', [
            'payload' => $this->stringContains('App\\Jobs\\TestFailingJob'),
        ]);

        // Retry the failed job
        $this->artisan('queue:retry', ['all' => true]);

        // Check it's back on the queue (not in failed)
        Queue::assertPushed(TestFailingJob::class);
    }
}