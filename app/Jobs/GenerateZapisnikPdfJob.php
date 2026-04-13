<?php

namespace App\Jobs;

use App\DTOs\ZapisnikStampaData;
use App\Services\IspitPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateZapisnikPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(
        public int $zapisnikId,
        public string $storagePath
    ) {}

    public function handle(IspitPdfService $ispitPdfService): void
    {
        $data = new ZapisnikStampaData($this->zapisnikId, null, null, null);

        ob_start();
        $ispitPdfService->zapisnikStampa($data);
        $pdfOutput = ob_get_clean();

        Storage::disk('local')->put($this->storagePath, $pdfOutput);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Генерисање PDF записника није успело', [
            'zapisnik_id' => $this->zapisnikId,
            'storage_path' => $this->storagePath,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
