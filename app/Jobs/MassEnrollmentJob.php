<?php

namespace App\Jobs;

use App\Services\UpisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MassEnrollmentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public array $kandidatIds
    ) {}

    public function handle(UpisService $upisService): void
    {
        foreach ($this->kandidatIds as $kandidatId) {
            try {
                $upisService->registrujKandidata($kandidatId);
            } catch (\Exception $e) {
                Log::error("Масовни упис није успео за кандидата {$kandidatId}", [
                    'kandidat_id' => $kandidatId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Масовни упис кандидата није успео', [
            'kandidat_ids_count' => count($this->kandidatIds),
            'kandidat_ids' => $this->kandidatIds,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
