<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PrilozenaDokumenta;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
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

    protected StatusGodine $upisanaGodinaStatus;

    protected StatusGodine $ponistenaGodinaStatus;

    protected StatusGodine $obnovljenaGodinaStatus;

    protected StatusGodine $zamrznutaGodinaStatus;

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

        $this->upisanaGodinaStatus = StatusGodine::query()->firstOrCreate(
            ['id' => 1],
            ['naziv' => 'Upisana']
        );

        $this->ponistenaGodinaStatus = StatusGodine::query()->firstOrCreate(
            ['id' => 3],
            ['naziv' => 'Poništena']
        );

        $this->obnovljenaGodinaStatus = StatusGodine::query()->firstOrCreate(
            ['id' => 4],
            ['naziv' => 'Obnovljena']
        );

        $this->zamrznutaGodinaStatus = StatusGodine::query()->firstOrCreate(
            ['id' => 7],
            ['naziv' => 'Zamrznuta']
        );

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

    public function test_upisi_studenta_marks_requested_year_as_enrolled_and_updates_student_year(): void
    {
        $nextYear = UpisGodine::create([
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 2,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $response = $this->get('/student/'.$this->osnovniStudent->id.'/upisiStudenta?godina=2&pokusaj=1');

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $this->assertDatabaseHas('upis_godine', [
            'id' => $nextYear->id,
            'statusGodine_id' => 1,
        ]);
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->osnovniStudent->id,
            'godinaStudija_id' => 2,
        ]);
    }

    public function test_upisi_studenta_is_blocked_when_required_documents_are_not_approved(): void
    {
        PrilozenaDokumenta::create([
            'redniBrojDokumenta' => 999,
            'naziv' => 'Obavezna diploma',
            'skolskaGodina_id' => '1',
        ]);

        UpisGodine::create([
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 2,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $response = $this->get('/student/'.$this->osnovniStudent->id.'/upisiStudenta?godina=2&pokusaj=1');

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $response->assertSessionHas('flash-error', 'upis');
        $response->assertSessionHas('error', 'Упис није могућ док сва обавезна документа не буду одобрена.');
        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 2,
            'statusGodine_id' => 3,
        ]);
    }

    public function test_obnovi_godinu_creates_new_attempt_and_updates_previous_attempt(): void
    {
        $response = $this->get('/student/'.$this->osnovniStudent->id.'/obnova?godina=1&tipStudijaId='.$this->osnovneStudije->id);

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 1,
            'pokusaj' => 1,
            'statusGodine_id' => 4,
        ]);
        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 1,
            'pokusaj' => 2,
            'statusGodine_id' => 1,
        ]);
    }

    public function test_obrisi_obnovu_godine_deletes_selected_attempt(): void
    {
        $obnova = UpisGodine::create([
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 1,
            'pokusaj' => 2,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 1,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'datumUpisa' => now(),
        ]);

        $response = $this->get('/student/'.$this->osnovniStudent->id.'/obrisiObnovu?upisId='.$obnova->id);

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $this->assertDatabaseMissing('upis_godine', [
            'id' => $obnova->id,
        ]);
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

    public function test_promeni_status_updates_student_and_selected_enrollment_status(): void
    {
        $upisGodine = UpisGodine::where('kandidat_id', $this->osnovniStudent->id)->firstOrFail();

        $response = $this->get('/student/'.$this->osnovniStudent->id.'/status/7/'.$upisGodine->id);

        $response->assertRedirect();
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->osnovniStudent->id,
            'statusUpisa_id' => 7,
        ]);
        $this->assertDatabaseHas('upis_godine', [
            'id' => $upisGodine->id,
            'statusGodine_id' => 7,
        ]);
    }

    public function test_izmena_godine_returns_edit_view(): void
    {
        $upisGodine = UpisGodine::where('kandidat_id', $this->osnovniStudent->id)->firstOrFail();

        $response = $this->get('/student/'.$upisGodine->id.'/izmenaGodine');

        $response->assertStatus(200);
        $response->assertViewIs('upis.edit');
        $response->assertViewHasAll(['upisGodine', 'statusGodine', 'skolskaGodina']);
    }

    public function test_store_izmena_godine_updates_selected_enrollment_fields(): void
    {
        $upisGodine = UpisGodine::where('kandidat_id', $this->osnovniStudent->id)->firstOrFail();

        $response = $this->post('/student/'.$this->osnovniStudent->id.'/izmenaGodine', [
            'id' => $upisGodine->id,
            'statusGodine_id' => 4,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'datumUpisa' => '2025-01-15',
            'datumUpisa_format' => 'Y-m-d',
            'datumPromene' => '2025-02-01',
            'datumPromene_format' => 'Y-m-d',
        ]);

        $response->assertRedirect('/student/'.$this->osnovniStudent->id.'/upis');
        $this->assertDatabaseHas('upis_godine', [
            'id' => $upisGodine->id,
            'statusGodine_id' => 4,
            'skolskaGodina_id' => $this->skolskaGodina->id,
        ]);
    }

    public function test_masovni_upis_advances_selected_students_to_next_year(): void
    {
        $drugiStudent = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => $this->upisanStatus->id,
            'indikatorAktivan' => 1,
        ]);

        UpisGodine::create([
            'kandidat_id' => $drugiStudent->id,
            'godina' => 1,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 1,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'datumUpisa' => now(),
        ]);

        UpisGodine::create([
            'kandidat_id' => $this->osnovniStudent->id,
            'godina' => 2,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        UpisGodine::create([
            'kandidat_id' => $drugiStudent->id,
            'godina' => 2,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $response = $this->post('/student/masovniUpis', [
            'odabir' => [$this->osnovniStudent->id, $drugiStudent->id],
        ]);

        $response->assertRedirect('/student/index/1');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->osnovniStudent->id,
            'godinaStudija_id' => 2,
        ]);
        $this->assertDatabaseHas('kandidat', [
            'id' => $drugiStudent->id,
            'godinaStudija_id' => 2,
        ]);
    }

    public function test_upis_master_studija_creates_master_clone_and_redirects(): void
    {
        $osnovniStudentSaIndeksom = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 4,
            'statusUpisa_id' => $this->upisanStatus->id,
            'brojIndeksa' => '1001/2024',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/student/'.$osnovniStudentSaIndeksom->id.'/upisMasterStudija?kandidat_id='.$osnovniStudentSaIndeksom->id.'&StudijskiProgram='.$this->masterProgram->id.'&SkolskaGodinaUpisa='.$this->skolskaGodina->id);

        $response->assertRedirect();
        $response->assertSessionHas('flash-success', 'upis');
        $this->assertDatabaseHas('kandidat', [
            'tipStudija_id' => $this->masterStudije->id,
            'studijskiProgram_id' => $this->masterProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
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
