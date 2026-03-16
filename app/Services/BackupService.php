<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupService
{
    public function createBackup($type = 'full')
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = 'backup_'.$timestamp.'_'.$type;

        $path = storage_path('app/backups');

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        try {
            if ($type === 'full' || $type === 'database') {
                $this->backupDatabase($path, $filename);
            }

            if ($type === 'full' || $type === 'files') {
                $this->backupFiles($path, $filename);
            }

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $path,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function backupDatabase($path, $filename)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $sqlFile = $path.'/'.$filename.'_db.sql';

        $command = sprintf(
            'mysqldump -u%s %s %s > %s 2>/dev/null',
            $username,
            $password ? '-p'.escapeshellarg($password) : '',
            $database,
            escapeshellarg($sqlFile)
        );

        if ($host !== '127.0.0.1') {
            $command = 'mysqldump -h'.$host.' -u'.$username.($password ? ' -p'.escapeshellarg($password) : '').' '.$database.' > '.escapeshellarg($sqlFile).' 2>/dev/null';
        }

        exec($command);

        return $sqlFile;
    }

    protected function backupFiles($path, $filename)
    {
        $publicPath = public_path();
        $storagePath = storage_path('app');

        $zipFile = $path.'/'.$filename.'_files.zip';

        $zip = new \ZipArchive;
        if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
            $zip->addDir($publicPath, 'public');
            $zip->addDir($storagePath, 'storage');
            $zip->close();
        }

        return $zipFile;
    }

    public function listBackups()
    {
        $path = storage_path('app/backups');

        if (! File::exists($path)) {
            return [];
        }

        $files = File::files($path);

        return collect($files)->map(function ($file) {
            return [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
            ];
        })->sortByDesc('modified')->values()->toArray();
    }

    public function deleteBackup($filename)
    {
        $path = storage_path('app/backups/'.$filename);

        if (File::exists($path)) {
            File::delete($path);

            return true;
        }

        return false;
    }
}
