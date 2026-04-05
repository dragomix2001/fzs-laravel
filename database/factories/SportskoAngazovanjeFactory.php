<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Kandidat;
use App\Models\Sport;
use App\Models\SportskoAngazovanje;
use Illuminate\Database\Eloquent\Factories\Factory;

class SportskoAngazovanjeFactory extends Factory
{
    protected $model = SportskoAngazovanje::class;

    public function definition(): array
    {
        return [
            'nazivKluba' => $this->faker->company(),
            'odDoGodina' => $this->faker->year().' - '.$this->faker->year(),
            'ukupnoGodina' => $this->faker->numberBetween(1, 10),
            'sport_id' => Sport::factory(),
            'kandidat_id' => Kandidat::factory(),
        ];
    }
}
