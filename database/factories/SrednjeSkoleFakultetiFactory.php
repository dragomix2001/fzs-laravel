<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SrednjeSkoleFakulteti;
use Illuminate\Database\Eloquent\Factories\Factory;

class SrednjeSkoleFakultetiFactory extends Factory
{
    protected $model = SrednjeSkoleFakulteti::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->words(3, true),
            'indSkoleFakulteta' => $this->faker->randomElement([0, 1]),
        ];
    }
}
