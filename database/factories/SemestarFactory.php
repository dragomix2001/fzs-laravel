<?php

namespace Database\Factories;

use App\Models\Semestar;
use Illuminate\Database\Eloquent\Factories\Factory;

class SemestarFactory extends Factory
{
    protected $model = Semestar::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->word(),
            'nazivRimski' => $this->faker->word(),
            'nazivBrojcano' => $this->faker->numerify('##'),
            'indikatorAktivan' => 1,
        ];
    }
}
