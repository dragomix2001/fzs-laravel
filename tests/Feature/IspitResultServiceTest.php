<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusIspita;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\IspitResultService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IspitResultServiceTest extends TestCase
{
    use DatabaseTransactions;

    private IspitResultService $ispitResultService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ispitResultService = app(IspitResultService::class);
    }

    private function createPredmetProgram(Kandidat $kandidat, Predmet $predmet): PredmetProgram
    {
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

    private function createStatusIspita(string $naziv): StatusIspita
    {
        return StatusIspita::forceCreate([
            'naziv' => $naziv,
            'indikatorAktivan' => 1,
        ]);
    }

    private function createKandidat(
        StudijskiProgram $program,
        TipStudija $tipStudija,
        SkolskaGodUpisa $skolskaGodina,
        StatusStudiranja $statusStudiranja,
        string $brojIndeksa,
        array $overrides = []
    ): Kandidat {
        return Kandidat::factory()->create(array_merge([
            'studijskiProgram_id' => $program->id,
            'tipStudija_id' => $tipStudija->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'statusUpisa_id' => $statusStudiranja->id,
            'brojIndeksa' => $brojIndeksa,
        ], $overrides));
    }

    private function createZapisnikFixtures(): array
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create();
        $statusStudiranja = StatusStudiranja::factory()->create();
        $predmet = Predmet::factory()->create();
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();

        $kandidat = $this->createKandidat(
            $program,
            $tipStudija,
            $skolskaGodina,
            $statusStudiranja,
            '2020/0001'
        );
        $predmetProgram = $this->createPredmetProgram($kandidat, $predmet);
        $prijava = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create([
            'predmet_id' => $predmet->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
            'prijavaIspita_id' => $prijava->id,
        ]);

        ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $zapisnik->id,
            'prijavaIspita_id' => $prijava->id,
            'kandidat_id' => $kandidat->id,
        ]);

        ZapisnikOPolaganju_StudijskiProgram::create([
            'zapisnik_id' => $zapisnik->id,
            'StudijskiProgram_id' => $program->id,
        ]);

        return compact('tipStudija', 'program', 'skolskaGodina', 'statusStudiranja', 'predmet', 'profesor', 'rok', 'kandidat', 'predmetProgram', 'prijava', 'zapisnik');
    }

    private function createPolozeniIspit(
        ZapisnikOPolaganjuIspita $zapisnik,
        Kandidat $kandidat,
        PredmetProgram $predmetProgram,
        PrijavaIspita $prijava,
        array $overrides = []
    ): PolozeniIspiti {
        return PolozeniIspiti::create(array_merge([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'zapisnik_id' => $zapisnik->id,
            'prijava_id' => $prijava->id,
            'indikatorAktivan' => 0,
        ], $overrides));
    }

    public function test_get_zapisnik_pregled_returns_expected_keys(): void
    {
        $fixtures = $this->createZapisnikFixtures();

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertArrayHasKey('zapisnik', $result);
        $this->assertArrayHasKey('studenti', $result);
        $this->assertArrayHasKey('studijskiProgrami', $result);
        $this->assertArrayHasKey('statusIspita', $result);
        $this->assertArrayHasKey('polozeniIspitIds', $result);
        $this->assertArrayHasKey('prijavaIds', $result);
        $this->assertArrayHasKey('kandidati', $result);
    }

    public function test_get_zapisnik_pregled_returns_statuses_and_study_program_links(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $this->createStatusIspita('Положио');
        $this->createStatusIspita('Није положио');

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertCount(2, $result['statusIspita']);
        $this->assertCount(1, $result['studijskiProgrami']);
        $this->assertEquals($fixtures['program']->id, $result['studijskiProgrami']->first()->StudijskiProgram_id);
    }

    public function test_get_zapisnik_pregled_builds_prijava_and_polozeni_maps_for_mixed_programs(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $program2 = StudijskiProgram::factory()->create(['tipStudija_id' => $fixtures['tipStudija']->id]);
        $kandidat2 = $this->createKandidat(
            $program2,
            $fixtures['tipStudija'],
            $fixtures['skolskaGodina'],
            $fixtures['statusStudiranja'],
            '2020/0002'
        );
        $predmetProgram2 = $this->createPredmetProgram($kandidat2, $fixtures['predmet']);
        $prijava2 = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat2->id,
            'predmet_id' => $predmetProgram2->id,
            'profesor_id' => $fixtures['profesor']->id,
            'rok_id' => $fixtures['rok']->id,
        ]);

        ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $fixtures['zapisnik']->id,
            'prijavaIspita_id' => $prijava2->id,
            'kandidat_id' => $kandidat2->id,
        ]);
        ZapisnikOPolaganju_StudijskiProgram::create([
            'zapisnik_id' => $fixtures['zapisnik']->id,
            'StudijskiProgram_id' => $program2->id,
        ]);

        $ispit1 = $this->createPolozeniIspit(
            $fixtures['zapisnik'],
            $fixtures['kandidat'],
            $fixtures['predmetProgram'],
            $fixtures['prijava']
        );
        $ispit2 = $this->createPolozeniIspit($fixtures['zapisnik'], $kandidat2, $predmetProgram2, $prijava2);

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertSame($fixtures['prijava']->id, $result['prijavaIds'][$fixtures['kandidat']->id]);
        $this->assertSame($prijava2->id, $result['prijavaIds'][$kandidat2->id]);
        $this->assertSame($ispit1->id, $result['polozeniIspitIds'][$fixtures['kandidat']->id]);
        $this->assertSame($ispit2->id, $result['polozeniIspitIds'][$kandidat2->id]);
    }

    public function test_get_zapisnik_pregled_skips_students_without_matching_predmet_program(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $kandidatBezPredmeta = $this->createKandidat(
            $fixtures['program'],
            $fixtures['tipStudija'],
            $fixtures['skolskaGodina'],
            $fixtures['statusStudiranja'],
            '2020/0003'
        );

        ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $fixtures['zapisnik']->id,
            'prijavaIspita_id' => $fixtures['prijava']->id,
            'kandidat_id' => $kandidatBezPredmeta->id,
        ]);

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertArrayNotHasKey($kandidatBezPredmeta->id, $result['prijavaIds']);
        $this->assertArrayNotHasKey($kandidatBezPredmeta->id, $result['polozeniIspitIds']);
    }

    public function test_get_zapisnik_pregled_returns_available_kandidati_for_all_programs_in_zapisnik(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $program2 = StudijskiProgram::factory()->create(['tipStudija_id' => $fixtures['tipStudija']->id]);
        $kandidat2 = $this->createKandidat(
            $program2,
            $fixtures['tipStudija'],
            $fixtures['skolskaGodina'],
            $fixtures['statusStudiranja'],
            '2020/0002'
        );
        $this->createPredmetProgram($kandidat2, $fixtures['predmet']);
        ZapisnikOPolaganju_StudijskiProgram::create([
            'zapisnik_id' => $fixtures['zapisnik']->id,
            'StudijskiProgram_id' => $program2->id,
        ]);

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertTrue($result['kandidati']->pluck('id')->contains($fixtures['kandidat']->id));
        $this->assertTrue($result['kandidati']->pluck('id')->contains($kandidat2->id));
    }

    public function test_get_zapisnik_pregled_returns_empty_available_kandidati_when_program_has_no_matching_predmet(): void
    {
        $fixtures = $this->createZapisnikFixtures();

        ZapisnikOPolaganju_StudijskiProgram::where('zapisnik_id', $fixtures['zapisnik']->id)->delete();

        $programBezPredmeta = StudijskiProgram::factory()->create(['tipStudija_id' => $fixtures['tipStudija']->id]);
        ZapisnikOPolaganju_StudijskiProgram::create([
            'zapisnik_id' => $fixtures['zapisnik']->id,
            'StudijskiProgram_id' => $programBezPredmeta->id,
        ]);

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertCount(0, $result['kandidati']);
    }

    public function test_get_zapisnik_pregled_sorts_polozeni_ispiti_by_broj_indeksa(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $kandidat2 = $this->createKandidat(
            $fixtures['program'],
            $fixtures['tipStudija'],
            $fixtures['skolskaGodina'],
            $fixtures['statusStudiranja'],
            '2020/0000'
        );
        $predmetProgram2 = $this->createPredmetProgram($kandidat2, $fixtures['predmet']);
        $prijava2 = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat2->id,
            'predmet_id' => $predmetProgram2->id,
            'profesor_id' => $fixtures['profesor']->id,
            'rok_id' => $fixtures['rok']->id,
        ]);
        ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $fixtures['zapisnik']->id,
            'prijavaIspita_id' => $prijava2->id,
            'kandidat_id' => $kandidat2->id,
        ]);

        $this->createPolozeniIspit($fixtures['zapisnik'], $fixtures['kandidat'], $fixtures['predmetProgram'], $fixtures['prijava']);
        $this->createPolozeniIspit($fixtures['zapisnik'], $kandidat2, $predmetProgram2, $prijava2);

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertSame('2020/0000', $result['polozeniIspiti']->first()->kandidat->brojIndeksa);
        $this->assertSame('2020/0001', $result['polozeniIspiti']->last()->kandidat->brojIndeksa);
    }

    public function test_get_zapisnik_pregled_returns_empty_polozeni_list_when_none_exist(): void
    {
        $fixtures = $this->createZapisnikFixtures();

        $result = $this->ispitResultService->getZapisnikPregled($fixtures['zapisnik']->id);

        $this->assertCount(0, $result['polozeniIspiti']);
    }

    public function test_save_polozeni_ispiti_updates_single_record(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $ispit = $this->createPolozeniIspit(
            $fixtures['zapisnik'],
            $fixtures['kandidat'],
            $fixtures['predmetProgram'],
            $fixtures['prijava']
        );

        $zapisnikId = $this->ispitResultService->savePolozeniIspiti([$ispit->id], [70], [80], [8], [75], [1]);

        $this->assertSame($fixtures['zapisnik']->id, $zapisnikId);
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

    public function test_save_polozeni_ispiti_updates_multiple_records_and_returns_last_zapisnik_id(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $program2 = StudijskiProgram::factory()->create(['tipStudija_id' => $fixtures['tipStudija']->id]);
        $kandidat2 = $this->createKandidat(
            $program2,
            $fixtures['tipStudija'],
            $fixtures['skolskaGodina'],
            $fixtures['statusStudiranja'],
            '2020/0002'
        );
        $predmetProgram2 = $this->createPredmetProgram($kandidat2, $fixtures['predmet']);
        $prijava2 = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat2->id,
            'predmet_id' => $predmetProgram2->id,
            'profesor_id' => $fixtures['profesor']->id,
            'rok_id' => $fixtures['rok']->id,
        ]);

        $ispit1 = $this->createPolozeniIspit($fixtures['zapisnik'], $fixtures['kandidat'], $fixtures['predmetProgram'], $fixtures['prijava']);
        $ispit2 = $this->createPolozeniIspit($fixtures['zapisnik'], $kandidat2, $predmetProgram2, $prijava2);

        $zapisnikId = $this->ispitResultService->savePolozeniIspiti(
            [$ispit1->id, $ispit2->id],
            [65, 85],
            [70, 90],
            [7, 9],
            [68, 88],
            [1, 1]
        );

        $this->assertSame($fixtures['zapisnik']->id, $zapisnikId);
        $this->assertDatabaseHas('polozeni_ispiti', ['id' => $ispit1->id, 'konacnaOcena' => 7]);
        $this->assertDatabaseHas('polozeni_ispiti', ['id' => $ispit2->id, 'konacnaOcena' => 9]);
    }

    public function test_save_polozeni_ispiti_sets_missing_values_to_null_but_activates_record(): void
    {
        $fixtures = $this->createZapisnikFixtures();
        $ispit = $this->createPolozeniIspit(
            $fixtures['zapisnik'],
            $fixtures['kandidat'],
            $fixtures['predmetProgram'],
            $fixtures['prijava'],
            ['ocenaPismeni' => 50, 'ocenaUsmeni' => 50, 'konacnaOcena' => 6]
        );

        $this->ispitResultService->savePolozeniIspiti([$ispit->id], [], [], [], [], []);

        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $ispit->id,
            'ocenaPismeni' => null,
            'ocenaUsmeni' => null,
            'konacnaOcena' => null,
            'brojBodova' => null,
            'statusIspita' => null,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_update_zapisnik_details_updates_fields(): void
    {
        $fixtures = $this->createZapisnikFixtures();

        $this->ispitResultService->updateZapisnikDetails($fixtures['zapisnik']->id, [
            'vreme' => '12:00:00',
            'ucionica' => '202',
            'datum' => '2026-02-01',
            'datum2' => '2026-02-07',
        ]);

        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $fixtures['zapisnik']->id,
            'vreme' => '12:00:00',
            'ucionica' => '202',
            'datum' => '2026-02-01',
            'datum2' => '2026-02-07',
        ]);
    }

    public function test_update_zapisnik_details_overwrites_existing_values(): void
    {
        $fixtures = $this->createZapisnikFixtures();

        $this->ispitResultService->updateZapisnikDetails($fixtures['zapisnik']->id, [
            'vreme' => '09:30:00',
            'ucionica' => 'Amfiteatar 1',
            'datum' => '2026-03-10',
            'datum2' => '2026-03-11',
        ]);

        $this->ispitResultService->updateZapisnikDetails($fixtures['zapisnik']->id, [
            'vreme' => '14:45:00',
            'ucionica' => 'Amfiteatar 2',
            'datum' => '2026-04-15',
            'datum2' => '2026-04-16',
        ]);

        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $fixtures['zapisnik']->id,
            'vreme' => '14:45:00',
            'ucionica' => 'Amfiteatar 2',
            'datum' => '2026-04-15',
            'datum2' => '2026-04-16',
        ]);
    }
}