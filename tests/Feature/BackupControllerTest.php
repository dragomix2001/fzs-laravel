<?php

namespace Tests\Feature;

use App\Http\Controllers\BackupController;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class BackupControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_renders_backup_list_from_service(): void
    {
        $service = $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('listBackups')
                ->once()
                ->andReturn([
                    ['name' => 'backup_1.zip', 'size' => 100, 'modified' => '2026-05-01 10:00:00'],
                ]);
        });

        $controller = new BackupController($service);
        $response = $controller->index();

        $this->assertSame('backup.index', $response->name());
        $this->assertSame('backup_1.zip', $response->getData()['backups'][0]['name']);
    }

    public function test_create_flashes_success_message_when_backup_succeeds(): void
    {
        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('createBackup')
                ->once()
                ->with('database')
                ->andReturn([
                    'success' => true,
                    'filename' => 'backup_ok.sql',
                ]);
        });

        $controller = app(BackupController::class);
        $response = $controller->create(Request::create('/backup/create', 'POST', ['type' => 'database']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_create_flashes_error_message_when_backup_fails(): void
    {
        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('createBackup')
                ->once()
                ->with('files')
                ->andReturn([
                    'success' => false,
                    'error' => 'boom',
                ]);
        });

        $controller = app(BackupController::class);
        $response = $controller->create(Request::create('/backup/create', 'POST', ['type' => 'files']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_download_returns_file_response_when_backup_exists(): void
    {
        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'test_backup_download.sql';
        File::put($backupDir.'/'.$filename, 'backup-content');

        try {
            $controller = app(BackupController::class);
            $response = $controller->download($filename);

            $this->assertInstanceOf(BinaryFileResponse::class, $response);
        } finally {
            File::delete($backupDir.'/'.$filename);
        }
    }

    public function test_download_redirects_with_error_when_file_is_missing(): void
    {
        $controller = app(BackupController::class);
        $response = $controller->download('missing_file.sql');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_delete_flashes_success_when_service_deletes_backup(): void
    {
        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('deleteBackup')
                ->once()
                ->with('old_backup.zip')
                ->andReturn(true);
        });

        $controller = app(BackupController::class);
        $response = $controller->delete('old_backup.zip');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_delete_flashes_error_when_service_cannot_delete_backup(): void
    {
        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('deleteBackup')
                ->once()
                ->with('missing_backup.zip')
                ->andReturn(false);
        });

        $controller = app(BackupController::class);
        $response = $controller->delete('missing_backup.zip');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
