<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestFailingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Always fail to test failed job handling
        throw new Exception('This job is designed to fail for testing purposes');
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        // Log the failure or perform cleanup if needed
        Log::error('TestFailingJob failed: '.$exception->getMessage());
    }
}
