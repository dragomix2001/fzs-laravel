<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Jobs\MassEnrollmentJob;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\KandidatEnrollmentService;
use App\Services\UpisService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KandidatEnrollmentServiceTest extends TestCase
{
    use DatabaseTransactions;

    private KandidatEnrollmentService $service;

    /** @var MockInterface&UpisService */
    private MockInterface $upisService;

    protected function setUp(): void
    {
        parent::setUp();

        // Insert required FK rows via raw queries to bypass Eloquent guards
        DB::table('tip_studija')->insertOrIgnore([
            ['id' => 1, 'naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'naziv' => 'Master akademske studije', 'skrNaziv' => 'MAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'naziv' => 'Doktorske akademske studije', 'skrNaziv' => 'DAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('status_studiranja')->insertOrIgnore([
            ['id' => 1, 'naziv' => 'upis u toku', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'naziv' => 'upis završen', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->upisService = Mockery::mock(UpisService::class);
        $this->service = new KandidatEnrollmentService($this->upisService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeKandidat(int $tipStudijaId = 1): Kandidat
    {
        // tip_studija and status_studiranja rows are inserted in setUp()
        $tipStudija = TipStudija::find($tipStudijaId);

        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $status = StatusStudiranja::find(1);
        $skolskaGod = SkolskaGodUpisa::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        return Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'statusUpisa_id' => $status->id,
            'skolskaGodinaUpisa_id' => $skolskaGod->id,
            'godinaStudija_id' => $godinaStudija->id,
            'uplata' => 0,
        ]);
    }

    #[Test]
    public function masovna_uplata_sets_uplata_flag_on_each_kandidat(): void
    {
        $k1 = $this->makeKandidat();
        $k2 = $this->makeKandidat();

        $this->service->masovnaUplata([$k1->id, $k2->id]);

        $this->assertEquals(1, $k1->fresh()->uplata);
        $this->assertEquals(1, $k2->fresh()->uplata);
    }

    #[Test]
    public function masovna_uplata_master_sets_uplata_flag(): void
    {
        $k1 = $this->makeKandidat();
        $k2 = $this->makeKandidat();

        $this->service->masovnaUplataMaster([$k1->id, $k2->id]);

        $this->assertEquals(1, $k1->fresh()->uplata);
        $this->assertEquals(1, $k2->fresh()->uplata);
    }

    #[Test]
    public function masovni_upis_returns_true_and_sets_status_on_success(): void
    {
        $kandidat = $this->makeKandidat();

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(true);

        $result = $this->service->masovniUpis([$kandidat->id]);

        $this->assertTrue($result);
        $this->assertEquals(1, $kandidat->fresh()->statusUpisa_id);
    }

    #[Test]
    public function masovni_upis_returns_false_when_upis_fails(): void
    {
        $kandidat = $this->makeKandidat();

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(false);

        $result = $this->service->masovniUpis([$kandidat->id]);

        $this->assertFalse($result);
    }

    #[Test]
    public function masovni_upis_master_sets_status_and_calls_generisi_broj_indeksa(): void
    {
        $k1 = $this->makeKandidat();
        $k2 = $this->makeKandidat();

        $this->upisService->shouldReceive('generisiBrojIndeksa')->twice();

        $this->service->masovniUpisMaster([$k1->id, $k2->id]);

        $this->assertEquals(1, $k1->fresh()->statusUpisa_id);
        $this->assertEquals(1, $k2->fresh()->statusUpisa_id);
    }

    #[Test]
    public function masovni_upis_async_dispatches_job_and_returns_queued_status(): void
    {
        Bus::fake();

        $ids = [10, 20, 30];
        $result = $this->service->masovniUpisAsync($ids);

        Bus::assertDispatched(MassEnrollmentJob::class, static function (MassEnrollmentJob $job) use ($ids): bool {
            return $job->kandidatIds === $ids;
        });

        $this->assertSame('queued', $result['status']);
        $this->assertSame(3, $result['count']);
    }

    #[Test]
    public function upis_kandidata_tip_studija_1_sets_status_and_returns_success(): void
    {
        $kandidat = $this->makeKandidat(1);

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(true);

        $result = $this->service->upisKandidata($kandidat->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['tipStudija_id']);
        $this->assertEquals(1, $kandidat->fresh()->statusUpisa_id);
    }

    #[Test]
    public function upis_kandidata_tip_studija_1_returns_failure_when_upis_fails(): void
    {
        $kandidat = $this->makeKandidat(1);

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(false);

        $result = $this->service->upisKandidata($kandidat->id);

        $this->assertFalse($result['success']);
    }

    #[Test]
    public function upis_kandidata_tip_studija_2_calls_generisi_broj_indeksa(): void
    {
        $kandidat = $this->makeKandidat(2);

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(true);
        $this->upisService->shouldReceive('generisiBrojIndeksa')->once()->with($kandidat->id);

        $result = $this->service->upisKandidata($kandidat->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['tipStudija_id']);
    }

    #[Test]
    public function upis_kandidata_tip_studija_2_returns_failure_when_upis_fails(): void
    {
        $kandidat = $this->makeKandidat(2);

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(false);
        $this->upisService->shouldReceive('generisiBrojIndeksa')->never();

        $result = $this->service->upisKandidata($kandidat->id);

        $this->assertFalse($result['success']);
        $this->assertEquals(2, $result['tipStudija_id']);
    }

    #[Test]
    public function upis_kandidata_tip_studija_3_calls_generisi_broj_indeksa(): void
    {
        $kandidat = $this->makeKandidat(3);

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(true);
        $this->upisService->shouldReceive('generisiBrojIndeksa')->once()->with($kandidat->id);

        $result = $this->service->upisKandidata($kandidat->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['tipStudija_id']);
    }

    #[Test]
    public function upis_kandidata_tip_studija_3_returns_failure_when_upis_fails(): void
    {
        $kandidat = $this->makeKandidat(3);

        $this->upisService->shouldReceive('registrujKandidata')->once()->with($kandidat->id);
        $this->upisService->shouldReceive('upisiGodinu')->once()->andReturn(false);
        $this->upisService->shouldReceive('generisiBrojIndeksa')->never();

        $result = $this->service->upisKandidata($kandidat->id);

        $this->assertFalse($result['success']);
        $this->assertEquals(3, $result['tipStudija_id']);
    }

    #[Test]
    public function registracija_kandidata_delegates_to_upis_service(): void
    {
        $this->upisService->shouldReceive('registrujKandidata')->once()->with(42);

        $this->service->registracijaKandidata(42);

        $this->addToAssertionCount(1); // verified via Mockery expectation above
    }
}
