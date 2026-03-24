<?php

$dir = new RecursiveDirectoryIterator('app/Http/Controllers');
foreach (new RecursiveIteratorIterator($dir) as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        // Replace use App\ModelName; with use App\Models\ModelName;
        $content = preg_replace(
            '/^use App\\\\([A-Za-z]+);$/m',
            'use App\\Models\\\\$1;',
            $content
        );

        // Also fix cases like use App\Models\SomeModel; that were already correct
        // and avoid double Models

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo 'Fixed: '.$file->getPathname()."\n";
        }
    }
}

// Also fix Services
$dir = new RecursiveDirectoryIterator('app/Services');
foreach (new RecursiveIteratorIterator($dir) as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        $content = preg_replace(
            '/^use App\\\\([A-Za-z]+);$/m',
            'use App\\Models\\\\$1;',
            $content
        );

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo 'Fixed: '.$file->getPathname()."\n";
        }
    }
}

// Also fix Listeners
$dir = new RecursiveDirectoryIterator('app/Listeners');
foreach (new RecursiveIteratorIterator($dir) as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        $content = preg_replace(
            '/^use App\\\\([A-Za-z]+);$/m',
            'use App\\Models\\\\$1;',
            $content
        );

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo 'Fixed: '.$file->getPathname()."\n";
        }
    }
}

echo "Done!\n";
