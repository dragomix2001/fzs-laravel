<?php

namespace Database\Factories;

use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfesorFactory extends Factory
{
    protected $model = Profesor::class;

    public function definition(): array
    {
        $zvanja = ['Docent', 'Vanredni profesor', 'Redovni profesor', 'Asistent', 'Nastavnik'];

        return [
            'jmbg' => $this->faker->unique()->numerify('#############'),
            'ime' => $this->faker->firstName(),
            'prezime' => $this->faker->lastName(),
            'telefon' => $this->faker->phoneNumber(),
            'zvanje' => $this->faker->randomElement($zvanja),
            'mail' => $this->faker->unique()->safeEmail(),
            'indikatorAktivan' => 1,
            'status_id' => 1,
        ];
    }
}
