<?php

namespace Database\Factories;

use App\Models\DiplomskiPrijavaTeme;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiplomskiPrijavaTemeFactory extends Factory
{
    protected $model = DiplomskiPrijavaTeme::class;

    public function definition(): array
    {
        return [
            'kandidat_id' => 1,
            'tema' => $this->faker->sentence(),
            'mentor' => $this->faker->name(),
            'datum' => $this->faker->date(),
        ];
    }
}
