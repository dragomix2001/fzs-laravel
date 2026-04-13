<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\KandidatPrilozenaDokumenta;
use App\Models\SportskoAngazovanje;
use App\Models\StudijskiProgram;
use App\Models\UspehSrednjaSkola;
use App\Services\DropdownDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DropdownDataServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected DropdownDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
        $this->service = app(DropdownDataService::class);
    }

    public function test_get_dropdown_data_returns_all_expected_keys(): void
    {
        $data = $this->service->getDropdownData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('mestoRodjenja', $data);
        $this->assertArrayHasKey('krsnaSlava', $data);
        $this->assertArrayHasKey('mestoZavrseneSkoleFakulteta', $data);
        $this->assertArrayHasKey('opstiUspehSrednjaSkola', $data);
        $this->assertArrayHasKey('uspehSrednjaSkola', $data);
        $this->assertArrayHasKey('sportskoAngazovanje', $data);
        $this->assertArrayHasKey('prilozeniDokumentPrvaGodina', $data);
        $this->assertArrayHasKey('statusaUpisaKandidata', $data);
        $this->assertArrayHasKey('studijskiProgram', $data);
        $this->assertArrayHasKey('tipStudija', $data);
        $this->assertArrayHasKey('godinaStudija', $data);
        $this->assertArrayHasKey('skolskeGodineUpisa', $data);
    }

    public function test_get_dropdown_data_returns_collections_for_all_keys(): void
    {
        $data = $this->service->getDropdownData();

        $this->assertInstanceOf(Collection::class, $data['mestoRodjenja']);
        $this->assertInstanceOf(Collection::class, $data['krsnaSlava']);
        $this->assertInstanceOf(Collection::class, $data['studijskiProgram']);
    }

    public function test_get_dropdown_data_master_returns_all_expected_keys(): void
    {
        $data = $this->service->getDropdownDataMaster();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('mestoRodjenja', $data);
        $this->assertArrayHasKey('krsnaSlava', $data);
        $this->assertArrayHasKey('opstiUspehSrednjaSkola', $data);
        $this->assertArrayHasKey('uspehSrednjaSkola', $data);
        $this->assertArrayHasKey('sportskoAngazovanje', $data);
        $this->assertArrayHasKey('prilozeniDokumentPrvaGodina', $data);
        $this->assertArrayHasKey('statusaUpisaKandidata', $data);
        $this->assertArrayHasKey('studijskiProgram', $data);
        $this->assertArrayHasKey('tipStudija', $data);
        $this->assertArrayHasKey('godinaStudija', $data);
        $this->assertArrayHasKey('skolskeGodineUpisa', $data);
        $this->assertArrayHasKey('dokumentaMaster', $data);
    }

    public function test_get_dropdown_data_master_includes_dokumenta_master(): void
    {
        $data = $this->service->getDropdownDataMaster();

        $this->assertArrayHasKey('dokumentaMaster', $data);
        $this->assertInstanceOf(Collection::class, $data['dokumentaMaster']);
    }

    public function test_get_studijski_programi_filters_by_tip_studija_id(): void
    {
        // Test for osnovne studije (tipStudija_id = 1)
        $osnovne = $this->service->getStudijskiProgrami(1);

        $this->assertInstanceOf(Collection::class, $osnovne);

        foreach ($osnovne as $program) {
            $this->assertEquals(1, $program->tipStudija_id);
        }
    }

    public function test_get_studijski_programi_filters_master_programs(): void
    {
        // Test for master studije (tipStudija_id = 2)
        $master = $this->service->getStudijskiProgrami(2);

        $this->assertInstanceOf(Collection::class, $master);

        foreach ($master as $program) {
            $this->assertEquals(2, $program->tipStudija_id);
        }
    }

    public function test_get_edit_dropdown_data_merges_base_data_with_kandidat_specific(): void
    {
        // Create test kandidat data
        $kandidatId = 1;

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 101,
            'indikatorAktivan' => 1,
        ]);

        SportskoAngazovanje::create([
            'kandidat_id' => $kandidatId,
            'sport_id' => 1,
            'nazivKluba' => 'Test Klub',
            'odDoGodina' => '10-15',
            'ukupnoGodina' => 5,
        ]);

        $data = $this->service->getEditDropdownData($kandidatId);

        // Check base dropdown data keys
        $this->assertArrayHasKey('mestoRodjenja', $data);
        $this->assertArrayHasKey('krsnaSlava', $data);
        $this->assertArrayHasKey('studijskiProgram', $data);

        // Check kandidat-specific keys
        $this->assertArrayHasKey('sport', $data);
        $this->assertArrayHasKey('dokumentiPrvaGodina', $data);
        $this->assertArrayHasKey('dokumentiOstaleGodine', $data);
        $this->assertArrayHasKey('statusKandidata', $data);
        $this->assertArrayHasKey('prilozenaDokumenta', $data);
        $this->assertArrayHasKey('sportskoAngazovanjeKandidata', $data);

        // Check grades keys (from GradeManagementService)
        $this->assertArrayHasKey('prviRazred', $data);
        $this->assertArrayHasKey('drugiRazred', $data);
        $this->assertArrayHasKey('treciRazred', $data);
        $this->assertArrayHasKey('cetvrtiRazred', $data);
    }

    public function test_get_edit_dropdown_data_includes_grades_from_grade_service(): void
    {
        $kandidatId = 1;

        $data = $this->service->getEditDropdownData($kandidatId);

        // Verify grades exist (GradeManagementService integration returns Model objects)
        $this->assertInstanceOf(UspehSrednjaSkola::class, $data['prviRazred']);
        $this->assertInstanceOf(UspehSrednjaSkola::class, $data['drugiRazred']);
        $this->assertInstanceOf(UspehSrednjaSkola::class, $data['treciRazred']);
        $this->assertInstanceOf(UspehSrednjaSkola::class, $data['cetvrtiRazred']);
    }

    public function test_get_edit_dropdown_data_includes_attached_documents(): void
    {
        $kandidatId = 2;

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 201,
            'indikatorAktivan' => 1,
        ]);

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 202,
            'indikatorAktivan' => 1,
        ]);

        $data = $this->service->getEditDropdownData($kandidatId);

        $this->assertIsArray($data['prilozenaDokumenta']);
        $this->assertCount(2, $data['prilozenaDokumenta']);
        $this->assertContains(201, $data['prilozenaDokumenta']);
        $this->assertContains(202, $data['prilozenaDokumenta']);
    }

    public function test_get_edit_dropdown_data_includes_sports_engagements(): void
    {
        $kandidatId = 3;

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

        $data = $this->service->getEditDropdownData($kandidatId);

        $this->assertInstanceOf(Collection::class, $data['sportskoAngazovanjeKandidata']);
        $this->assertCount(2, $data['sportskoAngazovanjeKandidata']);
    }

    public function test_get_edit_dropdown_data_master_merges_correctly(): void
    {
        $kandidatId = 4;

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 401,
            'indikatorAktivan' => 1,
        ]);

        $data = $this->service->getEditDropdownDataMaster($kandidatId);

        // Check base master data
        $this->assertArrayHasKey('mestoRodjenja', $data);
        $this->assertArrayHasKey('dokumentaMaster', $data);

        // Check kandidat-specific master data
        $this->assertArrayHasKey('statusKandidata', $data);
        $this->assertArrayHasKey('prilozenaDokumenta', $data);

        $this->assertIsArray($data['prilozenaDokumenta']);
        $this->assertContains(401, $data['prilozenaDokumenta']);
    }

    public function test_get_edit_dropdown_data_master_excludes_inactive_statuses(): void
    {
        $kandidatId = 5;

        $data = $this->service->getEditDropdownDataMaster($kandidatId);

        $this->assertArrayHasKey('statusKandidata', $data);
        $statusIds = $data['statusKandidata']->pluck('id')->toArray();

        // Verify statuses 4 and 5 are excluded (per whereNotIn([4, 5]))
        $this->assertNotContains(4, $statusIds);
        $this->assertNotContains(5, $statusIds);
    }

    public function test_get_dropdown_data_filters_studijski_program_for_osnovne(): void
    {
        $data = $this->service->getDropdownData();

        $this->assertInstanceOf(Collection::class, $data['studijskiProgram']);

        // Verify studijskiProgram filtered to tipStudija_id = 1
        foreach ($data['studijskiProgram'] as $program) {
            $this->assertEquals(1, $program->tipStudija_id);
        }
    }

    public function test_get_dropdown_data_master_filters_studijski_program_for_master(): void
    {
        $data = $this->service->getDropdownDataMaster();

        $this->assertInstanceOf(Collection::class, $data['studijskiProgram']);

        // Verify studijskiProgram filtered to tipStudija_id = 2 and indikatorAktivan = 1
        foreach ($data['studijskiProgram'] as $program) {
            $this->assertEquals(2, $program->tipStudija_id);
            $this->assertEquals(1, $program->indikatorAktivan);
        }
    }
}
