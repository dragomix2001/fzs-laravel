<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

class SportFactory extends Factory
{
    protected $model = Sport::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->word(),
            'indikatorAktivan' => $this->faker->randomElement([0, 1]),
        ];
    }
}
