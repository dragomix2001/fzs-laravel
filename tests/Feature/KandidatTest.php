<?php

namespace Tests\Feature;

use Database\Seeders\TestHelperSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KandidatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestHelperSeeder::class);
    }

    public function test_kandidat_index_returns_view()
    {
        $response = $this->get(route('kandidat.index'));
        $response->assertStatus(302);
    }

    public function test_kandidat_exists_in_database()
    {
        $count = DB::table('kandidat')->count();
        $this->assertGreaterThan(0, $count);
    }
}
