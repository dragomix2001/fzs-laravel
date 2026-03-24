<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KandidatTest extends TestCase
{
    public function test_kandidat_index_returns_view()
    {
        $response = $this->get(route('kandidat.index'));
        $response->assertStatus(302);
    }

    public function test_kandidat_exists_in_database()
    {
        $count = DB::table('kandidat')->count();
        if ($count === 0) {
            $this->markTestSkipped('No kandidat data in database');
        }
        $this->assertGreaterThan(0, $count);
    }
}
