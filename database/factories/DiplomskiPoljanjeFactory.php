<?php

namespace Database\Factories;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiplomskiPoljanjeFactory extends Factory
{
    protected $model = DiplomskiPolaganje::class;

    public function definition(): array
    {
        $kandidat = Kandidat::factory()->create();

        return [
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'kandidat_id' => $kandidat->id,
            'predmet_id' => PredmetProgram::factory(),
            'nazivTeme' => $this->faker->sentence(),
            'datum' => $this->faker->date(),
            'vreme' => '10:00:00',
            'profesor_id' => Profesor::factory(),
            'profesor_id_predsednik' => Profesor::factory(),
            'profesor_id_clan' => Profesor::factory(),
            'rok_id' => AktivniIspitniRokovi::factory(),
            'brojBodova' => $this->faker->numberBetween(60, 100),
            'ocena' => $this->faker->numberBetween(6, 10),
        ];
    }
}
