<?php

namespace Database\Factories;

use App\Models\SkolskaGodUpisa;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkolskaGodUpisaFactory extends Factory
{
    protected $model = SkolskaGodUpisa::class;

    public function definition(): array
    {
        static $year = 2020;

        $start = $year;
        $end = $year + 1;
        $year++;

        return [
            'naziv' => "{$start}/{$end}",
        ];
    }
}
