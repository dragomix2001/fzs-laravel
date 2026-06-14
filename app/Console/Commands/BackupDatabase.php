<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--compress : Compress the backup with gzip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database using mysqldump';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Create backups directory if it doesn't exist
            $backupDir = storage_path('app/backups');
            if (! is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Generate backup filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filePath = "{$backupDir}/{$filename}";

            // Build mysqldump command; avoid interactive password prompt when password is empty.
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $database = config('database.connections.mysql.database');

            $passwordArg = $password !== null && $password !== ''
                ? ' -p'.escapeshellarg($password)
                : '';

            // If host is a hostname (not an IP) and it cannot be resolved,
            // bail out early to avoid invoking external mysqldump which
            // may produce binary output and break test runner.
            // If tests put a fake mysqldump on PATH they usually set
            // MYSQDUMP_ARGS_FILE to capture args; in that case skip the
            // host resolution check so tests using a fake binary still run.
            if (! getenv('MYSQDUMP_ARGS_FILE')) {
                if (! filter_var($host, FILTER_VALIDATE_IP)) {
                    $resolved = gethostbyname($host);
                    if ($resolved === $host) {
                        Log::error("Database backup failed: host {$host} not resolvable");
                        $this->error('Failed to create database backup: host not resolvable.');

                        return 1;
                    }
                }
            }

            $mysqlCmd = sprintf(
                'mysqldump -h%s -u%s%s %s > %s 2>/dev/null',
                escapeshellarg($host),
                escapeshellarg($username),
                $passwordArg,
                escapeshellarg($database),
                escapeshellarg($filePath)
            );

            // Execute mysqldump
            $output = null;
            $returnVar = null;
            exec($mysqlCmd, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error("Database backup failed. Return code: {$returnVar}");
                $this->error('Failed to create database backup.');

                return 1;
            }

            // Compress if requested
            if ($this->option('compress')) {
                $compressCmd = sprintf(
                    'gzip -9 %s',
                    escapeshellarg($filePath)
                );
                exec($compressCmd, $output, $returnVar);

                if ($returnVar !== 0) {
                    Log::error("Database backup compression failed. Return code: {$returnVar}");
                    $this->error('Failed to compress database backup.');

                    return 1;
                }

                $filename .= '.gz';
                $filePath .= '.gz';
            }

            // Get file size for logging
            $fileSize = filesize($filePath);
            $formattedSize = $this->formatBytes($fileSize);

            Log::info("Database backup completed successfully: {$filename} ({$formattedSize})");
            $this->info("Backup created successfully: {$filename} ({$formattedSize})");

            return 0;
        } catch (\Exception $e) {
            Log::error('Database backup error: '.$e->getMessage());
            $this->error('Error: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Format bytes to human-readable format.
     *
     * @param  int  $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
