<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\Console\Commands\Inspire;
use App\Console\Kernel as AppConsoleKernel;
use App\Http\Middleware\TrustHosts;
use App\Providers\BroadcastServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LegacyCoreCoverageTest extends TestCase
{
    #[Test]
    public function inspire_command_handle_executes_successfully(): void
    {
        $command = new Inspire;
        $command->setLaravel($this->app);

        $result = $command->run(new \Symfony\Component\Console\Input\ArrayInput([]), new \Symfony\Component\Console\Output\BufferedOutput);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function console_kernel_schedule_registers_expected_commands(): void
    {
        $kernel = new class($this->app, $this->app['events']) extends AppConsoleKernel
        {
            public function exposeSchedule(Schedule $schedule): void
            {
                $this->schedule($schedule);
            }
        };

        $schedule = new Schedule(config('app.timezone'));
        $kernel->exposeSchedule($schedule);

        $events = $schedule->events();
        $this->assertCount(3, $events);
    }

    #[Test]
    public function broadcast_service_provider_boot_executes(): void
    {
        $provider = new BroadcastServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue(true);
    }

    #[Test]
    public function trust_hosts_returns_single_pattern_array(): void
    {
        $middleware = new TrustHosts($this->app);

        $hosts = $middleware->hosts();

        $this->assertCount(1, $hosts);
    }
}
