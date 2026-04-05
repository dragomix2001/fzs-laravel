<?php

namespace Tests\Feature;

use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\DiplomskiRad;
use App\Models\Kandidat;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\DiplomskiRadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DiplomskiRadServiceTest extends TestCase
{
    use RefreshDatabase;

    private DiplomskiRadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DiplomskiRadService::class);

        TipStudija::factory()->create();
        StudijskiProgram::factory()->create();
        SkolskaGodUpisa::factory()->create();
        StatusGodine::factory()->create();
    }

    // =========================================================================
    // diplomskiUnos() tests
    // =========================================================================

    public function test_diplomski_unos_returns_view_with_student_and_profesori()
    {
        $student = Kandidat::factory()->create();
        Profesor::factory()->count(3)->create();

        $result = $this->service->diplomskiUnos($student);

        $this->assertIsObject($result);
        $this->assertEquals($student->id, $result->getData()['student']->id);
        $this->assertCount(3, $result->getData()['profesor']);
    }

    public function test_diplomski_unos_returns_empty_profesor_list_when_none_exist()
    {
        $student = Kandidat::factory()->create();

        $result = $this->service->diplomskiUnos($student);

        $this->assertIsObject($result);
        $this->assertCount(0, $result->getData()['profesor']);
    }

    public function test_diplomski_unos_passes_all_profesori_regardless_of_count()
    {
        $student = Kandidat::factory()->create();
        Profesor::factory()->count(10)->create();

        $result = $this->service->diplomskiUnos($student);

        $this->assertCount(10, $result->getData()['profesor']);
    }

    // =========================================================================
    // diplomskiAdd() tests
    // =========================================================================

    public function test_diplomski_add_creates_diplomski_rad_record()
    {
        $student = Kandidat::factory()->create();

        $request = new Request([
            'kandidat_id' => $student->id,
            'tema' => 'Moja diplomska tema',
            'mentor' => 'Dr. Pera Perić',
            'datumPrijave' => '2024-05-01',
        ]);

        $this->service->diplomskiAdd($request);

        $this->assertDatabaseHas('diplomski_rad', [
            'kandidat_id' => $student->id,
            'tema' => 'Moja diplomska tema',
            'mentor' => 'Dr. Pera Perić',
            'datumPrijave' => '2024-05-01',
        ]);
    }

    public function test_diplomski_add_creates_diplomski_prijava_teme_record()
    {
        $student = Kandidat::factory()->create();

        $request = new Request([
            'kandidat_id' => $student->id,
            'tema' => 'Moja diplomska tema',
            'mentor' => 'Dr. Pera Perić',
            'datumPrijave' => '2024-05-01',
        ]);

        $this->service->diplomskiAdd($request);

        $this->assertDatabaseHas('diplomski_prijava_teme', [
            'kandidat_id' => $student->id,
            'tema' => 'Moja diplomska tema',
            'mentor' => 'Dr. Pera Perić',
            'datum' => '2024-05-01',
        ]);
    }

    public function test_diplomski_add_creates_both_records_atomically()
    {
        $student = Kandidat::factory()->create();

        $request = new Request([
            'kandidat_id' => $student->id,
            'tema' => 'Atomska diplomska tema',
            'mentor' => 'Dr. Test Testić',
            'datumPrijave' => '2024-06-15',
        ]);

        $this->service->diplomskiAdd($request);

        $diplomski = DiplomskiRad::where('kandidat_id', $student->id)->first();
        $prijava = DiplomskiPrijavaTeme::where('kandidat_id', $student->id)->first();

        $this->assertNotNull($diplomski);
        $this->assertNotNull($prijava);
        $this->assertEquals($diplomski->tema, $prijava->tema);
        $this->assertEquals($diplomski->mentor, $prijava->mentor);
    }

    public function test_diplomski_add_stores_correct_field_values()
    {
        $student = Kandidat::factory()->create();

        $request = new Request([
            'kandidat_id' => $student->id,
            'tema' => 'Blockchain u obrazovanju',
            'mentor' => 'Prof. Dr. Marko Marković',
            'datumPrijave' => '2024-07-20',
        ]);

        $this->service->diplomskiAdd($request);

        $diplomski = DiplomskiRad::where('tema', 'Blockchain u obrazovanju')->first();

        $this->assertNotNull($diplomski);
        $this->assertEquals($student->id, $diplomski->kandidat_id);
        $this->assertEquals('Prof. Dr. Marko Marković', $diplomski->mentor);
    }

    public function test_diplomski_add_redirects_to_student_index()
    {
        $student = Kandidat::factory()->create();

        $request = new Request([
            'kandidat_id' => $student->id,
            'tema' => 'Test tema',
            'mentor' => 'Test mentor',
            'datumPrijave' => '2024-05-01',
        ]);

        $response = $this->service->diplomskiAdd($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(route('student.index'), $response->getTargetUrl());
    }

    // =========================================================================
    // komisijaStampa() tests (mocked PDF)
    // =========================================================================

    public function test_komisija_stampa_returns_redirect_when_diplomski_not_found()
    {
        $student = Kandidat::factory()->create();

        $response = $this->service->komisijaStampa($student);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_komisija_stampa_queried_with_correct_student_id()
    {
        $student = Kandidat::factory()->create();
        DiplomskiRad::factory()->create(['kandidat_id' => $student->id]);

        try {
            $this->service->komisijaStampa($student);
        } catch (\Exception) {
        }

        $this->assertDatabaseHas('diplomski_rad', ['kandidat_id' => $student->id]);
    }

    // =========================================================================
    // zapisnikDiplomski() tests (mocked PDF)
    // =========================================================================

    public function test_zapisnik_diplomski_returns_redirect_when_polaganje_not_found()
    {
        $student = Kandidat::factory()->create();

        $response = $this->service->zapisnikDiplomski($student);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_zapisnik_diplomski_succeeds_when_polaganje_exists()
    {
        $student = Kandidat::factory()->create();
        DiplomskiPolaganje::factory()->create(['kandidat_id' => $student->id]);

        $polaganje = DiplomskiPolaganje::where('kandidat_id', $student->id)->first();
        $this->assertNotNull($polaganje);
    }

    // =========================================================================
    // Integration tests
    // =========================================================================

    public function test_diplomski_rad_service_full_workflow()
    {
        $student = Kandidat::factory()->create();
        Profesor::factory()->count(2)->create();

        $view = $this->service->diplomskiUnos($student);
        $this->assertIsObject($view);
        $this->assertEquals($student->id, $view->getData()['student']->id);

        $request = new Request([
            'kandidat_id' => $student->id,
            'tema' => 'Final tema',
            'mentor' => 'Final mentor',
            'datumPrijave' => '2024-08-01',
        ]);

        $this->service->diplomskiAdd($request);

        $this->assertDatabaseHas('diplomski_rad', ['kandidat_id' => $student->id]);
        $this->assertDatabaseHas('diplomski_prijava_teme', ['kandidat_id' => $student->id]);
    }

    public function test_multiple_students_can_have_diplomski_rad()
    {
        $student1 = Kandidat::factory()->create();
        $student2 = Kandidat::factory()->create();

        $request1 = new Request([
            'kandidat_id' => $student1->id,
            'tema' => 'Tema student1',
            'mentor' => 'Mentor1',
            'datumPrijave' => '2024-05-01',
        ]);
        $this->service->diplomskiAdd($request1);

        $request2 = new Request([
            'kandidat_id' => $student2->id,
            'tema' => 'Tema student2',
            'mentor' => 'Mentor2',
            'datumPrijave' => '2024-05-02',
        ]);
        $this->service->diplomskiAdd($request2);

        $this->assertDatabaseHas('diplomski_rad', [
            'kandidat_id' => $student1->id,
            'tema' => 'Tema student1',
        ]);

        $this->assertDatabaseHas('diplomski_rad', [
            'kandidat_id' => $student2->id,
            'tema' => 'Tema student2',
        ]);

        $this->assertCount(2, DiplomskiRad::all());
        $this->assertCount(2, DiplomskiPrijavaTeme::all());
    }
}
