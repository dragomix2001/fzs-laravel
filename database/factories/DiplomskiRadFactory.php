<?php

namespace Database\Factories;

use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\DiplomskiRad;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiplomskiRadFactory extends Factory
{
    protected $model = DiplomskiRad::class;

    public function definition(): array
    {
        return [
            'kandidat_id' => Kandidat::factory(),
            'predmet_id' => PredmetProgram::factory(),
            'mentor_id' => Profesor::factory(),
            'predsednik_id' => Profesor::factory(),
            'clan_id' => Profesor::factory(),
            'naziv' => $this->faker->sentence(),
            'ocenaOpis' => 'Odlican',
            'ocenaBroj' => 10,
            'datumPrijave' => $this->faker->date(),
            'datumOdbrane' => $this->faker->date(),
        ];
    }
}
