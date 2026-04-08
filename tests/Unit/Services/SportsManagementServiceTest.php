<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\SportskoAngazovanje;
use App\Services\SportsManagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SportsManagementServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected SportsManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SportsManagementService::class);
    }

    public function test_create_sport_for_kandidat_creates_sports_record(): void
    {
        $kandidatId = 1;
        $data = [
            'sport' => 2,
            'klub' => 'FK Partizan',
            'uzrast' => '10-15',
            'godine' => 5,
        ];

        $sport = $this->service->createSportForKandidat($kandidatId, $data);

        $this->assertInstanceOf(SportskoAngazovanje::class, $sport);
        $this->assertEquals(2, $sport->sport_id);
        $this->assertEquals(1, $sport->kandidat_id);
        $this->assertEquals('FK Partizan', $sport->nazivKluba);
        $this->assertEquals('10-15', $sport->odDoGodina);
        $this->assertEquals(5, $sport->ukupnoGodina);
        $this->assertDatabaseHas('sportsko_angazovanje', [
            'kandidat_id' => 1,
            'sport_id' => 2,
            'nazivKluba' => 'FK Partizan',
        ]);
    }

    public function test_create_sport_for_kandidat_persists_to_database(): void
    {
        $kandidatId = 999;
        $data = [
            'sport' => 5,
            'klub' => 'Crvena Zvezda',
            'uzrast' => '12-18',
            'godine' => 6,
        ];

        $this->service->createSportForKandidat($kandidatId, $data);

        $this->assertDatabaseHas('sportsko_angazovanje', [
            'kandidat_id' => 999,
            'sport_id' => 5,
            'nazivKluba' => 'Crvena Zvezda',
            'odDoGodina' => '12-18',
            'ukupnoGodina' => 6,
        ]);
    }

    public function test_get_sports_for_kandidat_returns_empty_collection_when_no_sports(): void
    {
        $sports = $this->service->getSportsForKandidat(9999);

        $this->assertCount(0, $sports);
    }

    public function test_get_sports_for_kandidat_returns_all_sports_for_kandidat(): void
    {
        $kandidatId = 100;

        SportskoAngazovanje::create([
            'kandidat_id' => $kandidatId,
            'sport_id' => 1,
            'nazivKluba' => 'Klub A',
            'odDoGodina' => '10-12',
            'ukupnoGodina' => 2,
        ]);

        SportskoAngazovanje::create([
            'kandidat_id' => $kandidatId,
            'sport_id' => 2,
            'nazivKluba' => 'Klub B',
            'odDoGodina' => '12-15',
            'ukupnoGodina' => 3,
        ]);

        $sports = $this->service->getSportsForKandidat($kandidatId);

        $this->assertCount(2, $sports);
        $this->assertEquals('Klub A', $sports[0]->nazivKluba);
        $this->assertEquals('Klub B', $sports[1]->nazivKluba);
    }

    public function test_get_sports_for_kandidat_returns_only_for_specified_kandidat(): void
    {
        SportskoAngazovanje::create([
            'kandidat_id' => 1,
            'sport_id' => 1,
            'nazivKluba' => 'Kandidat 1 Klub',
            'odDoGodina' => '10-12',
            'ukupnoGodina' => 2,
        ]);

        SportskoAngazovanje::create([
            'kandidat_id' => 2,
            'sport_id' => 2,
            'nazivKluba' => 'Kandidat 2 Klub',
            'odDoGodina' => '12-15',
            'ukupnoGodina' => 3,
        ]);

        $sports = $this->service->getSportsForKandidat(1);

        $this->assertCount(1, $sports);
        $this->assertEquals('Kandidat 1 Klub', $sports[0]->nazivKluba);
    }

    public function test_delete_sports_for_kandidat_removes_all_sports(): void
    {
        $kandidatId = 200;

        SportskoAngazovanje::create([
            'kandidat_id' => $kandidatId,
            'sport_id' => 1,
            'nazivKluba' => 'Klub A',
            'odDoGodina' => '10-12',
            'ukupnoGodina' => 2,
        ]);

        SportskoAngazovanje::create([
            'kandidat_id' => $kandidatId,
            'sport_id' => 2,
            'nazivKluba' => 'Klub B',
            'odDoGodina' => '12-15',
            'ukupnoGodina' => 3,
        ]);

        $deletedCount = $this->service->deleteSportsForKandidat($kandidatId);

        $this->assertEquals(2, $deletedCount);
        $this->assertDatabaseMissing('sportsko_angazovanje', ['kandidat_id' => $kandidatId]);
    }

    public function test_delete_sports_for_kandidat_returns_zero_when_no_sports(): void
    {
        $deletedCount = $this->service->deleteSportsForKandidat(9999);

        $this->assertEquals(0, $deletedCount);
    }

    public function test_delete_sports_for_kandidat_does_not_affect_other_kandidats(): void
    {
        SportskoAngazovanje::create([
            'kandidat_id' => 1,
            'sport_id' => 1,
            'nazivKluba' => 'Kandidat 1 Klub',
            'odDoGodina' => '10-12',
            'ukupnoGodina' => 2,
        ]);

        SportskoAngazovanje::create([
            'kandidat_id' => 2,
            'sport_id' => 2,
            'nazivKluba' => 'Kandidat 2 Klub',
            'odDoGodina' => '12-15',
            'ukupnoGodina' => 3,
        ]);

        $this->service->deleteSportsForKandidat(1);

        $this->assertDatabaseMissing('sportsko_angazovanje', ['kandidat_id' => 1]);
        $this->assertDatabaseHas('sportsko_angazovanje', ['kandidat_id' => 2, 'nazivKluba' => 'Kandidat 2 Klub']);
    }
}
