<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GodinaStudija;
use App\Models\PrilozenaDokumenta;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrilozenaDokumentaFactory extends Factory
{
    protected $model = PrilozenaDokumenta::class;

    public function definition(): array
    {
        return [
            'redniBrojDokumenta' => $this->faker->unique()->numberBetween(1, 100),
            'naziv' => $this->faker->words(3, true),
            'skolskaGodina_id' => GodinaStudija::factory(),
        ];
    }
}
