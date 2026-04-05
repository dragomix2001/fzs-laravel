<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StatusProfesora;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusProfesoraFactory extends Factory
{
    protected $model = StatusProfesora::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->unique()->words(3, true),
            'indikatorAktivan' => 1,
        ];
    }
}
