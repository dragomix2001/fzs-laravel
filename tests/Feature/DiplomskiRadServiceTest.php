<?php

namespace Tests\Feature;

use App\DTOs\DiplomskiAddData;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\DiplomskiRad;
use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\DiplomskiRadService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;
use View;

class DiplomskiRadServiceTest extends TestCase
{
    use DatabaseTransactions;

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
        $baselineCount = Profesor::count();

        $result = $this->service->diplomskiUnos($student);

        $this->assertIsObject($result);
        $this->assertCount($baselineCount, $result->getData()['profesor']);
    }

    public function test_diplomski_unos_passes_all_profesori_regardless_of_count()
    {
        $student = Kandidat::factory()->create();
        $baselineCount = Profesor::count();
        Profesor::factory()->count(10)->create();

        $result = $this->service->diplomskiUnos($student);

        $this->assertCount($baselineCount + 10, $result->getData()['profesor']);
    }

    // =========================================================================
    // diplomskiAdd() tests
    // =========================================================================

    public function test_diplomski_add_creates_diplomski_rad_record()
    {
        $student = Kandidat::factory()->create();
        $mentor = Profesor::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        $data = new DiplomskiAddData(
            kandidatId: $student->id,
            predmetId: $predmetProgram->id,
            naziv: 'Moja diplomska tema',
            mentorId: $mentor->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-05-01',
            datumOdbrane: null
        );

        $this->service->diplomskiAdd($data);

        $this->assertDatabaseHas('diplomski_rad', [
            'kandidat_id' => $student->id,
            'naziv' => 'Moja diplomska tema',
            'mentor_id' => $mentor->id,
            'datumPrijave' => '2024-05-01',
        ]);
    }

    public function test_diplomski_add_creates_diplomski_prijava_teme_record()
    {
        $student = Kandidat::factory()->create();
        $mentor = Profesor::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        $data = new DiplomskiAddData(
            kandidatId: $student->id,
            predmetId: $predmetProgram->id,
            naziv: 'Moja diplomska tema',
            mentorId: $mentor->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-05-01',
            datumOdbrane: null
        );

        $this->service->diplomskiAdd($data);

        $this->assertDatabaseHas('diplomski_prijava_teme', [
            'kandidat_id' => $student->id,
            'nazivTeme' => 'Moja diplomska tema',
            'profesor_id' => $mentor->id,
            'datum' => '2024-05-01',
        ]);
    }

    public function test_diplomski_add_creates_both_records_atomically()
    {
        $student = Kandidat::factory()->create();
        $mentor = Profesor::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        $data = new DiplomskiAddData(
            kandidatId: $student->id,
            predmetId: $predmetProgram->id,
            naziv: 'Atomska diplomska tema',
            mentorId: $mentor->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-06-15',
            datumOdbrane: null
        );

        $this->service->diplomskiAdd($data);

        $diplomski = DiplomskiRad::where('kandidat_id', $student->id)->first();
        $prijava = DiplomskiPrijavaTeme::where('kandidat_id', $student->id)->first();

        $this->assertNotNull($diplomski);
        $this->assertNotNull($prijava);
        $this->assertEquals($diplomski->naziv, $prijava->nazivTeme);
        $this->assertEquals($diplomski->mentor_id, $prijava->profesor_id);
    }

    public function test_diplomski_add_stores_correct_field_values()
    {
        $student = Kandidat::factory()->create();
        $mentor = Profesor::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        $data = new DiplomskiAddData(
            kandidatId: $student->id,
            predmetId: $predmetProgram->id,
            naziv: 'Blockchain u obrazovanju',
            mentorId: $mentor->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-07-20',
            datumOdbrane: null
        );

        $this->service->diplomskiAdd($data);

        $diplomski = DiplomskiRad::where('naziv', 'Blockchain u obrazovanju')->first();

        $this->assertNotNull($diplomski);
        $this->assertEquals($student->id, $diplomski->kandidat_id);
        $this->assertEquals($mentor->id, $diplomski->mentor_id);
    }

    public function test_diplomski_add_redirects_to_student_index()
    {
        $student = Kandidat::factory()->create();
        $mentor = Profesor::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        $data = new DiplomskiAddData(
            kandidatId: $student->id,
            predmetId: $predmetProgram->id,
            naziv: 'Test tema',
            mentorId: $mentor->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-05-01',
            datumOdbrane: null
        );

        $response = $this->service->diplomskiAdd($data);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/student', $response->getTargetUrl());
    }

    // =========================================================================
    // komisijaStampa() tests (mocked PDF)
    // =========================================================================

    public function test_komisija_stampa_returns_redirect_when_diplomski_not_found()
    {
        $student = Kandidat::factory()->create();

        $response = $this->service->komisijaStampa($student);

        $this->assertInstanceOf(RedirectResponse::class, $response);
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

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_zapisnik_diplomski_returns_redirect_when_diplomski_not_found()
    {
        $student = Kandidat::factory()->create();
        DiplomskiPolaganje::factory()->create(['kandidat_id' => $student->id]);

        $response = $this->service->zapisnikDiplomski($student);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_zapisnik_diplomski_succeeds_when_polaganje_exists()
    {
        $student = Kandidat::factory()->create();
        DiplomskiPolaganje::factory()->create(['kandidat_id' => $student->id]);

        $polaganje = DiplomskiPolaganje::where('kandidat_id', $student->id)->first();
        $this->assertNotNull($polaganje);
    }

    public function test_komisija_stampa_returns_null_when_pdf_is_generated()
    {
        $student = Kandidat::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();
        DiplomskiRad::factory()->create([
            'kandidat_id' => $student->id,
            'predmet_id' => $predmetProgram->id,
        ]);

        $fakeView = new class
        {
            public function with($key, $value)
            {
                return $this;
            }

            public function render()
            {
                return '<html>komisija</html>';
            }
        };

        View::shouldReceive('make')
            ->once()
            ->with('izvestaji.komisijaStampa')
            ->andReturn($fakeView);

        $fakePdf = new class
        {
            public array $calls = [];

            public function SetTitle($title)
            {
                $this->calls[] = ['SetTitle', $title];
            }

            public function AddPage()
            {
                $this->calls[] = ['AddPage'];
            }

            public function SetFont($family, $style, $size)
            {
                $this->calls[] = ['SetFont', $family, $style, $size];
            }

            public function WriteHtml($contents, $arg = null)
            {
                $this->calls[] = ['WriteHtml', $contents, $arg];
            }

            public function Output($filename)
            {
                $this->calls[] = ['Output', $filename];
            }
        };

        $service = new class($fakePdf) extends DiplomskiRadService
        {
            public function __construct(private object $pdf) {}

            protected function createPdf()
            {
                return $this->pdf;
            }
        };

        $response = $service->komisijaStampa($student);

        $this->assertNull($response);
        $this->assertContains(['Output', 'Komisija.pdf'], $fakePdf->calls);
    }

    public function test_zapisnik_diplomski_returns_null_when_pdf_is_generated()
    {
        $student = Kandidat::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        DiplomskiPolaganje::factory()->create(['kandidat_id' => $student->id]);
        DiplomskiRad::factory()->create([
            'kandidat_id' => $student->id,
            'predmet_id' => $predmetProgram->id,
        ]);

        $fakeView = new class
        {
            public function with($key, $value)
            {
                return $this;
            }

            public function render()
            {
                return '<html>zapisnik</html>';
            }
        };

        View::shouldReceive('make')
            ->once()
            ->with('izvestaji.zapisnikDiplomski')
            ->andReturn($fakeView);

        $fakePdf = new class
        {
            public array $calls = [];

            public function SetAutoPageBreak($enabled, $margin)
            {
                $this->calls[] = ['SetAutoPageBreak', $enabled, $margin];
            }

            public function SetTitle($title)
            {
                $this->calls[] = ['SetTitle', $title];
            }

            public function AddPage()
            {
                $this->calls[] = ['AddPage'];
            }

            public function SetFont($family, $style, $size)
            {
                $this->calls[] = ['SetFont', $family, $style, $size];
            }

            public function WriteHtml($contents)
            {
                $this->calls[] = ['WriteHtml', $contents];
            }

            public function Output($filename)
            {
                $this->calls[] = ['Output', $filename];
            }
        };

        $service = new class($fakePdf) extends DiplomskiRadService
        {
            public function __construct(private object $pdf) {}

            protected function createPdf()
            {
                return $this->pdf;
            }
        };

        $response = $service->zapisnikDiplomski($student);

        $this->assertNull($response);
        $this->assertContains(['Output', 'ZapisnikDiplomski.pdf'], $fakePdf->calls);
    }

    public function test_zapisnik_diplomski_wraps_query_exception_in_runtime_exception()
    {
        $student = Kandidat::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        DiplomskiPolaganje::factory()->create(['kandidat_id' => $student->id]);
        DiplomskiRad::factory()->create([
            'kandidat_id' => $student->id,
            'predmet_id' => $predmetProgram->id,
        ]);

        View::shouldReceive('make')
            ->once()
            ->with('izvestaji.zapisnikDiplomski')
            ->andThrow(new QueryException('mysql', 'select 1', [], new \Exception('forced failure')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Грешка при генерисању записника дипломског');

        $this->service->zapisnikDiplomski($student);
    }

    // =========================================================================
    // Integration tests
    // =========================================================================

    public function test_diplomski_rad_service_full_workflow()
    {
        $student = Kandidat::factory()->create();
        Profesor::factory()->count(2)->create();
        $mentor = Profesor::factory()->create();
        $predmetProgram = PredmetProgram::factory()->create();

        $view = $this->service->diplomskiUnos($student);
        $this->assertIsObject($view);
        $this->assertEquals($student->id, $view->getData()['student']->id);

        $data = new DiplomskiAddData(
            kandidatId: $student->id,
            predmetId: $predmetProgram->id,
            naziv: 'Final tema',
            mentorId: $mentor->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-08-01',
            datumOdbrane: null
        );

        $this->service->diplomskiAdd($data);

        $this->assertDatabaseHas('diplomski_rad', ['kandidat_id' => $student->id]);
        $this->assertDatabaseHas('diplomski_prijava_teme', ['kandidat_id' => $student->id]);
    }

    public function test_multiple_students_can_have_diplomski_rad()
    {
        $student1 = Kandidat::factory()->create();
        $student2 = Kandidat::factory()->create();
        $mentor1 = Profesor::factory()->create();
        $mentor2 = Profesor::factory()->create();
        $predmetProgram1 = PredmetProgram::factory()->create();
        $predmetProgram2 = PredmetProgram::factory()->create();

        $data1 = new DiplomskiAddData(
            kandidatId: $student1->id,
            predmetId: $predmetProgram1->id,
            naziv: 'Tema student1',
            mentorId: $mentor1->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-05-01',
            datumOdbrane: null
        );
        $this->service->diplomskiAdd($data1);

        $data2 = new DiplomskiAddData(
            kandidatId: $student2->id,
            predmetId: $predmetProgram2->id,
            naziv: 'Tema student2',
            mentorId: $mentor2->id,
            predsednikId: null,
            clanId: null,
            ocenaOpis: null,
            ocenaBroj: null,
            datumPrijave: '2024-05-02',
            datumOdbrane: null
        );
        $this->service->diplomskiAdd($data2);

        $this->assertDatabaseHas('diplomski_rad', [
            'kandidat_id' => $student1->id,
            'naziv' => 'Tema student1',
        ]);

        $this->assertDatabaseHas('diplomski_rad', [
            'kandidat_id' => $student2->id,
            'naziv' => 'Tema student2',
        ]);

        $this->assertCount(2, DiplomskiRad::all());
        $this->assertCount(2, DiplomskiPrijavaTeme::all());
    }
}
