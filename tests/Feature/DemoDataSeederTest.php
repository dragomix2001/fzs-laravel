<?php

namespace Tests\Feature;

use Database\Seeders\TestDataSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    public function test_database_seed_includes_realistic_demo_data(): void
    {
        $this->seed(TestDataSeeder::class);

        $this->assertGreaterThan(0, DB::table('tip_studija')->count());
        $this->assertGreaterThan(0, DB::table('studijski_program')->count());
        $this->assertGreaterThan(0, DB::table('godina_studija')->count());
        $this->assertGreaterThan(0, DB::table('profesor')->count());
        $this->assertGreaterThan(0, DB::table('kandidat')->count());
        $this->assertGreaterThan(0, DB::table('predmet')->count());
        $this->assertGreaterThan(0, DB::table('prijava_ispita')->count());
        $this->assertGreaterThan(0, DB::table('zapisnik_o_polaganju_ispita')->count());
    }
}
