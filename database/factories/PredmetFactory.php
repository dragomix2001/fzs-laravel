<?php

namespace Database\Factories;

use App\Models\Predmet;
use App\Models\StudijskiProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class PredmetFactory extends Factory
{
    protected $model = Predmet::class;

    public function definition(): array
    {
        return [
            'sifra' => strtoupper($this->faker->lexify('??###')),
            'naziv' => $this->faker->words(3, true),
            'espb' => $this->faker->numberBetween(3, 9),
            'godinaStudija_id' => $this->faker->numberBetween(1, 4),
            'tipStudija_id' => 1,
            'studijskiProgram_id' => StudijskiProgram::factory(),
        ];
    }
}
