<?php

namespace Database\Factories;

use App\Models\TipStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TipStudija represents study type: Osnovne (1), Master (2), Doktorske (3).
 */
class TipStudijaFactory extends Factory
{
    protected $model = TipStudija::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        $tipovi = [
            ['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS'],
            ['naziv' => 'Master akademske studije', 'skrNaziv' => 'MAS'],
            ['naziv' => 'Doktorske akademske studije', 'skrNaziv' => 'DAS'],
        ];

        $tip = $tipovi[($counter - 1) % 3];

        return [
            'naziv' => $tip['naziv'],
            'skrNaziv' => $tip['skrNaziv'],
            'indikatorAktivan' => 1,
        ];
    }

    public function osnovne(): static
    {
        return $this->state(fn (array $attributes) => [
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
        ]);
    }

    public function master(): static
    {
        return $this->state(fn (array $attributes) => [
            'naziv' => 'Master akademske studije',
            'skrNaziv' => 'MAS',
        ]);
    }

    public function doktorske(): static
    {
        return $this->state(fn (array $attributes) => [
            'naziv' => 'Doktorske akademske studije',
            'skrNaziv' => 'DAS',
        ]);
    }
}
