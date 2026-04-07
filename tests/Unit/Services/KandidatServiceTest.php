<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Kandidat;
use App\Services\KandidatService;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KandidatServiceTest extends TestCase
{
    use DatabaseTransactions;

    private KandidatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        $this->service = app(KandidatService::class);
    }

    #[Test]
    public function test_get_all_returns_all_kandidati(): void
    {
        // Create test data
        TipStudija::factory()->create();
        StatusStudiranja::factory()->create();
        StudijskiProgram::factory()->create();

        Kandidat::factory()->count(3)->create();

        $result = $this->service->getAll([]);

        $this->assertCount(3, $result);
        $this->assertInstanceOf(Kandidat::class, $result->first());
    }

    #[Test]
    public function test_get_all_filters_by_tip_studija(): void
    {
        TipStudija::factory()->create(['id' => 1]);
        TipStudija::factory()->create(['id' => 2]);
        StatusStudiranja::factory()->create();
        StudijskiProgram::factory()->create();

        Kandidat::factory()->create(['tipStudija_id' => 1]);
        Kandidat::factory()->create(['tipStudija_id' => 2]);

        $result = $this->service->getAll(['tipStudija_id' => 1]);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->tipStudija_id);
    }

    #[Test]
    public function test_get_all_filters_by_status_upisa(): void
    {
        TipStudija::factory()->create();
        StatusStudiranja::factory()->create(['id' => 1]);
        StatusStudiranja::factory()->create(['id' => 2]);
        StudijskiProgram::factory()->create();

        Kandidat::factory()->create(['statusUpisa_id' => 1]);
        Kandidat::factory()->create(['statusUpisa_id' => 2]);

        $result = $this->service->getAll(['statusUpisa_id' => 1]);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->statusUpisa_id);
    }

    #[Test]
    public function test_get_all_filters_by_studijski_program(): void
    {
        TipStudija::factory()->create();
        StatusStudiranja::factory()->create();
        StudijskiProgram::factory()->create(['id' => 1]);
        StudijskiProgram::factory()->create(['id' => 2]);

        Kandidat::factory()->create(['studijskiProgram_id' => 1]);
        Kandidat::factory()->create(['studijskiProgram_id' => 2]);

        $result = $this->service->getAll(['studijskiProgram_id' => 1]);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->studijskiProgram_id);
    }

    #[Test]
    public function test_find_by_id_returns_kandidat(): void
    {
        TipStudija::factory()->create();
        StatusStudiranja::factory()->create();
        StudijskiProgram::factory()->create();

        $kandidat = Kandidat::factory()->create();

        $result = $this->service->findById($kandidat->id);

        $this->assertNotNull($result);
        $this->assertEquals($kandidat->id, $result->id);
    }

    #[Test]
    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $result = $this->service->findById(99999);

        $this->assertNull($result);
    }

    #[Test]
    public function test_get_active_studijski_program_osnovne(): void
    {
        TipStudija::factory()->create(['id' => 1]);
        StudijskiProgram::factory()->create(['tipStudija_id' => 1, 'indikatorAktivan' => 1]);

        $result = $this->service->getActiveStudijskiProgramOsnovne();

        $this->assertNotNull($result);
        $this->assertIsInt($result);
    }

    #[Test]
    public function test_get_studijski_programi_by_tip_studija(): void
    {
        TipStudija::factory()->create(['id' => 1]);
        TipStudija::factory()->create(['id' => 2]);

        StudijskiProgram::factory()->count(2)->create(['tipStudija_id' => 1]);
        StudijskiProgram::factory()->count(3)->create(['tipStudija_id' => 2]);

        $result = $this->service->getStudijskiProgrami(1);

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn ($p) => $p->tipStudija_id === 1));
    }

    #[Test]
    public function test_get_dropdown_data_returns_all_required_keys(): void
    {
        $result = $this->service->getDropdownData();

        $this->assertArrayHasKey('mestoRodjenja', $result);
        $this->assertArrayHasKey('krsnaSlava', $result);
        $this->assertArrayHasKey('mestoZavrseneSkoleFakulteta', $result);
        $this->assertArrayHasKey('opstiUspehSrednjaSkola', $result);
        $this->assertArrayHasKey('uspehSrednjaSkola', $result);
        $this->assertArrayHasKey('sportskoAngazovanje', $result);
        $this->assertArrayHasKey('prilozeniDokumentPrvaGodina', $result);
        $this->assertArrayHasKey('statusaUpisaKandidata', $result);
        $this->assertArrayHasKey('studijskiProgram', $result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('godinaStudija', $result);
        $this->assertArrayHasKey('skolskeGodineUpisa', $result);
    }

    #[Test]
    public function test_get_dropdown_data_master_returns_all_required_keys(): void
    {
        $result = $this->service->getDropdownDataMaster();

        $this->assertArrayHasKey('mestoRodjenja', $result);
        $this->assertArrayHasKey('krsnaSlava', $result);
        $this->assertArrayHasKey('dokumentaMaster', $result);
        $this->assertArrayHasKey('studijskiProgram', $result);
        $this->assertArrayHasKey('tipStudija', $result);
    }
}
