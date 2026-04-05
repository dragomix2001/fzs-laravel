<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GodinaStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

class GodinaStudijaFactory extends Factory
{
    protected $model = GodinaStudija::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->randomElement(['Прва година', 'Друга година', 'Трећа година', 'Четврта година']),
            'nazivRimski' => $this->faker->randomElement(['I', 'II', 'III', 'IV']),
            'nazivSlovimaUPadezu' => $this->faker->randomElement(['Prve', 'Druge', 'Treće', 'Četvrte']),
            'redosledPrikazivanja' => $this->faker->numberBetween(1, 4),
            'indikatorAktivan' => 1,
        ];
    }
}
