<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\ImportExportController;
use App\Http\Requests\ImportFileRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportExportControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_returns_view(): void
    {
        $controller = new ImportExportController;
        $response = $controller->index();

        $this->assertSame('import-export.index', $response->name());
    }

    public function test_export_returns_excel_download_default_format(): void
    {
        Excel::fake();

        $controller = new ImportExportController;
        $request = Request::create('/import-export/export', 'GET');

        $controller->export($request);

        Excel::matchByRegex();
        Excel::assertDownloaded('/kandidati_.*\.xlsx$/');
    }

    public function test_export_accepts_csv_format(): void
    {
        Excel::fake();

        $controller = new ImportExportController;
        $request = Request::create('/import-export/export', 'GET', ['format' => 'csv']);

        $controller->export($request);

        Excel::matchByRegex();
        Excel::assertDownloaded('/kandidati_.*\.csv$/');
    }

    public function test_export_studenti_returns_excel_download(): void
    {
        Excel::fake();

        $controller = new ImportExportController;
        $request = Request::create('/import-export/export-studenti', 'GET');

        $controller->exportStudenti($request);

        Excel::matchByRegex();
        Excel::assertDownloaded('/studenti_.*\.xlsx$/');
    }

    public function test_export_polozeni_ispiti_returns_excel_download(): void
    {
        Excel::fake();

        $controller = new ImportExportController;
        $request = Request::create('/import-export/export-ispiti', 'GET');

        $controller->exportPolozeniIspiti($request);

        Excel::matchByRegex();
        Excel::assertDownloaded('/polozeni_ispiti_.*\.xlsx$/');
    }

    public function test_import_handles_exception_gracefully(): void
    {
        Excel::shouldReceive('import')
            ->once()
            ->andThrow(new \Exception('Import failed'));

        // Create request directly via static create (bypasses FormRequest
        // container resolution which would trigger validation and throw)
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('kandidati.xlsx');
        $request = ImportFileRequest::create(
            '/import-export/import',
            'POST',
            [],
            [],
            ['file' => $fakeFile]
        );

        $controller = new ImportExportController;
        $response = $controller->import($request);

        $this->assertNotNull($response);
    }
}
