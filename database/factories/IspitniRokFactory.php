<?php

namespace Database\Factories;

use App\Models\IspitniRok;
use Illuminate\Database\Eloquent\Factories\Factory;

class IspitniRokFactory extends Factory
{
    protected $model = IspitniRok::class;

    public function definition(): array
    {
        $tipovi = ['Redovni rok', 'Septembarski rok', 'Februarski rok', 'Junski rok'];

        return [
            'naziv' => $this->faker->randomElement($tipovi),
            'indikatorAktivan' => 1,
        ];
    }
}
