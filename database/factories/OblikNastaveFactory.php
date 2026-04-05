<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OblikNastave;
use Illuminate\Database\Eloquent\Factories\Factory;

class OblikNastaveFactory extends Factory
{
    protected $model = OblikNastave::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->word(),
            'skrNaziv' => $this->faker->word(),
            'indikatorAktivan' => 1,
        ];
    }
}
