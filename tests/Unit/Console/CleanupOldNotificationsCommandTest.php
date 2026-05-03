<?php

declare(strict_types=1);

namespace Tests\Unit\Console;

use App\Console\Commands\CleanupOldNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class CleanupOldNotificationsCommandTest extends TestCase
{
    public function test_handle_logs_and_reports_deleted_notifications(): void
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->once()->andReturnSelf();
        $builder->shouldReceive('delete')->once()->andReturn(5);

        DB::shouldReceive('table')->once()->with('notifications')->andReturn($builder);
        Log::shouldReceive('info')->once();

        $command = app(CleanupOldNotifications::class);
        $command->setLaravel($this->app);

        $output = new BufferedOutput;
        $exitCode = $command->run(new ArrayInput(['--days' => 30]), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Successfully deleted 5 notifications older than 30 days.', $output->fetch());
    }

    public function test_handle_reports_no_notifications_to_delete(): void
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->once()->andReturnSelf();
        $builder->shouldReceive('delete')->once()->andReturn(0);

        DB::shouldReceive('table')->once()->with('notifications')->andReturn($builder);
        Log::shouldReceive('info')->once();

        $command = app(CleanupOldNotifications::class);
        $command->setLaravel($this->app);

        $output = new BufferedOutput;
        $exitCode = $command->run(new ArrayInput(['--days' => 90]), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No notifications older than 90 days found.', $output->fetch());
    }

    public function test_handle_returns_error_code_when_exception_occurs(): void
    {
        DB::shouldReceive('table')->once()->with('notifications')->andThrow(new \Exception('db failure'));
        Log::shouldReceive('error')->once();

        $command = app(CleanupOldNotifications::class);
        $command->setLaravel($this->app);

        $output = new BufferedOutput;
        $exitCode = $command->run(new ArrayInput(['--days' => 7]), $output);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Error: db failure', $output->fetch());
    }
}
