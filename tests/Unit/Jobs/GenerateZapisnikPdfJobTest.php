<?php

namespace Tests\Unit\Jobs;

use App\DTOs\ZapisnikStampaData;
use App\Jobs\GenerateZapisnikPdfJob;
use App\Services\IspitPdfService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class GenerateZapisnikPdfJobTest extends TestCase
{
    public function test_job_generates_pdf_output_and_stores_it(): void
    {
        Storage::fake('local');

        $pdfService = Mockery::mock(IspitPdfService::class);
        $pdfService->shouldReceive('zapisnikStampa')
            ->once()
            ->with(Mockery::on(function (ZapisnikStampaData $data): bool {
                return $data->zapisnikId === 42;
            }))
            ->andReturnUsing(function (): void {
                echo '%PDF-demo';
            });

        $path = 'pdfs/zapisnik_42_test.pdf';
        (new GenerateZapisnikPdfJob(42, $path))->handle($pdfService);

        Storage::disk('local')->assertExists($path);
        $this->assertSame('%PDF-demo', Storage::disk('local')->get($path));
    }
}
