<?php

namespace Database\Factories;

use App\Models\Predmet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PredmetFactory extends Factory
{
    protected $model = Predmet::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->words(3, true),
        ];
    }
}
