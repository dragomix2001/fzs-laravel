<?php

namespace Tests\Feature;

use Database\Seeders\TestDataSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    public function test_database_seed_includes_realistic_demo_data(): void
    {
        $this->seed(TestDataSeeder::class);
        $this->seed(TestDataSeeder::class);

        $this->assertSame(2, DB::table('tip_studija')->count());
        $this->assertSame(3, DB::table('studijski_program')->count());
        $this->assertSame(3, DB::table('profesor')->count());
        $this->assertSame(60, DB::table('kandidat')->count());
        $this->assertSame(67, DB::table('prijava_ispita')->count());
        $this->assertSame(67, DB::table('zapisnik_o_polaganju_ispita')->count());
        $this->assertGreaterThan(0, DB::table('polozeni_ispiti')->count());
        $this->assertLessThan(DB::table('prijava_ispita')->count(), DB::table('polozeni_ispiti')->count());

        $oasTypeId = DB::table('tip_studija')->where('skrNaziv', 'OAS')->value('id');
        $firstYearId = DB::table('godina_studija')->where('redosledPrikazivanja', 1)->value('id');
        $sportProgramId = DB::table('studijski_program')
            ->where('skrNazivStudijskogPrograma', 'SIV')
            ->value('id');

        $this->assertSame(20, DB::table('kandidat')->where([
            'tipStudija_id' => $oasTypeId,
            'statusUpisa_id' => 1,
            'studijskiProgram_id' => $sportProgramId,
            'godinaStudija_id' => $firstYearId,
        ])->count());

        foreach (range(1, 7) as $statusId) {
            $this->assertGreaterThan(
                0,
                DB::table('kandidat')->where('statusUpisa_id', $statusId)->count(),
                "No test candidate exists for enrollment status {$statusId}."
            );
        }

        $this->assertSame(3, DB::table('diploma')->count());
        $this->assertSame(3, DB::table('diplomski_prijava_teme')->count());
        $this->assertSame(3, DB::table('diplomski_prijava_odbrane')->count());
        $this->assertSame(3, DB::table('diplomski_polaganje')->count());

        $lukaId = DB::table('kandidat')->where('email', 'luka.lukovic@student.fzs.edu.rs')->value('id');
        $this->assertGreaterThanOrEqual(
            3,
            DB::table('polozeni_ispiti')->where('kandidat_id', $lukaId)->count()
        );
        $this->assertTrue(DB::table('diploma')->where('kandidat_id', $lukaId)->exists());
        $this->assertTrue(DB::table('diplomski_prijava_odbrane')->where('kandidat_id', $lukaId)->exists());
        $this->assertTrue(DB::table('diplomski_polaganje')->where('kandidat_id', $lukaId)->exists());
    }
}
