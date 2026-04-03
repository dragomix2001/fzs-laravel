<?php

namespace Tests;

use Database\Seeders\StatusGodineTableSeeder;
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

        if (! $this->usesManagedDatabaseLifecycle() && ! self::$databasePrepared) {
            Artisan::call('migrate', ['--seed' => true]);
            self::$databasePrepared = true;
        }

        if (! $this->usesManagedDatabaseLifecycle() && ! Schema::hasTable('status_godine')) {
            Artisan::call('migrate', ['--seed' => true]);
        }

        $this->seed(StatusGodineTableSeeder::class);
    }

    private function usesManagedDatabaseLifecycle(): bool
    {
        $traits = class_uses_recursive(static::class);

        return in_array(RefreshDatabase::class, $traits, true);
    }
}
