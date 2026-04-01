<?php

namespace Database\Factories;

use App\Models\StatusStudiranja;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusStudiranjaFactory extends Factory
{
    protected $model = StatusStudiranja::class;

    public function definition(): array
    {
        $statusi = ['упис у току', 'упис завршен', 'одустао', 'дипломирао'];

        return [
            'naziv' => $this->faker->randomElement($statusi),
            'indikatorAktivan' => 1,
        ];
    }
}
