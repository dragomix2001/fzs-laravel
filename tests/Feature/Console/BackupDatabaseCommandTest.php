<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Console\Commands\BackupDatabase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class BackupDatabaseCommandTest extends TestCase
{
    private string $originalPath;

    /**
     * @var array<int, string>
     */
    private array $tempBinDirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalPath = getenv('PATH') ?: '';
    }

    protected function tearDown(): void
    {
        putenv('PATH='.$this->originalPath);

        foreach ($this->tempBinDirs as $dir) {
            @unlink($dir.'/mysqldump');
            @unlink($dir.'/gzip');
            @rmdir($dir);
        }

        parent::tearDown();
    }

    public function test_backup_run_succeeds_without_compression_when_mysqldump_succeeds(): void
    {
        $mysqldumpScript = <<<'BASH'
    #!/usr/bin/env bash
    echo '-- sql backup content'
    exit 0
    BASH;
        $this->createFakeBinDir('mysqldump', $mysqldumpScript);

        config()->set('database.connections.mysql.host', '127.0.0.1');
        config()->set('database.connections.mysql.username', 'root');
        config()->set('database.connections.mysql.password', '');
        config()->set('database.connections.mysql.database', 'fzs_testing');

        $command = app(BackupDatabase::class);
        $command->setLaravel($this->app);

        $before = glob(storage_path('app/backups/backup_*.sql')) ?: [];
        $output = new BufferedOutput;

        $exitCode = $command->run(new ArrayInput([]), $output);

        $after = glob(storage_path('app/backups/backup_*.sql')) ?: [];
        $created = array_values(array_diff($after, $before));

        $this->assertSame(0, $exitCode);
        $this->assertNotEmpty($created);
        $this->assertStringContainsString('Backup created successfully:', $output->fetch());

        foreach ($created as $file) {
            @unlink($file);
        }
    }

    public function test_backup_run_succeeds_with_compression_when_gzip_succeeds(): void
    {
        $mysqldumpScript = <<<'BASH'
    #!/usr/bin/env bash
    echo '-- sql backup content'
    exit 0
    BASH;
        $binDir = $this->createFakeBinDir('mysqldump', $mysqldumpScript);
        $gzipScript = <<<'BASH'
    #!/usr/bin/env bash
    target="${@: -1}"
    cp "$target" "$target.gz"
    rm -f "$target"
    exit 0
    BASH;
        $this->createFakeBinary(
            $binDir,
            'gzip',
            $gzipScript
        );

        config()->set('database.connections.mysql.host', '127.0.0.1');
        config()->set('database.connections.mysql.username', 'root');
        config()->set('database.connections.mysql.password', '');
        config()->set('database.connections.mysql.database', 'fzs_testing');

        $command = app(BackupDatabase::class);
        $command->setLaravel($this->app);

        $before = glob(storage_path('app/backups/backup_*.sql.gz')) ?: [];
        $output = new BufferedOutput;

        $exitCode = $command->run(new ArrayInput(['--compress' => true]), $output);

        $after = glob(storage_path('app/backups/backup_*.sql.gz')) ?: [];
        $created = array_values(array_diff($after, $before));

        $this->assertSame(0, $exitCode);
        $this->assertNotEmpty($created);
        $this->assertStringContainsString('.gz', $output->fetch());

        foreach ($created as $file) {
            @unlink($file);
        }
    }

    public function test_backup_run_returns_error_when_compression_fails(): void
    {
        $mysqldumpScript = <<<'BASH'
    #!/usr/bin/env bash
    echo '-- sql backup content'
    exit 0
    BASH;
        $binDir = $this->createFakeBinDir('mysqldump', $mysqldumpScript);
        $gzipFailScript = <<<'BASH'
    #!/usr/bin/env bash
    exit 2
    BASH;
        $this->createFakeBinary($binDir, 'gzip', $gzipFailScript);

        config()->set('database.connections.mysql.host', '127.0.0.1');
        config()->set('database.connections.mysql.username', 'root');
        config()->set('database.connections.mysql.password', '');
        config()->set('database.connections.mysql.database', 'fzs_testing');

        $command = app(BackupDatabase::class);
        $command->setLaravel($this->app);

        $output = new BufferedOutput;
        $exitCode = $command->run(new ArrayInput(['--compress' => true]), $output);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Failed to compress database backup.', $output->fetch());
    }

    private function createFakeBinDir(string $binaryName, string $content): string
    {
        $binDir = storage_path('app/backups/bin-'.uniqid());
        if (! is_dir($binDir)) {
            mkdir($binDir, 0755, true);
        }

        $this->tempBinDirs[] = $binDir;

        $this->createFakeBinary($binDir, $binaryName, $content);
        putenv('PATH='.$binDir.':'.$this->originalPath);

        return $binDir;
    }

    private function createFakeBinary(string $binDir, string $name, string $content): void
    {
        $binary = $binDir.'/'.$name;
        file_put_contents($binary, $content);
        chmod($binary, 0755);
    }
}
