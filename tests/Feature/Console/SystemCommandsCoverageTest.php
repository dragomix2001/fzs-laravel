<?php

namespace Tests\Feature\Console;

use App\Console\Commands\BackupDatabase;
use App\Console\Commands\CleanupOrphanedRecords;
use App\Console\Commands\QueueHealthCheck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class SystemCommandsCoverageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function ($table) {
                $table->id();
                $table->string('queue')->nullable();
                $table->longText('payload')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at')->nullable();
                $table->unsignedInteger('created_at')->nullable();
            });
        }
    }

    public function test_queue_health_check_passes_when_failed_jobs_are_below_threshold(): void
    {
        DB::table('failed_jobs')->truncate();

        $command = app(QueueHealthCheck::class);
        $command->setLaravel($this->app);

        $exitCode = $command->run(
            new ArrayInput(['--warn' => 10, '--fail' => 50]),
            new BufferedOutput
        );

        $this->assertSame(0, $exitCode);
    }

    public function test_queue_health_check_returns_warning_when_failed_jobs_exceed_warn_threshold(): void
    {
        DB::table('failed_jobs')->truncate();

        for ($i = 0; $i < 3; $i++) {
            DB::table('failed_jobs')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'warn',
                'failed_at' => now(),
            ]);
        }

        $command = app(QueueHealthCheck::class);
        $command->setLaravel($this->app);

        $exitCode = $command->run(
            new ArrayInput(['--warn' => 2, '--fail' => 10, '--verbose' => true]),
            new BufferedOutput
        );

        $this->assertSame(2, $exitCode);
    }

    public function test_queue_health_check_returns_failure_when_failed_jobs_exceed_fail_threshold(): void
    {
        DB::table('failed_jobs')->truncate();

        for ($i = 0; $i < 5; $i++) {
            DB::table('failed_jobs')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'fail',
                'failed_at' => now(),
            ]);
        }

        $command = app(QueueHealthCheck::class);
        $command->setLaravel($this->app);

        $exitCode = $command->run(
            new ArrayInput(['--warn' => 2, '--fail' => 4]),
            new BufferedOutput
        );

        $this->assertSame(1, $exitCode);
    }

    public function test_cleanup_orphaned_records_runs_in_dry_run_mode(): void
    {
        $command = app(CleanupOrphanedRecords::class);
        $command->setLaravel($this->app);

        $output = new BufferedOutput;
        $exitCode = $command->run(
            new ArrayInput(['--dry-run' => true]),
            $output
        );

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('DRY RUN MODE', $output->fetch());
    }

    public function test_cleanup_orphaned_records_runs_with_fix_nulls_option(): void
    {
        $command = app(CleanupOrphanedRecords::class);
        $command->setLaravel($this->app);

        $exitCode = $command->run(
            new ArrayInput(['--fix-nulls' => true]),
            new BufferedOutput
        );

        $this->assertSame(0, $exitCode);
    }

    public function test_backup_database_command_handles_backup_errors(): void
    {
        config()->set('database.connections.mysql.host', 'invalid-host.local');
        config()->set('database.connections.mysql.database', 'missing_db');

        $command = app(BackupDatabase::class);
        $command->setLaravel($this->app);

        $exitCode = $command->run(
            new ArrayInput([]),
            new BufferedOutput
        );

        $this->assertSame(1, $exitCode);
    }

    public function test_backup_database_format_bytes_converts_units_correctly(): void
    {
        $command = app(BackupDatabase::class);

        $reflection = new \ReflectionMethod($command, 'formatBytes');
        $reflection->setAccessible(true);

        $this->assertSame('1 KB', $reflection->invoke($command, 1024));
        $this->assertSame('1 MB', $reflection->invoke($command, 1048576));
    }
}
