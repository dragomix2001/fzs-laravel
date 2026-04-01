<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=90 : Number of days to keep notifications}';

    protected $description = 'Delete notifications older than specified days';

    public function handle()
    {
        try {
            $days = $this->option('days');
            $cutoffDate = now()->subDays($days);

            $deletedCount = DB::table('notifications')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Notifications cleanup completed: {$deletedCount} old notifications deleted (older than {$days} days)");
                $this->info("Successfully deleted {$deletedCount} notifications older than {$days} days.");
            } else {
                Log::info("Notifications cleanup completed: no notifications to delete (older than {$days} days)");
                $this->info("No notifications older than {$days} days found.");
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Notifications cleanup error: '.$e->getMessage());
            $this->error('Error: '.$e->getMessage());

            return 1;
        }
    }
}
