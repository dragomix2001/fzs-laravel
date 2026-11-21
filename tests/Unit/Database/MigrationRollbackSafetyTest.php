<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use LogicException;
use Tests\TestCase;

class MigrationRollbackSafetyTest extends TestCase
{
    public function test_nullable_column_migration_refuses_an_unsafe_rollback(): void
    {
        $migration = require database_path('migrations/2026_03_19_231349_fix_all_nullable_columns.php');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('cannot be rolled back safely');

        $migration->down();
    }
}
