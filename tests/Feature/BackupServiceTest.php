<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    private MockInterface $backupServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a partial mock of BackupService to mock protected methods
        $this->backupServiceMock = Mockery::mock(BackupService::class, function ($mock) {
            $mock->makePartial();
            $mock->shouldAllowMockingProtectedMethods();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ======================== createBackup() Tests ========================

    /**
     * Test createBackup() with 'full' type - both database and files
     */
    public function test_create_backup_full_type_calls_both_methods(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('string'))
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('string'))
            ->andReturn('path/to/backup_files.zip');

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('full');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertStringContainsString('backup_', $result['filename']);
        $this->assertStringContainsString('_full', $result['filename']);
    }

    /**
     * Test createBackup() with 'database' type - only database backup
     */
    public function test_create_backup_database_type_only_calls_backup_database(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('string'))
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->never();

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('database');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertStringContainsString('_database', $result['filename']);
    }

    /**
     * Test createBackup() with 'files' type - only files backup
     */
    public function test_create_backup_files_type_only_calls_backup_files(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->never();

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('string'))
            ->andReturn('path/to/backup_files.zip');

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('files');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertStringContainsString('_files', $result['filename']);
    }

    /**
     * Test createBackup() returns correct response structure on success
     */
    public function test_create_backup_returns_correct_structure(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->andReturn('path/to/backup_files.zip');

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('full');

        // Assert response structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertTrue($result['success']);
        $this->assertIsString($result['filename']);
        $this->assertIsString($result['path']);
    }

    /**
     * Test createBackup() creates directory if it doesn't exist
     */
    public function test_create_backup_creates_directory_if_not_exists(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->andReturn('path/to/backup_files.zip');

        // Mock File facade - directory doesn't exist
        File::shouldReceive('exists')
            ->once()
            ->andReturn(false);

        File::shouldReceive('makeDirectory')
            ->once()
            ->with(Mockery::type('string'), 0755, true);

        // Execute
        $result = $this->backupServiceMock->createBackup('full');

        // Assert
        $this->assertTrue($result['success']);
    }

    /**
     * Test createBackup() handles exceptions gracefully
     */
    public function test_create_backup_handles_exception_from_backup_database(): void
    {
        // Mock the protected methods to throw exception
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->once()
            ->andThrow(new \Exception('Database backup failed'));

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('full');

        // Assert exception handling
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Database backup failed', $result['error']);
    }

    /**
     * Test createBackup() handles exception from backup files
     */
    public function test_create_backup_handles_exception_from_backup_files(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->once()
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->once()
            ->andThrow(new \Exception('Files backup failed'));

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('full');

        // Assert exception handling
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Files backup failed', $result['error']);
    }

    /**
     * Test createBackup() with default type (should be 'full')
     */
    public function test_create_backup_default_type_is_full(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->once()
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->once()
            ->andReturn('path/to/backup_files.zip');

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute without type parameter
        $result = $this->backupServiceMock->createBackup();

        // Assert - filename should contain 'full'
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('_full', $result['filename']);
    }

    /**
     * Test createBackup() filename contains timestamp format
     */
    public function test_create_backup_filename_contains_timestamp(): void
    {
        // Mock the protected methods
        $this->backupServiceMock
            ->shouldReceive('backupDatabase')
            ->andReturn('path/to/backup_db.sql');

        $this->backupServiceMock
            ->shouldReceive('backupFiles')
            ->andReturn('path/to/backup_files.zip');

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        // Execute
        $result = $this->backupServiceMock->createBackup('full');

        // Assert filename format: backup_Y-m-d_His_type
        $this->assertStringStartsWith('backup_', $result['filename']);
        $this->assertStringEndsWith('_full', $result['filename']);
        // Check for date format YYYY-MM-DD
        $this->assertMatchesRegularExpression('/backup_\d{4}-\d{2}-\d{2}_\d{6}_/', $result['filename']);
    }

    /**
     * Test listBackups() returns empty array when no backups exist
     */
    public function test_list_backups_returns_empty_array_when_no_backups(): void
    {
        $service = new BackupService;

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(false);

        // Execute
        $result = $service->listBackups();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test deleteBackup() deletes file when it exists
     */
    public function test_delete_backup_deletes_file_when_exists(): void
    {
        $service = new BackupService;

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(true);

        File::shouldReceive('delete')
            ->once()
            ->with(Mockery::type('string'));

        // Execute
        $result = $service->deleteBackup('backup_2024-01-01_120000_full');

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test deleteBackup() returns false when file doesn't exist
     */
    public function test_delete_backup_returns_false_when_not_exists(): void
    {
        $service = new BackupService;

        // Mock File facade
        File::shouldReceive('exists')
            ->once()
            ->andReturn(false);

        // Execute
        $result = $service->deleteBackup('non_existent_backup');

        // Assert
        $this->assertFalse($result);
    }

    public function test_backup_database_local_host_creates_sql_file_and_uses_password_flag(): void
    {
        $service = new class extends BackupService
        {
            public function callBackupDatabase(string $path, string $filename): string
            {
                return $this->backupDatabase($path, $filename);
            }
        };

        $tempDir = storage_path('app/backups/test-local-'.uniqid());
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $binDir = storage_path('app/backups/bin-local-'.uniqid());
        mkdir($binDir, 0755, true);

        $argsFile = $tempDir.'/mysqldump_args.txt';
        $scriptPath = $binDir.'/mysqldump';
        file_put_contents($scriptPath, "#!/usr/bin/env bash\nprintf '%s\n' \"$@\" > \"\$MYSQDUMP_ARGS_FILE\"\necho '-- dump'\n");
        chmod($scriptPath, 0755);

        $oldPath = getenv('PATH') ?: '';
        putenv('PATH='.$binDir.':'.$oldPath);
        putenv('MYSQDUMP_ARGS_FILE='.$argsFile);

        config()->set('database.connections.mysql.host', '127.0.0.1');
        config()->set('database.connections.mysql.username', 'root');
        config()->set('database.connections.mysql.password', 'secret');
        config()->set('database.connections.mysql.database', 'fzs_testing');

        try {
            $sqlFile = $service->callBackupDatabase($tempDir, 'legacy_local');

            $this->assertFileExists($sqlFile);
            $this->assertStringContainsString('-- dump', (string) file_get_contents($sqlFile));
            $args = (string) file_get_contents($argsFile);
            $this->assertStringContainsString('-uroot', $args);
            $this->assertStringContainsString('-psecret', $args);
            $this->assertStringContainsString('fzs_testing', $args);
        } finally {
            putenv('PATH='.$oldPath);
            putenv('MYSQDUMP_ARGS_FILE');
            @unlink($argsFile);
            @unlink($sqlFile ?? '');
            @rmdir($binDir);
            @rmdir($tempDir);
        }
    }

    public function test_backup_database_remote_host_uses_h_flag_without_password(): void
    {
        $service = new class extends BackupService
        {
            public function callBackupDatabase(string $path, string $filename): string
            {
                return $this->backupDatabase($path, $filename);
            }
        };

        $tempDir = storage_path('app/backups/test-remote-'.uniqid());
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $binDir = storage_path('app/backups/bin-remote-'.uniqid());
        mkdir($binDir, 0755, true);

        $argsFile = $tempDir.'/mysqldump_args.txt';
        $scriptPath = $binDir.'/mysqldump';
        file_put_contents($scriptPath, "#!/usr/bin/env bash\nprintf '%s\n' \"$@\" > \"\$MYSQDUMP_ARGS_FILE\"\necho '-- dump'\n");
        chmod($scriptPath, 0755);

        $oldPath = getenv('PATH') ?: '';
        putenv('PATH='.$binDir.':'.$oldPath);
        putenv('MYSQDUMP_ARGS_FILE='.$argsFile);

        config()->set('database.connections.mysql.host', 'db.internal');
        config()->set('database.connections.mysql.username', 'root');
        config()->set('database.connections.mysql.password', '');
        config()->set('database.connections.mysql.database', 'fzs_testing');

        try {
            $sqlFile = $service->callBackupDatabase($tempDir, 'legacy_remote');

            $this->assertFileExists($sqlFile);
            $args = (string) file_get_contents($argsFile);
            $this->assertStringContainsString('-hdb.internal', $args);
            $this->assertStringContainsString('-uroot', $args);
            $this->assertStringNotContainsString('-p', $args);
        } finally {
            putenv('PATH='.$oldPath);
            putenv('MYSQDUMP_ARGS_FILE');
            @unlink($argsFile);
            @unlink($sqlFile ?? '');
            @rmdir($binDir);
            @rmdir($tempDir);
        }
    }

    public function test_backup_files_creates_zip_with_public_and_storage_prefixes(): void
    {
        $service = new class extends BackupService
        {
            public function callBackupFiles(string $path, string $filename): string
            {
                return $this->backupFiles($path, $filename);
            }
        };

        $publicProbe = public_path('legacy-coverage-public-'.uniqid().'.txt');
        $storageProbe = storage_path('app/legacy-coverage-storage-'.uniqid().'.txt');
        file_put_contents($publicProbe, 'public-probe');
        file_put_contents($storageProbe, 'storage-probe');

        $backupPath = storage_path('app/backups');
        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        try {
            $zipPath = $service->callBackupFiles($backupPath, 'legacy_zip_'.uniqid());
            $this->assertFileExists($zipPath);

            $zip = new \ZipArchive;
            $openResult = $zip->open($zipPath);
            $this->assertSame(true, $openResult);
            $this->assertNotFalse($zip->locateName('public/'.basename($publicProbe)));
            $this->assertNotFalse($zip->locateName('storage/'.basename($storageProbe)));
            $zip->close();
        } finally {
            @unlink($publicProbe);
            @unlink($storageProbe);
            @unlink($zipPath ?? '');
        }
    }

    public function test_list_backups_maps_and_sorts_files_by_modified_desc(): void
    {
        $service = new BackupService;

        $older = Mockery::mock();
        $older->shouldReceive('getFilename')->andReturn('older.sql');
        $older->shouldReceive('getSize')->andReturn(10);
        $older->shouldReceive('getMTime')->andReturn(1700000000);

        $newer = Mockery::mock();
        $newer->shouldReceive('getFilename')->andReturn('newer.sql');
        $newer->shouldReceive('getSize')->andReturn(20);
        $newer->shouldReceive('getMTime')->andReturn(1800000000);

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('files')->once()->andReturn([$older, $newer]);

        $result = $service->listBackups();

        $this->assertCount(2, $result);
        $this->assertSame('newer.sql', $result[0]['name']);
        $this->assertSame('older.sql', $result[1]['name']);
        $this->assertSame(20, $result[0]['size']);
        $this->assertArrayHasKey('modified', $result[0]);
    }
}
