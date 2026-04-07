<?php

namespace Database\Factories;

use App\Models\GodinaStudija;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

class PredmetProgramFactory extends Factory
{
    protected $model = PredmetProgram::class;

    public function definition(): array
    {
        return [
            'studijskiProgram_id' => StudijskiProgram::factory(),
            'godinaStudija_id' => GodinaStudija::factory(),
            'semestar' => 1,
            'predmet_id' => Predmet::factory(),
            'tipPredmeta_id' => TipPredmeta::factory(),
            'tipStudija_id' => TipStudija::factory(),
            'espb' => 6,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => SkolskaGodUpisa::factory(),
            'indikatorAktivan' => 1,
        ];
    }
}
