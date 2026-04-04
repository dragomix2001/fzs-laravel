<?php

namespace Tests\Feature;

use App\DTOs\ZapisnikData;
use App\Jobs\GenerateZapisnikPdfJob;
use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\IspitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IspitServiceTest extends TestCase
{
    use RefreshDatabase;

    private IspitService $ispitService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ispitService = app(IspitService::class);
    }

    private function createPredmetProgram(Kandidat $kandidat, ?Predmet $predmet = null): PredmetProgram
    {
        $predmet ??= Predmet::factory()->create();
        $tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::forceCreate([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        return PredmetProgram::create([
            'predmet_id' => $predmet->id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $kandidat->godinaStudija_id,
            'tipPredmeta_id' => $tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 0,
            'vezbe' => 0,
            'skolskaGodina_id' => $kandidat->skolskaGodinaUpisa_id,
        ]);
    }

    private function buildZapisnikFixtures(): array
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create();
        $statusStudiranja = StatusStudiranja::factory()->create();

        $kandidat = Kandidat::factory()->create([
            'studijskiProgram_id' => $program->id,
            'tipStudija_id' => $tipStudija->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'statusUpisa_id' => $statusStudiranja->id,
        ]);

        $predmet = Predmet::factory()->create();
        $predmetProgram = $this->createPredmetProgram($kandidat, $predmet);

        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create(['indikatorAktivan' => 1]);

        $prijava = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        return compact('tipStudija', 'program', 'skolskaGodina', 'statusStudiranja', 'kandidat', 'predmet', 'predmetProgram', 'profesor', 'rok', 'prijava');
    }

    private function createPolozeniIspit(array $attrs = []): PolozeniIspiti
    {
        $kandidat = isset($attrs['kandidat_id']) ? Kandidat::findOrFail($attrs['kandidat_id']) : Kandidat::factory()->create();
        $kandidatId = $kandidat->id;
        $predmet = Predmet::factory()->create();
        $predmetProgram = $this->createPredmetProgram($kandidat, $predmet);
        $prijava = PrijavaIspita::factory()->create([
            'predmet_id' => $predmetProgram->id,
            'kandidat_id' => $kandidatId,
        ]);

        return PolozeniIspiti::create(array_merge([
            'kandidat_id' => $kandidatId,
            'predmet_id' => $predmetProgram->id,
            'zapisnik_id' => null,
            'prijava_id' => $prijava->id,
            'indikatorAktivan' => 0,
        ], $attrs));
    }

    private function createZapisnikStudent(ZapisnikOPolaganjuIspita $zapisnik, Kandidat $kandidat): ZapisnikOPolaganju_Student
    {
        $predmetProgram = $this->createPredmetProgram($kandidat, Predmet::findOrFail($zapisnik->predmet_id));
        $prijava = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'rok_id' => $zapisnik->rok_id,
        ]);

        return ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $zapisnik->id,
            'prijavaIspita_id' => $prijava->id,
            'kandidat_id' => $kandidat->id,
        ]);
    }

    public function test_get_zapisnici_for_index_returns_all_non_archived_by_default(): void
    {
        ZapisnikOPolaganjuIspita::factory()->count(3)->create(['arhiviran' => false]);
        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => true]);

        $result = $this->ispitService->getZapisniciForIndex([]);

        $this->assertCount(3, $result['zapisnici']);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('profesori', $result);
        $this->assertArrayHasKey('aktivniIspitniRok', $result);
    }

    public function test_get_zapisnici_for_index_filters_by_predmet_id(): void
    {
        $predmet = Predmet::factory()->create();
        ZapisnikOPolaganjuIspita::factory()->create(['predmet_id' => $predmet->id, 'arhiviran' => false]);
        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => false]);

        $result = $this->ispitService->getZapisniciForIndex(['filter_predmet_id' => $predmet->id]);

        $this->assertCount(1, $result['zapisnici']);
        $this->assertEquals($predmet->id, $result['zapisnici']->first()->predmet_id);
    }

    public function test_get_zapisnici_for_index_filters_by_rok_id(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create();
        ZapisnikOPolaganjuIspita::factory()->create(['rok_id' => $rok->id, 'arhiviran' => false]);
        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => false]);

        $result = $this->ispitService->getZapisniciForIndex(['filter_rok_id' => $rok->id]);

        $this->assertCount(1, $result['zapisnici']);
        $this->assertEquals($rok->id, $result['zapisnici']->first()->rok_id);
    }

    public function test_get_zapisnici_for_index_filters_by_profesor_id(): void
    {
        $profesor = Profesor::factory()->create();
        ZapisnikOPolaganjuIspita::factory()->create(['profesor_id' => $profesor->id, 'arhiviran' => false]);
        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => false]);

        $result = $this->ispitService->getZapisniciForIndex(['filter_profesor_id' => $profesor->id]);

        $this->assertCount(1, $result['zapisnici']);
        $this->assertEquals($profesor->id, $result['zapisnici']->first()->profesor_id);
    }

    public function test_get_create_zapisnik_data_returns_expected_keys(): void
    {
        Predmet::factory()->count(2)->create();
        Profesor::factory()->count(2)->create();
        AktivniIspitniRokovi::factory()->create(['indikatorAktivan' => 1]);

        $result = $this->ispitService->getCreateZapisnikData();

        $this->assertArrayHasKey('aktivniIspitniRok', $result);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('profesori', $result);
    }

    public function test_get_create_zapisnik_data_returns_null_when_no_aktivni_rokovi(): void
    {
        $result = $this->ispitService->getCreateZapisnikData();

        $this->assertNull($result['aktivniIspitniRok']);
    }

    public function test_get_arhivirani_zapisnici_returns_only_archived(): void
    {
        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => true]);
        ZapisnikOPolaganjuIspita::factory()->count(3)->create(['arhiviran' => false]);

        $result = $this->ispitService->getArhiviraniZapisnici();

        $this->assertArrayHasKey('arhiviraniZapisnici', $result);
        $this->assertCount(2, $result['arhiviraniZapisnici']);
    }

    public function test_get_zapisnik_predmet_data_returns_predmeti_and_profesori(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create();
        $predmet = Predmet::factory()->create();
        $profesor = Profesor::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $predmetProgram = $this->createPredmetProgram($kandidat, $predmet);

        PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat->id,
            'rok_id' => $rok->id,
            'predmet_id' => $predmetProgram->id,
            'profesor_id' => $profesor->id,
        ]);

        $result = $this->ispitService->getZapisnikPredmetData($rok->id);

        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('profesori', $result);
        $this->assertEquals(1, $result['predmeti']->count());
    }

    public function test_get_zapisnik_studenti_returns_empty_message_when_no_students(): void
    {
        $predmet = Predmet::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $result = $this->ispitService->getZapisnikStudenti($predmet->id, $rok->id, $profesor->id);

        $this->assertNotEmpty($result['message']);
        $this->assertCount(0, $result['kandidati']);
        $this->assertNull($result['prijavaId']);
    }

    public function test_get_zapisnik_studenti_returns_kandidati_with_prijave(): void
    {
        $f = $this->buildZapisnikFixtures();

        $result = $this->ispitService->getZapisnikStudenti(
            $f['predmet']->id,
            $f['rok']->id,
            $f['profesor']->id
        );

        $this->assertEmpty($result['message']);
        $this->assertCount(1, $result['kandidati']);
        $this->assertEquals($f['prijava']->id, $result['prijavaId']);
    }

    public function test_create_zapisnik_creates_zapisnik_and_children(): void
    {
        $f = $this->buildZapisnikFixtures();

        $data = [
            'predmet_id' => $f['predmet']->id,
            'profesor_id' => $f['profesor']->id,
            'rok_id' => $f['rok']->id,
            'datum' => now()->toDateString(),
            'datum2' => now()->addDays(7)->toDateString(),
            'vreme' => '10:00:00',
            'ucionica' => '101',
            'prijavaIspita_id' => $f['prijava']->id,
        ];

        $odabir = [$f['kandidat']->id];

        $zapisnik = $this->ispitService->createZapisnik($data, $odabir);

        $this->assertInstanceOf(ZapisnikOPolaganjuIspita::class, $zapisnik);
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
        $this->assertDatabaseHas('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $f['kandidat']->id,
        ]);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $f['kandidat']->id,
        ]);
        $this->assertDatabaseHas('zapisnik_o_polaganju__studijski_program', [
            'zapisnik_id' => $zapisnik->id,
            'StudijskiProgram_id' => $f['program']->id,
        ]);
    }

    public function test_store_zapisnik_creates_via_dto(): void
    {
        $f = $this->buildZapisnikFixtures();

        $dto = new ZapisnikData(
            predmetId: $f['predmet']->id,
            profesorId: $f['profesor']->id,
            rokId: $f['rok']->id,
            datum: now()->toDateString(),
            datum2: now()->addDays(7)->toDateString(),
            vreme: '10:00:00',
            ucionica: '101',
            prijavaIspitaId: $f['prijava']->id,
            studentiIds: [$f['kandidat']->id],
        );

        $zapisnik = $this->ispitService->storeZapisnik($dto);

        $this->assertInstanceOf(ZapisnikOPolaganjuIspita::class, $zapisnik);
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
    }

    public function test_delete_zapisnik_deletes_zapisnik_and_children(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat = Kandidat::factory()->create();

        $this->createZapisnikStudent($zapisnik, $kandidat);
        ZapisnikOPolaganju_StudijskiProgram::create([
            'zapisnik_id' => $zapisnik->id,
            'StudijskiProgram_id' => StudijskiProgram::factory()->create()->id,
        ]);

        $this->ispitService->deleteZapisnik($zapisnik->id);

        $this->assertDatabaseMissing('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
        $this->assertDatabaseMissing('zapisnik_o_polaganju__student', ['zapisnik_id' => $zapisnik->id]);
        $this->assertDatabaseMissing('zapisnik_o_polaganju__studijski_program', ['zapisnik_id' => $zapisnik->id]);
    }

    public function test_delete_zapisnik_with_children_removes_all_related_records(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat = Kandidat::factory()->create();

        $this->createZapisnikStudent($zapisnik, $kandidat);

        $this->ispitService->deleteZapisnikWithChildren($zapisnik->id);

        $this->assertDatabaseMissing('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
        $this->assertDatabaseMissing('zapisnik_o_polaganju__student', ['zapisnik_id' => $zapisnik->id]);
    }

    public function test_save_polozeni_ispiti_updates_records(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat = Kandidat::factory()->create();

        $ispit = $this->createPolozeniIspit([
            'kandidat_id' => $kandidat->id,
            'zapisnik_id' => $zapisnik->id,
            'indikatorAktivan' => 0,
        ]);

        $zapisnikId = $this->ispitService->savePolozeniIspiti(
            [$ispit->id],
            [70],
            [80],
            [8],
            [75],
            [1]
        );

        $this->assertEquals($zapisnik->id, $zapisnikId);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $ispit->id,
            'ocenaPismeni' => 70,
            'ocenaUsmeni' => 80,
            'konacnaOcena' => 8,
            'brojBodova' => 75,
            'statusIspita' => 1,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_delete_polozeni_ispit_with_brisi_zapisnik_1_removes_from_zapisnik(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat = Kandidat::factory()->create();

        $this->createZapisnikStudent($zapisnik, $kandidat);

        $ispit = $this->createPolozeniIspit([
            'kandidat_id' => $kandidat->id,
            'zapisnik_id' => $zapisnik->id,
            'indikatorAktivan' => 1,
        ]);

        $this->ispitService->deletePolozeniIspit($ispit->id, 1);

        $this->assertDatabaseMissing('polozeni_ispiti', ['id' => $ispit->id]);
        $this->assertDatabaseMissing('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $kandidat->id,
        ]);
        $this->assertDatabaseMissing('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
    }

    public function test_delete_polozeni_ispit_with_brisi_zapisnik_0_resets_grades(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat = Kandidat::factory()->create();

        $ispit = $this->createPolozeniIspit([
            'kandidat_id' => $kandidat->id,
            'zapisnik_id' => $zapisnik->id,
            'indikatorAktivan' => 1,
            'ocenaPismeni' => 7,
            'ocenaUsmeni' => 8,
            'konacnaOcena' => 8,
            'brojBodova' => 75,
            'statusIspita' => 1,
        ]);

        $this->ispitService->deletePolozeniIspit($ispit->id, 0);

        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $ispit->id,
            'indikatorAktivan' => 0,
            'ocenaPismeni' => 0,
            'ocenaUsmeni' => 0,
            'konacnaOcena' => 0,
            'brojBodova' => 0,
            'statusIspita' => 0,
        ]);
    }

    public function test_delete_privremeni_ispit_deletes_record(): void
    {
        $kandidat = Kandidat::factory()->create();

        $ispit = $this->createPolozeniIspit([
            'kandidat_id' => $kandidat->id,
            'zapisnik_id' => null,
        ]);

        $this->ispitService->deletePrivremeniIspit($ispit->id);

        $this->assertDatabaseMissing('polozeni_ispiti', ['id' => $ispit->id]);
    }

    public function test_add_student_to_zapisnik_adds_new_student(): void
    {
        $f = $this->buildZapisnikFixtures();

        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create([
            'predmet_id' => $f['predmet']->id,
            'profesor_id' => $f['profesor']->id,
            'rok_id' => $f['rok']->id,
        ]);

        $kandidat2 = Kandidat::factory()->create([
            'studijskiProgram_id' => $f['program']->id,
            'tipStudija_id' => $f['tipStudija']->id,
            'skolskaGodinaUpisa_id' => $f['skolskaGodina']->id,
            'statusUpisa_id' => $f['statusStudiranja']->id,
        ]);

        $this->ispitService->addStudentToZapisnik($zapisnik->id, [$kandidat2->id]);

        $this->assertDatabaseHas('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $kandidat2->id,
        ]);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $kandidat2->id,
        ]);
    }

    public function test_add_student_to_zapisnik_skips_existing_student(): void
    {
        $f = $this->buildZapisnikFixtures();

        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create([
            'predmet_id' => $f['predmet']->id,
            'profesor_id' => $f['profesor']->id,
            'rok_id' => $f['rok']->id,
        ]);

        $this->createZapisnikStudent($zapisnik, $f['kandidat']);

        $this->ispitService->addStudentToZapisnik($zapisnik->id, [$f['kandidat']->id]);

        $count = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $f['kandidat']->id,
        ])->count();

        $this->assertEquals(1, $count);
    }

    public function test_remove_student_from_zapisnik_returns_false_when_students_remain(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat1 = Kandidat::factory()->create();
        $kandidat2 = Kandidat::factory()->create();

        $this->createZapisnikStudent($zapisnik, $kandidat1);
        $this->createZapisnikStudent($zapisnik, $kandidat2);

        $this->createPolozeniIspit([
            'kandidat_id' => $kandidat1->id,
            'zapisnik_id' => $zapisnik->id,
        ]);

        $result = $this->ispitService->removeStudentFromZapisnik($zapisnik->id, $kandidat1->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
        $this->assertDatabaseMissing('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $zapisnik->id,
            'kandidat_id' => $kandidat1->id,
        ]);
    }

    public function test_remove_student_from_zapisnik_returns_true_and_deletes_zapisnik_when_no_students_left(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();
        $kandidat = Kandidat::factory()->create();

        $this->createZapisnikStudent($zapisnik, $kandidat);

        $this->createPolozeniIspit([
            'kandidat_id' => $kandidat->id,
            'zapisnik_id' => $zapisnik->id,
        ]);

        $result = $this->ispitService->removeStudentFromZapisnik($zapisnik->id, $kandidat->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('zapisnik_o_polaganju_ispita', ['id' => $zapisnik->id]);
    }

    public function test_get_priznavanje_data_returns_kandidat_and_predmet_program(): void
    {
        $f = $this->buildZapisnikFixtures();

        $result = $this->ispitService->getPriznavanjeData($f['kandidat']->id);

        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('predmetProgram', $result);
        $this->assertEquals($f['kandidat']->id, $result['kandidat']->id);
    }

    public function test_store_priznati_ispiti_creates_polozeni_ispiti_records(): void
    {
        $kandidat = Kandidat::factory()->create();
        $predmetProgram1 = $this->createPredmetProgram($kandidat);
        $predmetProgram2 = $this->createPredmetProgram($kandidat);

        $this->ispitService->storePriznatiIspiti(
            kandidatId: $kandidat->id,
            predmetIds: [$predmetProgram1->id, $predmetProgram2->id],
            konacneOcene: [8, 9]
        );

        $this->assertDatabaseHas('polozeni_ispiti', [
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram1->id,
            'konacnaOcena' => 8,
            'statusIspita' => 5,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram2->id,
            'konacnaOcena' => 9,
            'statusIspita' => 5,
        ]);
    }

    public function test_store_priznati_ispiti_does_nothing_when_predmet_ids_null(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->ispitService->storePriznatiIspiti(
            kandidatId: $kandidat->id,
            predmetIds: null,
            konacneOcene: []
        );

        $this->assertDatabaseCount('polozeni_ispiti', 0);
    }

    public function test_delete_priznat_ispit_deletes_record_and_returns_kandidat_id(): void
    {
        $kandidat = Kandidat::factory()->create();

        $ispit = $this->createPolozeniIspit([
            'kandidat_id' => $kandidat->id,
            'zapisnik_id' => null,
            'konacnaOcena' => 8,
            'statusIspita' => 5,
            'indikatorAktivan' => 1,
        ]);

        $returnedKandidatId = $this->ispitService->deletePriznatIspit($ispit->id);

        $this->assertEquals($kandidat->id, $returnedKandidatId);
        $this->assertDatabaseMissing('polozeni_ispiti', ['id' => $ispit->id]);
    }

    public function test_update_zapisnik_details_updates_fields(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create([
            'vreme' => '08:00:00',
            'ucionica' => '101',
            'datum' => '2026-01-01',
            'datum2' => '2026-01-07',
        ]);

        $this->ispitService->updateZapisnikDetails($zapisnik->id, [
            'vreme' => '12:00:00',
            'ucionica' => '202',
            'datum' => '2026-02-01',
            'datum2' => '2026-02-07',
        ]);

        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $zapisnik->id,
            'vreme' => '12:00:00',
            'ucionica' => '202',
            'datum' => '2026-02-01',
            'datum2' => '2026-02-07',
        ]);
    }

    public function test_arhiviraj_zapisnik_sets_arhiviran_true(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create(['arhiviran' => false]);

        $this->ispitService->arhivirajZapisnik($zapisnik->id);

        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $zapisnik->id,
            'arhiviran' => true,
        ]);
    }

    public function test_arhiviraj_zapisnike_za_rok_archives_all_for_rok(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create();
        ZapisnikOPolaganjuIspita::factory()->count(3)->create([
            'rok_id' => $rok->id,
            'arhiviran' => false,
        ]);

        $this->ispitService->arhivirajZapisnikeZaRok($rok->id);

        $arhivirani = ZapisnikOPolaganjuIspita::where('rok_id', $rok->id)->get();
        foreach ($arhivirani as $z) {
            $this->assertTrue((bool) $z->arhiviran);
        }
    }

    public function test_generate_pdf_async_dispatches_job(): void
    {
        Queue::fake();

        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create();

        $storagePath = $this->ispitService->generatePdfAsync($zapisnik->id);

        Queue::assertPushed(GenerateZapisnikPdfJob::class, function ($job) use ($zapisnik, $storagePath) {
            return $job->zapisnikId === $zapisnik->id && $job->storagePath === $storagePath;
        });

        $this->assertStringStartsWith('pdfs/zapisnik_'.$zapisnik->id.'_', $storagePath);
    }
}
