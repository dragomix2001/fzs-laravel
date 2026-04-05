<?php

namespace Database\Factories;

use App\Models\Opstina;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpstinaFactory extends Factory
{
    protected $model = Opstina::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->word(),
            'region_id' => 1,
        ];
    }
}
