<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bodovanje;
use Illuminate\Database\Eloquent\Factories\Factory;

class BodovanjeFactory extends Factory
{
    protected $model = Bodovanje::class;

    public function definition(): array
    {
        return [
            'opisnaOcena' => $this->faker->randomElement(['Одличан', 'Врлодобар', 'Добар', 'Довољан']),
            'poeniMin' => $this->faker->numberBetween(0, 50),
            'poeniMax' => $this->faker->numberBetween(51, 100),
            'ocena' => $this->faker->numberBetween(6, 10),
            'indikatorAktivan' => 1,
        ];
    }
}
