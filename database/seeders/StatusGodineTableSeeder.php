<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusGodineTableSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('status_godine')->count() > 0) {
            return;
        }

        DB::table('status_godine')->insert([
            ['id' => 1, 'naziv' => 'уписан', 'datum' => null, 'indikatorAktivan' => 1, 'created_at' => '2016-08-29 20:03:53', 'updated_at' => '2016-08-29 20:03:53'],
            ['id' => 2, 'naziv' => 'одустао', 'datum' => null, 'indikatorAktivan' => 1, 'created_at' => '2016-08-29 20:03:53', 'updated_at' => '2016-08-29 20:03:53'],
            ['id' => 3, 'naziv' => 'није уписан', 'datum' => null, 'indikatorAktivan' => 1, 'created_at' => '2016-08-29 20:03:53', 'updated_at' => '2016-08-29 20:03:53'],
            ['id' => 4, 'naziv' => 'обновио', 'datum' => null, 'indikatorAktivan' => 1, 'created_at' => '2016-10-04 05:16:03', 'updated_at' => '2016-10-04 05:16:08'],
            ['id' => 5, 'naziv' => 'завршио', 'datum' => null, 'indikatorAktivan' => 1, 'created_at' => '2016-10-04 05:16:19', 'updated_at' => '2016-10-04 05:16:19'],
        ]);
    }
}
