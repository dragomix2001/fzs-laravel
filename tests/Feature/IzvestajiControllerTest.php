<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\User;
use App\Services\DiplomaService;
use App\Services\DiplomskiRadService;
use App\Services\IspitPdfService;
use App\Services\StudentListService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class IzvestajiControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createKandidat(): Kandidat
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $status = StatusStudiranja::factory()->create();
        $skolskaGod = SkolskaGodUpisa::factory()->create();

        /** @var Kandidat $kandidat */
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'statusUpisa_id' => $status->id,
            'skolskaGodinaUpisa_id' => $skolskaGod->id,
        ]);

        return $kandidat;
    }

    public function test_spisak_po_smerovima_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoSmerovima')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->get('izvestaji/spisakPoSmerovima');
        $response->assertOk();
    }

    public function test_integralno_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('integralno')
                ->once()
                ->with(2024)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/integralno', ['godina' => 2024]);
        $response->assertOk();
    }

    public function test_spisak_po_smerovima_ostali_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoSmerovimaOstali')
                ->once()
                ->with(2024)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoSmerovimaOstali', ['godina' => 2024]);
        $response->assertOk();
    }

    public function test_spisak_po_smerovima_aktivni_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoSmerovimaAktivni')
                ->once()
                ->with(2024)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoSmerovimaAktivni', ['godina' => 2024]);
        $response->assertOk();
    }

    public function test_spisak_za_smer_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakZaSmer')
                ->once()
                ->with(1, 2024)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakZaSmer', [
            'program' => 1,
            'godina' => 2024,
        ]);
        $response->assertOk();
    }

    public function test_spisak_po_programu_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoProgramu')
                ->once()
                ->with(1)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoProgramu', ['program' => 1]);
        $response->assertOk();
    }

    public function test_spisak_po_godini_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoGodini')
                ->once()
                ->with(2024)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoGodini', ['godina' => 2024]);
        $response->assertOk();
    }

    public function test_spisak_po_slavama_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoSlavama')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoSlavama');
        $response->assertOk();
    }

    public function test_spisak_po_profesorima_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoProfesorima')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoProfesorima');
        $response->assertOk();
    }

    public function test_spiskovi_studenti_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spiskoviStudenti')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->get('/izvestaji/spiskoviStudenti');
        $response->assertOk();
    }

    public function test_potvrde_student_delegates_to_diploma_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(DiplomaService::class, function ($mock) {
            $mock->shouldReceive('potvrdeStudent')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/potvrdeStudent/{$student->id}");
        $response->assertOk();
    }

    public function test_diploma_unos_delegates_to_diploma_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(DiplomaService::class, function ($mock) {
            $mock->shouldReceive('diplomaUnos')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/{$student->id}/diplomaUnos");
        $response->assertOk();
    }

    public function test_diploma_stampa_delegates_to_diploma_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(DiplomaService::class, function ($mock) {
            $mock->shouldReceive('diplomaStampa')
                ->once()
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/diplomaStampa/{$student->id}");
        $response->assertOk();
    }

    public function test_diplomski_unos_delegates_to_diplomski_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(DiplomskiRadService::class, function ($mock) {
            $mock->shouldReceive('diplomskiUnos')
                ->once()
                ->andReturn(view('welcome'));
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/diplomskiUnos/{$student->id}");
        $response->assertOk();
    }

    public function test_komisija_stampa_delegates_to_diplomski_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(DiplomskiRadService::class, function ($mock) {
            $mock->shouldReceive('komisijaStampa')
                ->once()
                ->andReturn(null);
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/komisijaStampa/{$student->id}");
        $response->assertOk();
    }

    public function test_polozeni_stampa_delegates_to_ispit_pdf_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(IspitPdfService::class, function ($mock) use ($student) {
            $mock->shouldReceive('polozeniStampa')
                ->once()
                ->with((string) $student->id)
                ->andReturn(null);
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/polozeniStampa/{$student->id}");
        $response->assertOk();
    }

    public function test_spisak_diplomiranih_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakDiplomiranih')
                ->once()
                ->with(2024)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakDiplomiranih', ['godina' => 2024]);
        $response->assertOk();
    }

    public function test_zapisnik_diplomski_delegates_to_diplomski_service(): void
    {
        $student = $this->createKandidat();

        $this->mock(DiplomskiRadService::class, function ($mock) {
            $mock->shouldReceive('zapisnikDiplomski')
                ->once()
                ->andReturn(null);
        });

        $response = $this->actingAs($this->adminUser())->get("izvestaji/zapisnikDiplomski/{$student->id}");
        $response->assertOk();
    }

    public function test_spisak_po_predmetima_delegates_to_service(): void
    {
        $this->mock(StudentListService::class, function ($mock) {
            $mock->shouldReceive('spisakPoPredmetima')
                ->once()
                ->with(1)
                ->andReturn(new Response('OK'));
        });

        $response = $this->actingAs($this->adminUser())->post('izvestaji/spisakPoPredmetima', ['predmet' => 1]);
        $response->assertOk();
    }
}
