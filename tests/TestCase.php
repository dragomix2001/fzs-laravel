<?php

namespace Tests;

use Database\Seeders\StatusGodineTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $databasePrepared = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure DB-backed tests that do not use RefreshDatabase always run
        // against a fully migrated, seeded schema.
        if (! $this->usesRefreshDatabase() && ! self::$databasePrepared) {
            Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);
            self::$databasePrepared = true;
        }

        // Keep FK prerequisites stable for tests/factories that assume status_godine id=1 exists.
        if (Schema::hasTable('status_godine') && ! DB::table('status_godine')->where('id', 1)->exists()) {
            $this->seed(StatusGodineTableSeeder::class);
        }
    }

    private function usesRefreshDatabase(): bool
    {
        $traits = class_uses_recursive(static::class);

        return in_array(RefreshDatabase::class, $traits, true);
    }
}
