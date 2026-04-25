<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\IspitZapisnikService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IspitZapisnikServiceTest extends TestCase
{
    use DatabaseTransactions;

    private IspitZapisnikService $ispitZapisnikService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ispitZapisnikService = app(IspitZapisnikService::class);
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

        return compact('kandidat', 'predmet', 'predmetProgram', 'profesor', 'rok', 'prijava');
    }

    public function test_get_zapisnici_for_index_returns_all_non_archived_by_default(): void
    {
        $initialCount = ZapisnikOPolaganjuIspita::where('arhiviran', false)->count();

        ZapisnikOPolaganjuIspita::factory()->count(3)->create(['arhiviran' => false]);
        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => true]);

        $result = $this->ispitZapisnikService->getZapisniciForIndex([]);

        $this->assertCount($initialCount + 3, $result['zapisnici']);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('profesori', $result);
        $this->assertArrayHasKey('aktivniIspitniRok', $result);
    }

    public function test_get_zapisnici_for_index_filters_by_predmet_rok_and_profesor(): void
    {
        $predmet = Predmet::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();
        ZapisnikOPolaganjuIspita::factory()->create([
            'predmet_id' => $predmet->id,
            'rok_id' => $rok->id,
            'profesor_id' => $profesor->id,
            'arhiviran' => false,
        ]);
        ZapisnikOPolaganjuIspita::factory()->count(3)->create(['arhiviran' => false]);

        $result = $this->ispitZapisnikService->getZapisniciForIndex([
            'filter_predmet_id' => $predmet->id,
            'filter_rok_id' => $rok->id,
            'filter_profesor_id' => $profesor->id,
        ]);

        $this->assertCount(1, $result['zapisnici']);
    }

    public function test_get_create_zapisnik_data_returns_expected_keys(): void
    {
        Predmet::factory()->count(2)->create();
        Profesor::factory()->count(2)->create();
        AktivniIspitniRokovi::factory()->create(['indikatorAktivan' => 1]);

        $result = $this->ispitZapisnikService->getCreateZapisnikData();

        $this->assertArrayHasKey('aktivniIspitniRok', $result);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('profesori', $result);
    }

    public function test_get_create_zapisnik_data_returns_null_when_no_aktivni_rokovi(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        AktivniIspitniRokovi::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $result = $this->ispitZapisnikService->getCreateZapisnikData();

        $this->assertNull($result['aktivniIspitniRok']);
    }

    public function test_get_zapisnik_predmet_data_returns_predmeti_and_profesori(): void
    {
        $fixtures = $this->buildZapisnikFixtures();

        $result = $this->ispitZapisnikService->getZapisnikPredmetData($fixtures['rok']->id);

        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('profesori', $result);
        $this->assertEquals(1, $result['predmeti']->count());
        $this->assertEquals(1, $result['profesori']->count());
    }

    public function test_get_zapisnik_studenti_returns_empty_message_when_no_students(): void
    {
        $predmet = Predmet::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();
        $profesor = Profesor::factory()->create();

        $result = $this->ispitZapisnikService->getZapisnikStudenti($predmet->id, $rok->id, $profesor->id);

        $this->assertNotEmpty($result['message']);
        $this->assertCount(0, $result['kandidati']);
        $this->assertNull($result['prijavaId']);
    }

    public function test_get_zapisnik_studenti_returns_registered_students(): void
    {
        $fixtures = $this->buildZapisnikFixtures();

        $result = $this->ispitZapisnikService->getZapisnikStudenti(
            $fixtures['predmet']->id,
            $fixtures['rok']->id,
            $fixtures['profesor']->id
        );

        $this->assertEmpty($result['message']);
        $this->assertCount(1, $result['kandidati']);
        $this->assertEquals($fixtures['prijava']->id, $result['prijavaId']);
    }

    public function test_get_arhivirani_zapisnici_returns_only_archived(): void
    {
        $initialCount = ZapisnikOPolaganjuIspita::where('arhiviran', true)->count();

        ZapisnikOPolaganjuIspita::factory()->count(2)->create(['arhiviran' => true]);
        ZapisnikOPolaganjuIspita::factory()->count(3)->create(['arhiviran' => false]);

        $result = $this->ispitZapisnikService->getArhiviraniZapisnici();

        $this->assertArrayHasKey('arhiviraniZapisnici', $result);
        $this->assertCount($initialCount + 2, $result['arhiviraniZapisnici']);
    }

    public function test_arhiviraj_zapisnik_sets_arhiviran_true(): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create(['arhiviran' => false]);

        $this->ispitZapisnikService->arhivirajZapisnik($zapisnik->id);

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

        $this->ispitZapisnikService->arhivirajZapisnikeZaRok($rok->id);

        foreach (ZapisnikOPolaganjuIspita::where('rok_id', $rok->id)->get() as $zapisnik) {
            $this->assertTrue((bool) $zapisnik->arhiviran);
        }
    }
}
