<?php

namespace Database\Factories;

use App\Models\AktivniIspitniRokovi;
use App\Models\Predmet;
use App\Models\Profesor;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZapisnikOPolaganjuIspitaFactory extends Factory
{
    protected $model = ZapisnikOPolaganjuIspita::class;

    public function definition(): array
    {
        return [
            'predmet_id' => Predmet::factory(),
            'profesor_id' => Profesor::factory(),
            'rok_id' => AktivniIspitniRokovi::factory(),
            'datum' => now()->toDateString(),
            'datum2' => now()->addDays(7)->toDateString(),
            'vreme' => '10:00:00',
            'ucionica' => $this->faker->numerify('##'),
            'prijavaIspita_id' => null,
            'kandidat_id' => null,
        ];
    }
}
