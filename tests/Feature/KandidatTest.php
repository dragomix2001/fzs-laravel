<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\StudijskiProgram;

class KandidatTest extends TestCase
{
    use RefreshDatabase;

    public function test_kandidat_index_returns_view()
    {
        $response = $this->get(route('kandidat.index'));
        $response->assertStatus(302);
    }

    public function test_kandidat_can_be_created()
    {
        $program = StudijskiProgram::create([
            'naziv' => 'Test Program',
            'skrNazivStudijskogPrograma' => 'TP'
        ]);

        $kandidat = Kandidat::create([
            'ime' => 'Test',
            'prezimeKandidata' => 'User',
            'brojIndeksa' => '0001/2024',
            'studijskiProgram_id' => $program->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => 1,
            'skolskaGodinaUpisa_id' => 1
        ]);

        $this->assertDatabaseHas('kandidat', [
            'ime' => 'Test',
            'prezimeKandidata' => 'User'
        ]);
    }

    public function test_kandidat_requires_ime()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Kandidat::create([
            'prezimeKandidata' => 'User',
            'brojIndeksa' => '0001/2024'
        ]);
    }
}
