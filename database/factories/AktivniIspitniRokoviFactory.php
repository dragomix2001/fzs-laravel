<?php

namespace Database\Factories;

use App\Models\AktivniIspitniRokovi;
use Illuminate\Database\Eloquent\Factories\Factory;

class AktivniIspitniRokoviFactory extends Factory
{
    protected $model = AktivniIspitniRokovi::class;

    public function definition(): array
    {
        $rokovi = ['Januarski', 'Februarski', 'Junski', 'Julski', 'Septembarski', 'Oktobar'];
        $naziv = $this->faker->randomElement($rokovi).' ispitni rok';
        $pocetak = $this->faker->dateTimeBetween('now', '+30 days');
        $kraj = clone $pocetak;
        $kraj->modify('+14 days');

        return [
            'rok_id' => $this->faker->numberBetween(1, 10),
            'naziv' => $naziv,
            'pocetak' => $pocetak->format('Y-m-d'),
            'kraj' => $kraj->format('Y-m-d'),
            'tipRoka_id' => 1,
            'komentar' => '',
            'indikatorAktivan' => 1,
        ];
    }

    public function aktivan(): static
    {
        return $this->state(fn (array $attributes) => [
            'indikatorAktivan' => 1,
        ]);
    }

    public function neaktivan(): static
    {
        return $this->state(fn (array $attributes) => [
            'indikatorAktivan' => 0,
        ]);
    }
}
