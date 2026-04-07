<?php

namespace Database\Factories;

use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\DiplomskiPrijavaTeme;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiplomskiPrijavaTemeFactory extends Factory
{
    protected $model = DiplomskiPrijavaTeme::class;

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
            'profesor_id' => Profesor::factory(),
            'indikatorOdobreno' => 0,
        ];
    }
}
