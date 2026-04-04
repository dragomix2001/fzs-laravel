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
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\IspitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function makeKandidat(array $overrides = []): Kandidat
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1]);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);

        return Kandidat::factory()->create(array_merge([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
        ], $overrides));
    }

    private function makePredmetProgram(Kandidat $kandidat, ?Predmet $predmet = null): PredmetProgram
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

    private function makePolozeniIspit(Kandidat $kandidat, array $overrides = []): PolozeniIspiti
    {
        $predmet = Predmet::factory()->create();
        $predmetProgram = $this->makePredmetProgram($kandidat, $predmet);
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();
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
        ]);

        return PolozeniIspiti::create(array_merge([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'prijava_id' => $prijava->id,
            'zapisnik_id' => $zapisnik->id,
            'indikatorAktivan' => 0,
        ], $overrides));
    }

    public function test_polozeni_ispit_se_cuva_u_bazi(): void
    {
        $kandidat = $this->makeKandidat();
        $ispit = $this->makePolozeniIspit($kandidat);

        $this->assertDatabaseHas('polozeni_ispiti', [
            'kandidat_id' => $kandidat->id,
            'id' => $ispit->id,
        ]);
    }

    public function test_unos_ocene_azurira_polozeni_ispit(): void
    {
        $kandidat = $this->makeKandidat();
        $ispit = $this->makePolozeniIspit($kandidat);

        $ispit->konacnaOcena = 8;
        $ispit->ocenaPismeni = 7;
        $ispit->ocenaUsmeni = 9;
        $ispit->indikatorAktivan = 1;
        $ispit->save();

        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $ispit->id,
            'konacnaOcena' => 8,
            'ocenaPismeni' => 7,
            'ocenaUsmeni' => 9,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_save_polozeni_ispiti_azurira_vise_zapisa_odjednom(): void
    {
        $ispitService = app(IspitService::class);

        $kandidat1 = $this->makeKandidat();
        $kandidat2 = $this->makeKandidat();

        $predmet = Predmet::factory()->create();
        $predmetProgram1 = $this->makePredmetProgram($kandidat1, $predmet);
        $predmetProgram2 = $this->makePredmetProgram($kandidat2, $predmet);
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();
        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create([
            'predmet_id' => $predmet->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        $prijava1 = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat1->id,
            'predmet_id' => $predmetProgram1->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        $prijava2 = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat2->id,
            'predmet_id' => $predmetProgram2->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        $ispit1 = PolozeniIspiti::create([
            'kandidat_id' => $kandidat1->id,
            'predmet_id' => $predmetProgram1->id,
            'prijava_id' => $prijava1->id,
            'zapisnik_id' => $zapisnik->id,
            'indikatorAktivan' => 0,
        ]);

        $ispit2 = PolozeniIspiti::create([
            'kandidat_id' => $kandidat2->id,
            'predmet_id' => $predmetProgram2->id,
            'prijava_id' => $prijava2->id,
            'zapisnik_id' => $zapisnik->id,
            'indikatorAktivan' => 0,
        ]);

        $ispitService->savePolozeniIspiti(
            [$ispit1->id, $ispit2->id],
            [7, 8],
            [8, 9],
            [8, 9],
            [75, 85],
            [1, 1]
        );

        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $ispit1->id,
            'konacnaOcena' => 8,
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $ispit2->id,
            'konacnaOcena' => 9,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_ocena_mora_biti_izmedju_5_i_10(): void
    {
        $kandidat = $this->makeKandidat();
        $ispit = $this->makePolozeniIspit($kandidat);

        $validneOcene = [5, 6, 7, 8, 9, 10];

        foreach ($validneOcene as $ocena) {
            $ispit->konacnaOcena = $ocena;
            $ispit->save();

            $this->assertDatabaseHas('polozeni_ispiti', [
                'id' => $ispit->id,
                'konacnaOcena' => $ocena,
            ]);
        }
    }

    public function test_polozeni_ispit_ima_vezu_na_kandidata(): void
    {
        $kandidat = $this->makeKandidat();
        $ispit = $this->makePolozeniIspit($kandidat);

        $this->assertEquals($kandidat->id, $ispit->kandidat_id);
        $this->assertInstanceOf(Kandidat::class, $ispit->kandidat);
    }
}
