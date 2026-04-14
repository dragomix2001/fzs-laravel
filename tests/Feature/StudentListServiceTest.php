<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\StudentListService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentListServiceTest extends TestCase
{
    use RefreshDatabase;

    private StudentListService $service;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([1, 2, 3, 4, 5, 6] as $id) {
            DB::table('status_studiranja')->insertOrIgnore([
                'id' => $id,
                'naziv' => "Status {$id}",
                'indikatorAktivan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('krsna_slava')->insertOrIgnore([
            'id' => 1, 'naziv' => 'Test Slava', 'datumSlave' => '19.12.', 'indikatorAktivan' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('opsti_uspeh')->insertOrIgnore([
            'id' => 1, 'naziv' => 'Odlican',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('opstina')->insertOrIgnore([
            'id' => 1, 'naziv' => 'Beograd',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('mesto')->insertOrIgnore([
            'id' => 1, 'naziv' => 'Beograd', 'opstina_id' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->service = app(StudentListService::class);
    }

    private function buildBaseFixtures(int $statusUpisa = 3): array
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS']);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);
        $statusStudiranja = StatusStudiranja::factory()->create();
        $godinaId = DB::table('godina_studija')->insertGetId([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $godina = GodinaStudija::find($godinaId);

        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => $godina->id,
            'statusUpisa_id' => $statusUpisa,
            'krsnaSlava_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'uspehSrednjaSkola_id' => 1,
            'mesto_id' => 1,
        ]);

        return compact('tipStudija', 'program', 'skolskaGodina', 'statusStudiranja', 'godina', 'kandidat');
    }

    public function test_spisak_po_smerovima_executes_query_logic(): void
    {
        $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spisakPoSmerovima();
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_smerovima_with_no_data_does_not_crash_queries(): void
    {
        ob_start();
        $this->service->spisakPoSmerovima();
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_integralno_executes_with_matching_data(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 1);

        ob_start();
        $this->service->integralno($f['skolskaGodina']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_integralno_with_no_matching_records(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create();

        ob_start();
        $this->service->integralno($skolskaGodina->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_smerovima_ostali_executes_query_logic(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 2);

        ob_start();
        $this->service->spisakPoSmerovimaOstali($f['skolskaGodina']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_smerovima_ostali_with_multiple_statuses(): void
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create();
        $godinaId = DB::table('godina_studija')->insertGetId([
            'naziv' => 'Druga',
            'nazivRimski' => 'II',
            'nazivSlovimaUPadezu' => 'druge',
            'redosledPrikazivanja' => 2,
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $godina = GodinaStudija::find($godinaId);

        foreach ([1, 2, 4] as $status) {
            Kandidat::factory()->create([
                'tipStudija_id' => $tipStudija->id,
                'studijskiProgram_id' => $program->id,
                'skolskaGodinaUpisa_id' => $skolskaGodina->id,
                'godinaStudija_id' => $godina->id,
                'statusUpisa_id' => $status,
                'krsnaSlava_id' => 1,
                'opstiUspehSrednjaSkola_id' => 1,
                'uspehSrednjaSkola_id' => 1,
                'mesto_id' => 1,
            ]);
        }

        ob_start();
        $this->service->spisakPoSmerovimaOstali($skolskaGodina->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_smerovima_aktivni_executes_query_logic(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spisakPoSmerovimaAktivni($f['skolskaGodina']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_za_smer_executes_with_matching_data(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spisakZaSmer($f['program']->id, $f['godina']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_za_smer_with_nonexistent_ids(): void
    {
        ob_start();
        $this->service->spisakZaSmer(99999, 99999);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_programu_executes_query_logic(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spisakPoProgramu($f['program']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_godini_executes_query_logic(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spisakPoGodini($f['godina']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_slavama_executes_query_logic(): void
    {
        $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spisakPoSlavama();
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_profesorima_executes_with_profesori(): void
    {
        Profesor::factory()->count(3)->create();

        ob_start();
        $this->service->spisakPoProfesorima();
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_profesorima_executes_with_no_profesori(): void
    {
        ob_start();
        $this->service->spisakPoProfesorima();
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spiskovi_studenti_executes_with_full_data(): void
    {
        $this->buildBaseFixtures(statusUpisa: 3);

        ob_start();
        $this->service->spiskoviStudenti();
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_predmetima_executes_with_prijave(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 3);
        $prijava = PrijavaIspita::factory()->create([
            'kandidat_id' => $f['kandidat']->id,
        ]);
        $predmetProgramId = $prijava->predmet_id;
        $predmet = PredmetProgram::find($predmetProgramId);

        ob_start();
        $this->service->spisakPoPredmetima($predmet ? $predmet->predmet_id : 99999);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_po_predmetima_with_no_prijave(): void
    {
        $predmet = Predmet::factory()->create();

        ob_start();
        $this->service->spisakPoPredmetima($predmet->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_diplomiranih_executes_with_matching_data(): void
    {
        $f = $this->buildBaseFixtures(statusUpisa: 6);

        ob_start();
        $this->service->spisakDiplomiranih($f['skolskaGodina']->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_spisak_diplomiranih_with_no_data(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create();

        ob_start();
        $this->service->spisakDiplomiranih($skolskaGodina->id);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }
}
