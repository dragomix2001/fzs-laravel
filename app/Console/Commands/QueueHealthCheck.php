<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:health-check
                            {--verbose : Show detailed output}
                            {--warn= : Warning threshold for failed jobs count (default: 10)}
                            {--fail= : Failure threshold for failed jobs count (default: 50)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of the queue system and report metrics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $verbose = $this->option('verbose');
        $warnThreshold = (int) $this->option('warn') ?: 10;
        $failThreshold = (int) $this->option('fail') ?: 50;

        $this->info('🔍 Checking Queue System Health...');
        $this->newLine();

        // Check failed jobs count
        $failedCount = DB::table('failed_jobs')->count();
        
        // Check queue tables exist
        $tablesExist = $this->checkQueueTables();
        
        // Check if queue worker is running (basic check)
        $workerRunning = $this->checkWorkerStatus();
        
        // Get queue connection info
        $connection = config('queue.default', 'database');
        
        // Determine health status
        $status = self::SUCCESS;
        $statusText = 'HEALTHY';
        
        if ($failedCount >= $failThreshold) {
            $status = self::FAILURE;
            $statusText = 'UNHEALTHY';
        } elseif ($failedCount >= $warnThreshold) {
            $status = 2; // Using INVALID constant from Symfony Command as WARNING equivalent
            $statusText = 'WARNING';
        }
        
        if (!$tablesExist) {
            $status = self::FAILURE;
            $statusText = 'UNHEALTHY';
        }
        
        // Output results
        $this->line('📊 Queue Health Report');
        $this->line(str_repeat('─', 40));
        $this->info("Status: {$statusText}");
        
        if ($verbose || $status !== self::SUCCESS) {
            $this->newLine();
            $this->text('Failed Jobs: <fg=yellow>'.$failedCount.'</>');
            
            if ($failedCount >= $warnThreshold) {
                $this->warning("  ⚠️  Above warning threshold ({$warnThreshold})");
            }
            if ($failedCount >= $failThreshold) {
                $this->error("  ❌  Above failure threshold ({$failThreshold})");
            }
            
            $this->newLine();
            $this->text("Queue Connection: <fg=cyan>{$connection}</>");
            $this->newLine();
            $this->text("Queue Tables: ".($tablesExist ? '<fg=green>OK</>' : '<fg=red>MISSING</>'));
            $this->newLine();
            $this->text("Worker Running: ".($workerRunning ? '<fg=green>YES</>' : '<fg=red>NO</>'));
        }
        
        // Log to application log
        Log::info('Queue health check completed', [
            'status' => $statusText,
            'failed_jobs' => $failedCount,
            'connection' => $connection,
            'tables_exist' => $tablesExist,
            'worker_running' => $workerRunning,
        ]);
        
        // Return appropriate exit code
        if ($status === self::FAILURE) {
            $this->error('❌ Queue health check FAILED');
            return self::FAILURE;
        } elseif ($status === 2) {
            $this->warning('⚠️  Queue health check WARNING');
            return 2; // Using INVALID constant from Symfony Command as WARNING equivalent
        } else {
            $this->info('✅ Queue health check PASSED');
            return self::SUCCESS;
        }
    }

    /**
     * Check if queue tables exist in database.
     */
    protected function checkQueueTables(): bool
    {
        try {
            $tables = ['jobs', 'failed_jobs'];
            foreach ($tables as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    return false;
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if queue worker appears to be running.
     * This is a basic check - in production you'd use proper process monitoring.
     */
    protected function checkWorkerStatus(): bool
    {
        // Check if there are any recent jobs processed (last 5 minutes)
        try {
            $recentJobs = DB::table('jobs')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->count();
                
            // Or check for recently failed jobs (indicates worker is processing)
            $recentFailed = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subMinutes(5))
                ->count();
                
            return ($recentJobs > 0 || $recentFailed > 0);
        } catch (\Exception $e) {
            return false;
        }
    }
}