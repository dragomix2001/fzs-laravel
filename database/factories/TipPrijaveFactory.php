<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TipPrijave;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipPrijaveFactory extends Factory
{
    protected $model = TipPrijave::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->unique()->words(3, true),
            'indikatorAktivan' => 1,
        ];
    }
}
