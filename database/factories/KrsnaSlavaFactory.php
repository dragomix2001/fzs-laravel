<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KrsnaSlava;
use Illuminate\Database\Eloquent\Factories\Factory;

class KrsnaSlavaFactory extends Factory
{
    protected $model = KrsnaSlava::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->word(),
            'datumSlave' => $this->faker->date(),
            'indikatorAktivan' => 1,
        ];
    }
}
