<?php

namespace Database\Factories;

use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

class KandidatFactory extends Factory
{
    protected $model = Kandidat::class;

    public function definition(): array
    {
        $godinaStudija = $this->faker->numberBetween(1, 4);

        return [
            'imeKandidata' => $this->faker->firstName(),
            'prezimeKandidata' => $this->faker->lastName(),
            'jmbg' => $this->faker->unique()->numerify('#############'),
            'studijskiProgram_id' => StudijskiProgram::factory(),
            'tipStudija_id' => TipStudija::factory(),
            'skolskaGodinaUpisa_id' => SkolskaGodUpisa::factory(),
            'godinaStudija_id' => $godinaStudija,
            'statusUpisa_id' => StatusStudiranja::factory(),
            'indikatorAktivan' => 1,
            'krsnaSlava_id' => 1,
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'mesto_id' => 1,
            'uplata' => 0,
            'upisan' => 0,
            'brojIndeksa' => null,
        ];
    }

    public function osnovneStudije(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipStudija_id' => TipStudija::factory()->osnovne(),
            'godinaStudija_id' => 1,
        ]);
    }

    public function masterStudije(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipStudija_id' => TipStudija::factory()->master(),
            'godinaStudija_id' => 1,
        ]);
    }

    public function upisan(): static
    {
        return $this->state(fn (array $attributes) => [
            'statusUpisa_id' => StatusStudiranja::factory(),
            'upisan' => 1,
        ]);
    }
}
