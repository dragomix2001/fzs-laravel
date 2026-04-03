<?php

namespace Tests\Feature;

use App\Models\UpisGodine;
use Carbon\Carbon;
use Database\Seeders\TestHelperSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModelCastingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestHelperSeeder::class);
    }

    public function test_upis_godine_datum_upisa_is_datetime(): void
    {
        $upis = UpisGodine::whereNotNull('datumUpisa')->first();

        $this->assertNotNull($upis, 'UpisGodine with datumUpisa should exist');
        $this->assertNotNull($upis->datumUpisa, 'datumUpisa is null');

        $this->assertTrue(
            is_a($upis->datumUpisa, Carbon::class),
            'datumUpisa should be Carbon instance, got: '.gettype($upis->datumUpisa)
        );
    }

    public function test_upis_godine_datum_promene_is_datetime(): void
    {
        $upis = UpisGodine::whereNotNull('datumPromene')->first();

        $this->assertNotNull($upis, 'UpisGodine with datumPromene should exist');
        $this->assertNotNull($upis->datumPromene, 'datumPromene is null');

        $this->assertTrue(
            is_a($upis->datumPromene, Carbon::class),
            'datumPromene should be Carbon instance, got: '.gettype($upis->datumPromene)
        );
    }

    public function test_upis_godine_date_format_works_in_view(): void
    {
        $upis = UpisGodine::whereNotNull('datumUpisa')->first();

        $this->assertNotNull($upis, 'UpisGodine with datumUpisa should exist');

        $formatted = $upis->datumUpisa->format('d.m.Y.');
        $this->assertNotEmpty($formatted);
        $this->assertMatchesRegularExpression('/^\d{2}\.\d{2}\.\d{4}\.$/', $formatted);
    }

    public function test_godina_studija_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('godina_studija'), 'godina_studija table should exist');
    }

    public function test_upis_godine_table_has_required_columns(): void
    {
        $columns = Schema::getColumnListing('upis_godine');

        $this->assertContains('datumUpisa', $columns);
        $this->assertContains('datumPromene', $columns);
    }
}
