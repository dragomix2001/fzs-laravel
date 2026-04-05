<?php

namespace Database\Factories;

use App\Models\Mesto;
use Illuminate\Database\Eloquent\Factories\Factory;

class MestoFactory extends Factory
{
    protected $model = Mesto::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->city(),
            'opstina_id' => 1,
        ];
    }
}
