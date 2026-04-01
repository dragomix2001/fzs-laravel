<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        Commands\FailedJobsMonitor::class,
        Commands\CleanupOrphanedRecords::class,
        Commands\BackupDatabase::class,
        Commands\CleanupOldNotifications::class,
        Commands\ArchiveCompletedZapisnici::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Daily database backup at 2 AM
        $schedule->command('backup:run --compress')
                 ->dailyAt('02:00');

        // Weekly notification cleanup (Sundays at 3 AM)
        $schedule->command('notifications:cleanup')
                 ->weekly()
                 ->sundays()
                 ->at('03:00');

        // Monthly zapisnik archive (1st of month at 4 AM)
        $schedule->command('zapisnici:archive')
                 ->monthlyOn(1, '04:00');
    }
}
