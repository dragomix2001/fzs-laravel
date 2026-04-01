<?php

namespace App\Console\Commands;

use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveCompletedZapisnici extends Command
{
    protected $signature = 'zapisnici:archive {--months=6 : Number of months before archiving}';

    protected $description = 'Archive completed exam records (zapisnici) older than specified months';

    public function handle()
    {
        try {
            $months = $this->option('months');
            $cutoffDate = now()->subMonths($months);

            $archivedCount = ZapisnikOPolaganjuIspita::where('datum', '<', $cutoffDate)
                ->where('arhiviran', false)
                ->update(['arhiviran' => true]);

            if ($archivedCount > 0) {
                Log::info("Zapisnici archive completed: {$archivedCount} exam records archived (older than {$months} months)");
                $this->info("Successfully archived {$archivedCount} exam records older than {$months} months.");
            } else {
                Log::info("Zapisnici archive completed: no records to archive (older than {$months} months)");
                $this->info("No exam records older than {$months} months found to archive.");
            }

            return 0;
        } catch (\Exception $e) {
            Log::error("Zapisnici archive error: " . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
