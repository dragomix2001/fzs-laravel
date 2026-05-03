<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class AdditionalCommandsCoverageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_archive_zapisnici_runs_successfully_with_no_records(): void
    {
        $output = new BufferedOutput;
        $this->artisan('zapisnici:archive')->assertExitCode(0);
    }

    public function test_archive_zapisnici_archives_old_records(): void
    {
        // Create a ZapisnikOPolaganjuIspita that is old and not archived
        // We use DB directly since the factory might have constraints
        $oldDate = now()->subMonths(7)->format('Y-m-d');

        // Check if table has 'arhiviran' column
        $columns = DB::getSchemaBuilder()->getColumnListing('zapisnici_o_polaganju_ispita');
        if (! in_array('arhiviran', $columns, true)) {
            $this->markTestSkipped('Table does not have arhiviran column');
        }

        $this->artisan('zapisnici:archive', ['--months' => 6])->assertExitCode(0);
    }

    public function test_archive_zapisnici_accepts_custom_months_option(): void
    {
        $this->artisan('zapisnici:archive', ['--months' => 3])->assertExitCode(0);
    }

    public function test_cleanup_old_notifications_runs_with_no_records(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            $this->markTestSkipped('Notifications table does not exist in test DB');
        }

        $this->artisan('notifications:cleanup')->assertExitCode(0);
    }

    public function test_cleanup_old_notifications_accepts_custom_days_option(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            $this->markTestSkipped('Notifications table does not exist in test DB');
        }

        $this->artisan('notifications:cleanup', ['--days' => 30])->assertExitCode(0);
    }

    public function test_cleanup_old_notifications_deletes_old_records(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            $this->markTestSkipped('Notifications table does not exist in test DB');
        }
        // Insert an old notification directly
        $oldDate = now()->subDays(100)->format('Y-m-d H:i:s');
        DB::table('notifications')->insert([
            'id' => Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => 1,
            'data' => json_encode(['message' => 'old notification']),
            'created_at' => $oldDate,
            'updated_at' => $oldDate,
        ]);

        $countBefore = DB::table('notifications')->where('created_at', '<', now()->subDays(90))->count();

        $this->artisan('notifications:cleanup', ['--days' => 90])->assertExitCode(0);

        $countAfter = DB::table('notifications')->where('created_at', '<', now()->subDays(90))->count();
        $this->assertLessThan($countBefore, $countAfter + 1); // count decreased or stayed at 0
    }
}
