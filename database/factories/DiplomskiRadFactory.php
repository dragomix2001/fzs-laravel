<?php

namespace Database\Factories;

use App\Models\DiplomskiRad;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiplomskiRadFactory extends Factory
{
    protected $model = DiplomskiRad::class;

    public function definition(): array
    {
        return [
            'kandidat_id' => 1,
            'tema' => $this->faker->sentence(),
            'mentor' => $this->faker->name(),
            'datumPrijave' => $this->faker->date(),
        ];
    }
}
