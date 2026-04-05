<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StatusIspita;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusIspitaFactory extends Factory
{
    protected $model = StatusIspita::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->word(),
            'indikatorAktivan' => 1,
        ];
    }
}
