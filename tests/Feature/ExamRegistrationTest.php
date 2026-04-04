<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamRegistrationTest extends TestCase
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

    public function test_prijava_ispita_cuva_u_bazi(): void
    {
        $kandidat = $this->makeKandidat();
        $predmet = Predmet::factory()->create();
        $predmetProgram = $this->makePredmetProgram($kandidat, $predmet);
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();

        $prijava = PrijavaIspita::create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        $this->assertDatabaseHas('prijava_ispita', [
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'rok_id' => $rok->id,
        ]);

        $this->assertNotNull($prijava->id);
    }

    public function test_kandidat_moze_imati_vise_prijava_za_razlicite_predmete(): void
    {
        $kandidat = $this->makeKandidat();
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();

        $predmet1 = Predmet::factory()->create();
        $predmet2 = Predmet::factory()->create();
        $predmetProgram1 = $this->makePredmetProgram($kandidat, $predmet1);
        $predmetProgram2 = $this->makePredmetProgram($kandidat, $predmet2);

        PrijavaIspita::create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram1->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        PrijavaIspita::create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram2->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ]);

        $this->assertDatabaseCount('prijava_ispita', 2);
    }

    public function test_prijava_ispita_factory_kreira_validan_zapis(): void
    {
        $prijava = PrijavaIspita::factory()->create();

        $this->assertDatabaseHas('prijava_ispita', [
            'id' => $prijava->id,
        ]);

        $this->assertNotNull($prijava->kandidat_id);
        $this->assertNotNull($prijava->predmet_id);
        $this->assertNotNull($prijava->profesor_id);
        $this->assertNotNull($prijava->rok_id);
    }

    public function test_prijava_ispita_ima_vezu_na_kandidata(): void
    {
        $kandidat = $this->makeKandidat();
        $prijava = PrijavaIspita::factory()->create(['kandidat_id' => $kandidat->id]);

        $this->assertEquals($kandidat->id, $prijava->kandidat_id);
        $this->assertInstanceOf(Kandidat::class, $prijava->kandidat);
    }

    public function test_prijava_ispita_ima_vezu_na_ispitni_rok(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create();
        $prijava = PrijavaIspita::factory()->create(['rok_id' => $rok->id]);

        $this->assertEquals($rok->id, $prijava->rok_id);
    }

    public function test_vise_razlicitih_kandidata_mogu_biti_prijavljeni_za_isti_predmet_i_rok(): void
    {
        $predmet = Predmet::factory()->create();
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();

        $kandidati = collect(range(1, 3))->map(function () {
            return $this->makeKandidat();
        });

        foreach ($kandidati as $kandidat) {
            $predmetProgram = $this->makePredmetProgram($kandidat, $predmet);
            PrijavaIspita::factory()->create([
                'kandidat_id' => $kandidat->id,
                'predmet_id' => $predmetProgram->id,
                'profesor_id' => $profesor->id,
                'rok_id' => $rok->id,
            ]);
        }

        $predmetProgramIds = PredmetProgram::where('predmet_id', $predmet->id)->pluck('id');
        $count = PrijavaIspita::whereIn('predmet_id', $predmetProgramIds)
            ->where('rok_id', $rok->id)
            ->count();

        $this->assertEquals(3, $count);
    }
}
