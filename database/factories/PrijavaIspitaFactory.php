<?php

namespace Database\Factories;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrijavaIspitaFactory extends Factory
{
    protected $model = PrijavaIspita::class;

    public function definition(): array
    {
        return [
            'kandidat_id' => Kandidat::factory(),
            'predmet_id' => Predmet::factory(),
            'profesor_id' => Profesor::factory(),
            'rok_id' => AktivniIspitniRokovi::factory(),
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => 1,
        ];
    }
}
