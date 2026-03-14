<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\StudijskiProgram;

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
        $this->assertGreaterThan(0, $count);
    }
}
