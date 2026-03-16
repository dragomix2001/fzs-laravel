<?php

namespace Tests\Feature;

use App\Models\UpisGodine;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModelCastingTest extends TestCase
{
    public function test_upis_godine_datum_upisa_is_datetime(): void
    {
        $upis = UpisGodine::whereNotNull('datumUpisa')->first();

        if (! $upis) {
            $this->markTestSkipped('No UpisGodine records with datumUpisa found');

            return;
        }

        $this->assertNotNull($upis->datumUpisa, 'datumUpisa is null');

        $this->assertTrue(
            is_a($upis->datumUpisa, \Carbon\Carbon::class),
            'datumUpisa should be Carbon instance, got: '.gettype($upis->datumUpisa)
        );
    }

    public function test_upis_godine_datum_promene_is_datetime(): void
    {
        $upis = UpisGodine::whereNotNull('datumPromene')->first();

        if (! $upis) {
            $this->markTestSkipped('No UpisGodine records with datumPromene found');

            return;
        }

        $this->assertNotNull($upis->datumPromene, 'datumPromene is null');

        $this->assertTrue(
            is_a($upis->datumPromene, \Carbon\Carbon::class),
            'datumPromene should be Carbon instance, got: '.gettype($upis->datumPromene)
        );
    }

    public function test_upis_godine_date_format_works_in_view(): void
    {
        $upis = UpisGodine::whereNotNull('datumUpisa')->first();

        if (! $upis) {
            $this->markTestSkipped('No UpisGodine records with datumUpisa found');

            return;
        }

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
