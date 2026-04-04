<?php

namespace Tests;

use Database\Seeders\StatusGodineTableSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $databasePrepared = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Buffer output to suppress PDF and other binary data during tests
        ob_start();

        if (! $this->usesManagedDatabaseLifecycle() && ! self::$databasePrepared) {
            Artisan::call('migrate', ['--seed' => true]);
            self::$databasePrepared = true;
        }

        if (! $this->usesManagedDatabaseLifecycle() && ! Schema::hasTable('status_godine')) {
            Artisan::call('migrate', ['--seed' => true]);
        }

        $this->seed(StatusGodineTableSeeder::class);
    }

    protected function tearDown(): void
    {
        // Clear output buffer to discard any buffered output (PDFs, etc.)
        ob_end_clean();
        parent::tearDown();
    }

    private function usesManagedDatabaseLifecycle(): bool
    {
        $traits = class_uses_recursive(static::class);

        return in_array(RefreshDatabase::class, $traits, true)
            || in_array(DatabaseTransactions::class, $traits, true);
    }
}
