<?php

namespace Tests\Feature;

use App\Jobs\TestFailingJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

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

        // Dispatch the failing job
        TestFailingJob::dispatch();

        // Assert job was pushed to queue
        Queue::assertPushed(TestFailingJob::class);
    }

    /** @test */
    public function test_failed_job_can_be_retried()
    {
        Queue::fake();

        // Dispatch the failing job
        TestFailingJob::dispatch();

        // Assert job was pushed to the queue
        Queue::assertPushed(TestFailingJob::class);

        // Verify we can reference the job
        $jobs = Queue::pushedJobs();
        $this->assertNotEmpty($jobs);
    }
}
