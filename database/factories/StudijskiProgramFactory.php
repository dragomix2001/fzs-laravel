<?php

namespace Database\Factories;

use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudijskiProgramFactory extends Factory
{
    protected $model = StudijskiProgram::class;

    public function definition(): array
    {
        return [
            'naziv' => $this->faker->words(3, true),
            'skrNazivStudijskogPrograma' => strtoupper($this->faker->lexify('???')),
            'zvanje' => $this->faker->words(2, true),
            'tipStudija_id' => TipStudija::factory(),
            'indikatorAktivan' => 1,
        ];
    }
}
