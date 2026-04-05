<?php

namespace Database\Factories;

use App\Models\DiplomskiPolaganje;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiplomskiPoljanjeFactory extends Factory
{
    protected $model = DiplomskiPolaganje::class;

    public function definition(): array
    {
        return [
            'kandidat_id' => 1,
            'datumPolaganja' => $this->faker->date(),
            'ocena' => $this->faker->numberBetween(6, 10),
        ];
    }
}
