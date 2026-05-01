<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\PrijavaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrijavaServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PrijavaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        $this->service = new PrijavaService;

        // Ensure StatusStudiranja with id=1 exists (needed by tests that set statusUpisa_id=1)
        if (! StatusStudiranja::find(1)) {
            DB::table('status_studiranja')->insert([
                'id' => 1,
                'naziv' => 'Aktivan',
                'indikatorAktivan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    // =========================================================================
    // Helper: create a fully wired kandidat with matching predmet_program
    // =========================================================================

    private function createKandidatWithProgram(array $overrides = []): array
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create();
        $status = StatusStudiranja::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create();
        $predmet = Predmet::factory()->create();

        $kandidat = Kandidat::factory()->create(array_merge([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'statusUpisa_id' => $status->id,
            'godinaStudija_id' => $godinaStudija->id,
        ], $overrides));

        $predmetProgram = PredmetProgram::create([
            'predmet_id' => $predmet->id,
            'studijskiProgram_id' => $program->id,
            'tipStudija_id' => $tipStudija->id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $godinaStudija->id,
            'tipPredmeta_id' => $tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $skolskaGodina->id,
        ]);

        return compact(
            'kandidat', 'tipStudija', 'program', 'skolskaGodina',
            'predmet', 'predmetProgram', 'godinaStudija', 'tipPredmeta'
        );
    }

    // =========================================================================
    // getSpisakPredmetaData()
    // =========================================================================

    public function test_get_spisak_predmeta_data_returns_correct_keys(): void
    {
        $result = $this->service->getSpisakPredmetaData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('studijskiProgrami', $result);
        $this->assertArrayHasKey('predmeti', $result);
    }

    public function test_get_spisak_predmeta_data_returns_all_records(): void
    {
        TipStudija::factory()->count(2)->create();
        StudijskiProgram::factory()->count(3)->create();
        Predmet::factory()->count(4)->create();

        $result = $this->service->getSpisakPredmetaData();

        $this->assertGreaterThanOrEqual(2, $result['tipStudija']->count());
        $this->assertGreaterThanOrEqual(3, $result['studijskiProgrami']->count());
        $this->assertGreaterThanOrEqual(4, $result['predmeti']->count());
    }

    // =========================================================================
    // getPrijaveZaPredmet()
    // =========================================================================

    public function test_get_prijave_za_predmet_returns_correct_structure(): void
    {
        $data = $this->createKandidatWithProgram();
        $predmet = $data['predmet'];

        $result = $this->service->getPrijaveZaPredmet($predmet->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('predmet', $result);
        $this->assertArrayHasKey('prijave', $result);
        $this->assertEquals($predmet->id, $result['predmet']->id);
    }

    public function test_get_prijave_za_predmet_returns_prijave_for_predmet(): void
    {
        $data = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        PrijavaIspita::create([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        $result = $this->service->getPrijaveZaPredmet($data['predmet']->id);

        $this->assertGreaterThanOrEqual(1, $result['prijave']->count());
    }

    public function test_get_prijave_za_predmet_returns_empty_when_no_prijave(): void
    {
        $predmet = Predmet::factory()->create();

        $result = $this->service->getPrijaveZaPredmet($predmet->id);

        $this->assertEquals($predmet->id, $result['predmet']->id);
        $this->assertCount(0, $result['prijave']);
    }

    // =========================================================================
    // getCreatePrijavaIspitaPredmetData()
    // =========================================================================

    public function test_get_create_prijava_ispita_predmet_data_returns_correct_keys(): void
    {
        $data = $this->createKandidatWithProgram();
        // Set statusUpisa_id = 1 so the kandidat shows in brojeviIndeksa
        $data['kandidat']->update(['statusUpisa_id' => 1]);

        $result = $this->service->getCreatePrijavaIspitaPredmetData($data['predmetProgram']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('brojeviIndeksa', $result);
        $this->assertArrayHasKey('predmet', $result);
        $this->assertArrayHasKey('studijskiProgram', $result);
        $this->assertArrayHasKey('godinaStudija', $result);
        $this->assertArrayHasKey('tipPredmeta', $result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('ispitniRok', $result);
        $this->assertArrayHasKey('profesor', $result);
        $this->assertArrayHasKey('tipPrijave', $result);
    }

    public function test_get_create_prijava_ispita_predmet_data_returns_profesor_for_predmet(): void
    {
        $data = $this->createKandidatWithProgram();
        $profesor = Profesor::factory()->create();
        ProfesorPredmet::create([
            'predmet_id' => $data['predmetProgram']->id,
            'profesor_id' => $profesor->id,
        ]);

        $result = $this->service->getCreatePrijavaIspitaPredmetData($data['predmetProgram']->id);

        $this->assertEquals(1, $result['profesor']->count());
        $this->assertEquals($profesor->id, $result['profesor']->first()->id);
    }

    public function test_get_create_prijava_ispita_predmet_data_returns_all_profesori_when_no_match(): void
    {
        $data = $this->createKandidatWithProgram();
        Profesor::factory()->count(3)->create();

        $result = $this->service->getCreatePrijavaIspitaPredmetData($data['predmetProgram']->id);

        $this->assertGreaterThanOrEqual(3, $result['profesor']->count());
    }

    // =========================================================================
    // getCreatePrijavaIspitaPredmetManyData()
    // =========================================================================

    public function test_get_create_prijava_ispita_predmet_many_data_returns_correct_keys(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);

        $result = $this->service->getCreatePrijavaIspitaPredmetManyData($data['predmet']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kandidati', $result);
        $this->assertArrayHasKey('kandidatiJson', $result);
        $this->assertArrayHasKey('predmet', $result);
        $this->assertArrayHasKey('studijskiProgram', $result);
        $this->assertArrayHasKey('godinaStudija', $result);
        $this->assertArrayHasKey('tipPredmeta', $result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('ispitniRok', $result);
        $this->assertArrayHasKey('profesor', $result);
        $this->assertArrayHasKey('tipPrijave', $result);
    }

    public function test_get_create_prijava_ispita_predmet_many_data_filters_kandidati_by_program(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);

        $result = $this->service->getCreatePrijavaIspitaPredmetManyData($data['predmet']->id);

        $this->assertGreaterThanOrEqual(1, $result['kandidati']->count());
    }

    public function test_get_create_prijava_ispita_predmet_many_data_returns_kandidati_json(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1, 'brojIndeksa' => '001/2024']);

        $result = $this->service->getCreatePrijavaIspitaPredmetManyData($data['predmet']->id);

        $this->assertGreaterThanOrEqual(1, $result['kandidatiJson']->count());
        $jsonItem = $result['kandidatiJson']->first();
        $this->assertArrayHasKey('id', $jsonItem);
        $this->assertArrayHasKey('label', $jsonItem);
        $this->assertArrayHasKey('value', $jsonItem);
    }

    public function test_get_create_prijava_ispita_predmet_many_data_falls_back_to_all_when_no_programs(): void
    {
        $predmet = Predmet::factory()->create();
        // No PredmetProgram for this predmet, so empty studijskiProgrami
        // Create a kandidat with statusUpisa_id = 1
        Kandidat::factory()->create(['statusUpisa_id' => 1]);

        $result = $this->service->getCreatePrijavaIspitaPredmetManyData($predmet->id);

        $this->assertGreaterThanOrEqual(1, $result['kandidati']->count());
    }

    // =========================================================================
    // storePrijavaIspita()
    // =========================================================================

    public function test_store_prijava_ispita_creates_record(): void
    {
        $data = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $prijava = $this->service->storePrijavaIspita([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => '2024-06-15',
            'tipPrijave_id' => 1,
        ]);

        $this->assertInstanceOf(PrijavaIspita::class, $prijava);
        $this->assertTrue($prijava->exists);
        $this->assertEquals($data['kandidat']->id, $prijava->kandidat_id);
    }

    public function test_store_prijava_ispita_saves_all_fields(): void
    {
        $data = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $prijava = $this->service->storePrijavaIspita([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 3,
            'datum' => '2024-09-01',
            'tipPrijave_id' => 2,
        ]);

        $this->assertDatabaseHas('prijava_ispita', [
            'id' => $prijava->id,
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 3,
            'tipPrijave_id' => 2,
        ]);
    }

    // =========================================================================
    // deletePrijavaIspita() — with cascading cleanup
    // =========================================================================

    public function test_delete_prijava_ispita_removes_prijava(): void
    {
        $data = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $prijava = PrijavaIspita::create([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        $result = $this->service->deletePrijavaIspita($prijava->id);

        $this->assertIsArray($result);
        $this->assertEquals($data['kandidat']->id, $result['kandidat_id']);
        $this->assertEquals($data['predmet']->id, $result['predmet_id']);
        $this->assertNull(PrijavaIspita::find($prijava->id));
    }

    public function test_delete_prijava_ispita_cascades_zapisnik_and_polozeni(): void
    {
        $data = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $prijava = PrijavaIspita::create([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        // Create a zapisnik (predmet_id FK references predmet, not predmet_program)
        $zapisnik = ZapisnikOPolaganjuIspita::create([
            'predmet_id' => $data['predmet']->id,
            'datum' => now()->toDateString(),
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
        ]);

        // Link student to zapisnik
        $zapisStudent = ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $zapisnik->id,
            'prijavaIspita_id' => $prijava->id,
            'kandidat_id' => $data['kandidat']->id,
        ]);

        // Create polozeni ispit linked to prijava
        $polozeni = PolozeniIspiti::create([
            'prijava_id' => $prijava->id,
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'indikatorAktivan' => 0,
        ]);

        $this->service->deletePrijavaIspita($prijava->id);

        // Verify cascading deletes
        $this->assertNull(ZapisnikOPolaganju_Student::find($zapisStudent->id));
        $this->assertNull(PolozeniIspiti::find($polozeni->id));
        $this->assertNull(PrijavaIspita::find($prijava->id));
        // Zapisnik itself should be deleted since no more students
        $this->assertNull(ZapisnikOPolaganjuIspita::find($zapisnik->id));
    }

    public function test_delete_prijava_ispita_keeps_zapisnik_when_other_students_exist(): void
    {
        $data = $this->createKandidatWithProgram();
        $data2 = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $prijava1 = PrijavaIspita::create([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);
        $prijava2 = PrijavaIspita::create([
            'kandidat_id' => $data2['kandidat']->id,
            'predmet_id' => $data2['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        $zapisnik = ZapisnikOPolaganjuIspita::create([
            'predmet_id' => $data['predmet']->id,
            'datum' => now()->toDateString(),
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
        ]);

        ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $zapisnik->id,
            'prijavaIspita_id' => $prijava1->id,
            'kandidat_id' => $data['kandidat']->id,
        ]);
        ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $zapisnik->id,
            'prijavaIspita_id' => $prijava2->id,
            'kandidat_id' => $data2['kandidat']->id,
        ]);

        $this->service->deletePrijavaIspita($prijava1->id);

        // Zapisnik should remain because prijava2 still linked
        $this->assertNotNull(ZapisnikOPolaganjuIspita::find($zapisnik->id));
    }

    // =========================================================================
    // vratiKandidataPrijava()
    // =========================================================================

    public function test_vrati_kandidata_prijava_returns_student_and_predmeti(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->vratiKandidataPrijava($data['kandidat']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertEquals($data['kandidat']->id, $result['student']->id);
        $this->assertIsString($result['predmeti']);
    }

    public function test_vrati_kandidata_prijava_returns_html_options(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->vratiKandidataPrijava($data['kandidat']->id);

        $this->assertStringContainsString('<option', $result['predmeti']);
        $this->assertStringContainsString("value='{$data['predmetProgram']->id}'", $result['predmeti']);
    }

    // =========================================================================
    // vratiPredmetPrijava()
    // =========================================================================

    public function test_vrati_predmet_prijava_returns_correct_keys(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->vratiPredmetPrijava($data['predmetProgram']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tipPredmeta', $result);
        $this->assertArrayHasKey('godinaStudija', $result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('profesori', $result);
    }

    public function test_vrati_predmet_prijava_returns_html_profesori_when_linked(): void
    {
        $data = $this->createKandidatWithProgram();
        $profesor = Profesor::factory()->create(['ime' => 'Jovan', 'prezime' => 'Jovic']);
        ProfesorPredmet::create([
            'predmet_id' => $data['predmetProgram']->id,
            'profesor_id' => $profesor->id,
        ]);

        $result = $this->service->vratiPredmetPrijava($data['predmetProgram']->id);

        $this->assertStringContainsString('<option', $result['profesori']);
        $this->assertStringContainsString('Jovan', $result['profesori']);
        $this->assertStringContainsString('Jovic', $result['profesori']);
    }

    public function test_vrati_predmet_prijava_returns_all_profesori_when_none_linked(): void
    {
        $data = $this->createKandidatWithProgram();
        Profesor::factory()->count(2)->create();

        $result = $this->service->vratiPredmetPrijava($data['predmetProgram']->id);

        $this->assertStringContainsString('<option', $result['profesori']);
    }

    // =========================================================================
    // vratiKandidataPoBroju()
    // =========================================================================

    public function test_vrati_kandidata_po_broju_returns_html_row(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['brojIndeksa' => '100/2024']);

        $result = $this->service->vratiKandidataPoBroju($data['kandidat']->id);

        $this->assertIsString($result);
        $this->assertStringContainsString('<tr>', $result);
        $this->assertStringContainsString('100/2024', $result);
        $this->assertStringContainsString($data['kandidat']->imeKandidata, $result);
        $this->assertStringContainsString('checkbox', $result);
    }

    // =========================================================================
    // getSvePrijaveZaStudenta()
    // =========================================================================

    public function test_get_sve_prijave_za_studenta_returns_correct_keys(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->getSvePrijaveZaStudenta($data['kandidat']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('prijave', $result);
        $this->assertArrayHasKey('diplomskiRadTema', $result);
        $this->assertArrayHasKey('diplomskiRadOdbrana', $result);
        $this->assertArrayHasKey('diplomskiRadPolaganje', $result);
        $this->assertArrayHasKey('ispiti', $result);
    }

    public function test_get_sve_prijave_za_studenta_returns_related_data(): void
    {
        $data = $this->createKandidatWithProgram();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        PrijavaIspita::create([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        DiplomskiPrijavaTeme::create([
            'kandidat_id' => $data['kandidat']->id,
            'tipStudija_id' => $data['tipStudija']->id,
            'studijskiProgram_id' => $data['program']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'nazivTeme' => 'Test tema',
            'datum' => now()->toDateString(),
            'profesor_id' => $profesor->id,
            'indikatorOdobreno' => 0,
        ]);

        $result = $this->service->getSvePrijaveZaStudenta($data['kandidat']->id);

        $this->assertEquals($data['kandidat']->id, $result['kandidat']->id);
        $this->assertGreaterThanOrEqual(1, $result['prijave']->count());
        $this->assertNotNull($result['diplomskiRadTema']);
    }

    // =========================================================================
    // getCreatePrijavaIspitaStudentData()
    // =========================================================================

    public function test_get_create_prijava_ispita_student_data_returns_correct_keys(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->getCreatePrijavaIspitaStudentData($data['kandidat']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('brojeviIndeksa', $result);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('studijskiProgram', $result);
        $this->assertArrayHasKey('godinaStudija', $result);
        $this->assertArrayHasKey('tipPredmeta', $result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('ispitniRok', $result);
        $this->assertArrayHasKey('profesor', $result);
        $this->assertArrayHasKey('tipPrijave', $result);
        $this->assertArrayHasKey('profesori', $result);
    }

    public function test_get_create_prijava_ispita_student_data_returns_linked_profesori(): void
    {
        $data = $this->createKandidatWithProgram();
        $profesor = Profesor::factory()->create();
        ProfesorPredmet::create([
            'predmet_id' => $data['predmetProgram']->id,
            'profesor_id' => $profesor->id,
        ]);

        $result = $this->service->getCreatePrijavaIspitaStudentData($data['kandidat']->id);

        $this->assertGreaterThanOrEqual(1, $result['profesori']->count());
    }

    public function test_get_create_prijava_ispita_student_data_all_profesori_when_no_predmeti(): void
    {
        // Create a kandidat with no matching predmet_program
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
        ]);
        Profesor::factory()->count(2)->create();

        $result = $this->service->getCreatePrijavaIspitaStudentData($kandidat->id);

        $this->assertGreaterThanOrEqual(2, $result['profesori']->count());
    }

    // =========================================================================
    // Diplomski status on student overview (current service API)
    // =========================================================================

    public function test_get_sve_prijave_za_studenta_returns_existing_diplomski_records(): void
    {
        $data = $this->createKandidatWithProgram();
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();

        $tema = DiplomskiPrijavaTeme::create([
            'kandidat_id' => $data['kandidat']->id,
            'tipStudija_id' => $data['tipStudija']->id,
            'studijskiProgram_id' => $data['program']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'nazivTeme' => 'Tema status pregled',
            'datum' => '2024-07-01',
            'profesor_id' => $profesor->id,
            'indikatorOdobreno' => 1,
        ]);

        $odbrana = DiplomskiPrijavaOdbrane::create([
            'kandidat_id' => $data['kandidat']->id,
            'tipStudija_id' => $data['tipStudija']->id,
            'studijskiProgram_id' => $data['program']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'nazivTeme' => 'Odbrana status pregled',
            'datumPrijave' => '2024-08-01',
            'datumOdbrane' => '2024-09-01',
            'indikatorOdobreno' => 1,
            'temu_odobrio_profesor_id' => $profesor->id,
            'odbranu_odobrio_profesor_id' => $profesor->id,
        ]);

        $polaganje = DiplomskiPolaganje::create([
            'kandidat_id' => $data['kandidat']->id,
            'tipStudija_id' => $data['tipStudija']->id,
            'studijskiProgram_id' => $data['program']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'nazivTeme' => 'Polaganje status pregled',
            'datum' => '2024-10-01',
            'vreme' => '10:00',
            'profesor_id' => $profesor->id,
            'profesor_id_predsednik' => $profesor->id,
            'profesor_id_clan' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        $result = $this->service->getSvePrijaveZaStudenta($data['kandidat']->id);

        $this->assertNotNull($result['diplomskiRadTema']);
        $this->assertNotNull($result['diplomskiRadOdbrana']);
        $this->assertNotNull($result['diplomskiRadPolaganje']);
        $this->assertEquals($tema->id, $result['diplomskiRadTema']->id);
        $this->assertEquals($odbrana->id, $result['diplomskiRadOdbrana']->id);
        $this->assertEquals($polaganje->id, $result['diplomskiRadPolaganje']->id);
    }

    // =========================================================================
    // Privremeni deo — getUnosPrivremeniData()
    // =========================================================================

    public function test_get_unos_privremeni_data_returns_correct_keys(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->getUnosPrivremeniData($data['kandidat']->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('ispiti', $result);
        $this->assertArrayHasKey('polozeniIspiti', $result);
    }

    public function test_get_unos_privremeni_data_returns_predmet_programs_for_student(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->getUnosPrivremeniData($data['kandidat']->id);

        $this->assertGreaterThanOrEqual(1, $result['ispiti']->count());
    }

    // =========================================================================
    // vratiIspitPoId()
    // =========================================================================

    public function test_vrati_ispit_po_id_returns_html_row(): void
    {
        $data = $this->createKandidatWithProgram();

        $result = $this->service->vratiIspitPoId($data['predmetProgram']->id);

        $this->assertIsString($result);
        $this->assertStringContainsString('<tr>', $result);
        $this->assertStringContainsString('checkbox', $result);
        $this->assertStringContainsString('konacnaOcena', $result);
        $this->assertStringContainsString("data-index='{$data['predmetProgram']->id}'", $result);
    }

    // =========================================================================
    // dodajPolozeneIspite()
    // =========================================================================

    public function test_dodaj_polozene_ispite_creates_records(): void
    {
        $data = $this->createKandidatWithProgram();

        $this->service->dodajPolozeneIspite(
            $data['kandidat']->id,
            [$data['predmetProgram']->id],
            [8]
        );

        $this->assertDatabaseHas('polozeni_ispiti', [
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'konacnaOcena' => 8,
            'indikatorAktivan' => 1,
            'statusIspita' => 1,
        ]);
    }

    public function test_dodaj_polozene_ispite_creates_multiple_records(): void
    {
        $data = $this->createKandidatWithProgram();

        // Create a second predmet_program
        $predmet2 = Predmet::factory()->create();
        $pp2 = PredmetProgram::create([
            'predmet_id' => $predmet2->id,
            'studijskiProgram_id' => $data['program']->id,
            'tipStudija_id' => $data['tipStudija']->id,
            'semestar' => 2,
            'espb' => 5,
            'godinaStudija_id' => $data['godinaStudija']->id,
            'tipPredmeta_id' => $data['tipPredmeta']->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $data['skolskaGodina']->id,
        ]);

        $this->service->dodajPolozeneIspite(
            $data['kandidat']->id,
            [0 => $data['predmetProgram']->id, 1 => $pp2->id],
            [0 => 9, 1 => 10]
        );

        $polozeni = PolozeniIspiti::where('kandidat_id', $data['kandidat']->id)->get();
        $this->assertCount(2, $polozeni);
    }

    // =========================================================================
    // storePrijavaIspitaPredmetMany() — bulk registration
    // =========================================================================

    public function test_store_prijava_ispita_predmet_many_creates_records(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();
        TipPrijave::factory()->create();

        $result = $this->service->storePrijavaIspitaPredmetMany([
            'odabir' => [$data['kandidat']->id],
            'predmet_id' => $data['predmet']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'datum' => now()->toDateString(),
            'datum2' => null,
            'tipPrijave_id' => 1,
            'withZapisnik' => false,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('errorArray', $result);
        $this->assertArrayHasKey('duplicateArray', $result);
    }

    public function test_store_prijava_ispita_predmet_many_with_zapisnik(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();
        TipPrijave::factory()->create();

        $result = $this->service->storePrijavaIspitaPredmetMany([
            'odabir' => [$data['kandidat']->id],
            'predmet_id' => $data['predmet']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'datum' => now()->toDateString(),
            'datum2' => null,
            'tipPrijave_id' => 1,
            'withZapisnik' => true,
        ]);

        $this->assertIsArray($result);
        // A zapisnik should have been created
        $this->assertGreaterThanOrEqual(1, ZapisnikOPolaganjuIspita::count());
    }

    public function test_store_prijava_ispita_predmet_many_detects_duplicates(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();
        TipPrijave::factory()->create();

        // Pre-create a prijava for this kandidat+rok+predmetProgram
        PrijavaIspita::create([
            'kandidat_id' => $data['kandidat']->id,
            'predmet_id' => $data['predmetProgram']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        $result = $this->service->storePrijavaIspitaPredmetMany([
            'odabir' => [$data['kandidat']->id],
            'predmet_id' => $data['predmet']->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'datum' => now()->toDateString(),
            'datum2' => null,
            'tipPrijave_id' => 1,
            'withZapisnik' => false,
        ]);

        $this->assertCount(1, $result['duplicateArray']);
    }

    public function test_store_prijava_ispita_predmet_many_skips_when_no_program_match_exists(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();
        TipPrijave::factory()->create();

        $drugiPredmet = Predmet::factory()->create();

        $result = $this->service->storePrijavaIspitaPredmetMany([
            'odabir' => [$data['kandidat']->id],
            'predmet_id' => $drugiPredmet->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'datum' => now()->toDateString(),
            'datum2' => null,
            'tipPrijave_id' => 1,
            'withZapisnik' => false,
        ]);

        $this->assertCount(0, $result['errorArray']);
        $this->assertCount(0, $result['duplicateArray']);
    }

    public function test_store_prijava_ispita_predmet_many_puts_candidate_into_error_array_when_save_fails(): void
    {
        $data = $this->createKandidatWithProgram();
        $data['kandidat']->update(['statusUpisa_id' => 1]);
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();
        TipPrijave::factory()->create();

        $dispatcher = PrijavaIspita::getEventDispatcher();
        PrijavaIspita::saving(static fn () => false);

        try {
            $result = $this->service->storePrijavaIspitaPredmetMany([
                'odabir' => [$data['kandidat']->id],
                'predmet_id' => $data['predmet']->id,
                'rok_id' => $rok->id,
                'profesor_id' => $profesor->id,
                'datum' => now()->toDateString(),
                'datum2' => null,
                'tipPrijave_id' => 1,
                'withZapisnik' => false,
            ]);
        } finally {
            PrijavaIspita::flushEventListeners();
            PrijavaIspita::setEventDispatcher($dispatcher);
        }

        $this->assertCount(1, $result['errorArray']);
    }
}
