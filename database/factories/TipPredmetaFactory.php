<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TipPredmeta;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipPredmetaFactory extends Factory
{
    protected $model = TipPredmeta::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->unique()->words(3, true),
            'skrNaziv' => $this->faker->unique()->lexify('???'),
            'indikatorAktivan' => 1,
        ];
    }
}
