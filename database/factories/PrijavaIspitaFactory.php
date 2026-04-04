<?php

namespace Database\Factories;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\TipPredmeta;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrijavaIspitaFactory extends Factory
{
    protected $model = PrijavaIspita::class;

    public function definition(): array
    {
        return [
            'kandidat_id' => Kandidat::factory(),
            'predmet_id' => function (array $attributes) {
                $kandidat = Kandidat::findOrFail($attributes['kandidat_id']);
                $predmet = Predmet::factory()->create();
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
                    'godinaStudija_id' => $kandidat->godinaStudija_id ?? 1,
                    'tipPredmeta_id' => $tipPredmeta->id,
                    'statusPredmeta' => 1,
                    'predavanja' => 0,
                    'vezbe' => 0,
                    'skolskaGodina_id' => $kandidat->skolskaGodinaUpisa_id,
                ])->id;
            },
            'profesor_id' => Profesor::factory(),
            'rok_id' => AktivniIspitniRokovi::factory(),
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'datum2' => now()->addDays(7)->toDateString(),
            'vreme' => '10:00:00',
            'tipPrijave_id' => 1,
        ];
    }
}
