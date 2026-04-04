<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\UpisGodine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected TipStudija $osnovneStudije;

    protected TipStudija $masterStudije;

    protected StudijskiProgram $osnovniProgram;

    protected StudijskiProgram $masterProgram;

    protected SkolskaGodUpisa $skolskaGodina;

    protected GodinaStudija $godinaStudija;

    protected StatusStudiranja $upisanStatus;

    protected StatusStudiranja $zamrznutStatus;

    protected StatusStudiranja $diplomiraoStatus;

    protected StatusStudiranja $odustaoStatus;

    protected Kandidat $osnovniStudent;

    protected Kandidat $masterStudent;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'student_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->osnovneStudije = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->masterStudije = TipStudija::factory()->create([
            'id' => 2,
            'naziv' => 'Master akademske studije',
            'skrNaziv' => 'MAS',
            'indikatorAktivan' => 1,
        ]);

        $this->osnovniProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'indikatorAktivan' => 1,
        ]);

        $this->masterProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->masterStudije->id,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->godinaStudija = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->upisanStatus = StatusStudiranja::factory()->create([
            'id' => 1,
            'naziv' => 'upisan',
            'indikatorAktivan' => 1,
        ]);

        $this->zamrznutStatus = StatusStudiranja::factory()->create([
            'id' => 7,
            'naziv' => 'zamrzao',
            'indikatorAktivan' => 1,
        ]);

        $this->diplomiraoStatus = StatusStudiranja::factory()->create([
            'id' => 6,
            'naziv' => 'diplomirao',
            'indikatorAktivan' => 1,
        ]);

        $this->odustaoStatus = StatusStudiranja::factory()->create([
            'id' => 2,
            'naziv' => 'odustao',
            'indikatorAktivan' => 1,
        ]);

        $this->osnovniStudent = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => $this->upisanStatus->id,
            'indikatorAktivan' => 1,
        ]);

        $this->masterStudent = Kandidat::factory()->create([
            'tipStudija_id' => $this->masterStudije->id,
            'studijskiProgram_id' => $this->masterProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => $this->upisanStatus->id,
            'indikatorAktivan' => 1,
        ]);

        UpisGodine::create([
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 1,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 1,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'datumUpisa' => now(),
        ]);

        UpisGodine::create([
            'kandidat_id' => $this->masterStudent->id,
            'godina' => 1,
            'pokusaj' => 1,
            'tipStudija_id' => $this->masterStudije->id,
            'studijskiProgram_id' => $this->masterProgram->id,
            'statusGodine_id' => 1,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'datumUpisa' => now(),
        ]);

        $this->actingAs($this->user);
    }

    public function test_index_returns_osnovne_students_view(): void
    {
        $response = $this->get('/student/index/1?godina=1&studijskiProgramId='.$this->osnovniProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('student.indeks');
        $response->assertViewHas('tipStudija', 1);
    }

    public function test_index_returns_master_students_view(): void
    {
        $response = $this->get('/student/index/2?studijskiProgramId='.$this->masterProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('student.index_master');
        $response->assertViewHas('tipStudija', 2);
    }

    public function test_index_defaults_invalid_godina_to_first_year(): void
    {
        $response = $this->get('/student/index/1?godina=9&studijskiProgramId='.$this->osnovniProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('student.indeks');
        $response->assertViewHas('studenti', function ($studenti) {
            return $studenti->contains('id', $this->osnovniStudent->id);
        });
    }

    public function test_upis_studenta_returns_view_with_enrollment_data(): void
    {
        $response = $this->get('/student/'.$this->osnovniStudent->id.'/upis');

        $response->assertStatus(200);
        $response->assertViewIs('upis.index');
        $response->assertViewHasAll(['kandidat', 'osnovneStudije', 'masterStudije', 'doktorskeStudije']);
    }

    public function test_upisi_studenta_redirects_with_flash_error_when_missing_godina(): void
    {
        $response = $this->get('/student/'.$this->osnovniStudent->id.'/upisiStudenta');

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $response->assertSessionHas('flash-error', 'upis');
    }

    public function test_ponisti_upis_marks_selected_upis_as_canceled(): void
    {
        $upisGodine = UpisGodine::where('kandidat_id', $this->osnovniStudent->id)->firstOrFail();

        $response = $this->get('/student/'.$this->osnovniStudent->id.'/ponistiUpis?upisId='.$upisGodine->id);

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $this->assertDatabaseHas('upis_godine', [
            'id' => $upisGodine->id,
            'statusGodine_id' => 3,
        ]);
    }

    public function test_zamrznuti_studenti_returns_view(): void
    {
        $zamrznutStudent = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => $this->zamrznutStatus->id,
        ]);

        $response = $this->get('/student/zamrznuti');

        $response->assertStatus(200);
        $response->assertViewIs('student.index_zamrznuti');
        $response->assertViewHas('studenti', function ($studenti) use ($zamrznutStudent) {
            return $studenti->contains('id', $zamrznutStudent->id);
        });
    }

    public function test_diplomirani_studenti_returns_filtered_view(): void
    {
        $diplomirani = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 4,
            'statusUpisa_id' => $this->diplomiraoStatus->id,
        ]);

        $response = $this->get('/student/diplomirani?tipStudijaId=1&studijskiProgramId='.$this->osnovniProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('student.index_diplomirani');
        $response->assertViewHas('studenti', function ($studenti) use ($diplomirani) {
            return $studenti->contains('id', $diplomirani->id);
        });
    }

    public function test_ispisani_studenti_returns_filtered_view(): void
    {
        $ispisani = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 2,
            'statusUpisa_id' => $this->odustaoStatus->id,
        ]);

        $response = $this->get('/student/ispisani?tipStudijaId=1&studijskiProgramId='.$this->osnovniProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('student.index_ispisani');
        $response->assertViewHas('studenti', function ($studenti) use ($ispisani) {
            return $studenti->contains('id', $ispisani->id);
        });
    }
}
