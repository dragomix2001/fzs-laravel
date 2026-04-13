<?php

namespace Tests;

use Database\Seeders\StatusGodineTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $databasePrepared = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Buffer output to suppress PDF and other binary data during tests
        ob_start();

        // DatabaseTransactions tests need tables to exist (they just wrap in a transaction).
        // RefreshDatabase tests handle their own migrations via the trait.
        // Only tests with NO database trait need manual migration here.
        if (! $this->usesRefreshDatabase() && ! self::$databasePrepared) {
            Artisan::call('migrate', ['--seed' => true]);
            self::$databasePrepared = true;
        }

        $this->seed(StatusGodineTableSeeder::class);
    }

    protected function tearDown(): void
    {
        // Clear output buffer to discard any buffered output (PDFs, etc.)
        ob_end_clean();
        parent::tearDown();
    }

    private function usesRefreshDatabase(): bool
    {
        $traits = class_uses_recursive(static::class);

        return in_array(RefreshDatabase::class, $traits, true);
    }
}
