<?php

namespace Database\Factories;

use App\Models\StatusGodine;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusGodineFactory extends Factory
{
    protected $model = StatusGodine::class;

    public function definition(): array
    {
        $statusi = ['уписан', 'одустао', 'није уписан', 'обновио', 'завршио'];

        return [
            'naziv' => $this->faker->randomElement($statusi),
            'datum' => null,
            'indikatorAktivan' => 1,
        ];
    }
}
