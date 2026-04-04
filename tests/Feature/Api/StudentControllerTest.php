<?php

namespace Tests\Feature\Api;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\UpisGodine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Kandidat $kandidat;

    protected SkolskaGodUpisa $skolskaGodina;

    protected PredmetProgram $predmetProgram;

    protected AktivniIspitniRokovi $rok;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $tipStudija = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        DB::table('krsna_slava')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Sveti Nikola',
            'datumSlave' => '19.12.',
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('opsti_uspeh')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Odlican',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('opstina')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Beograd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mesto')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Beograd',
            'opstina_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('godina_studija')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        StatusStudiranja::query()->firstOrCreate(
            ['id' => 1],
            ['naziv' => 'upisan', 'indikatorAktivan' => 1]
        );

        $program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->user = User::create([
            'name' => 'Api Student User',
            'email' => 'api.student@test.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_STUDENT,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
            'aktivan' => 1,
        ]);

        $this->kandidat = Kandidat::factory()->create([
            'email' => $this->user->email,
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => 1,
            'indikatorAktivan' => 1,
        ]);

        $tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::create([
            'id' => 1,
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        $this->predmetProgram = PredmetProgram::create([
            'studijskiProgram_id' => $program->id,
            'tipStudija_id' => $tipStudija->id,
            'predmet_id' => 1,
            'godinaStudija_id' => 1,
            'tipPredmeta_id' => $tipPredmeta->id,
            'semestar' => 1,
            'espb' => 6,
            'predavanja' => 3,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'statusPredmeta' => 1,
            'indikatorAktivan' => 1,
        ]);

        DB::table('ispitni_rok')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Januarski',
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rok = AktivniIspitniRokovi::create([
            'rok_id' => 1,
            'naziv' => 'Januarski rok',
            'pocetak' => now()->startOfMonth(),
            'kraj' => now()->endOfMonth(),
            'tipRoka_id' => 1,
            'komentar' => 'Test rok',
            'indikatorAktivan' => 1,
        ]);

        UpisGodine::create([
            'kandidat_id' => $this->kandidat->id,
            'godina' => 1,
            'pokusaj' => 1,
            'tipStudija_id' => $tipStudija->id,
            'statusGodine_id' => 1,
            'studijskiProgram_id' => $program->id,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'datumUpisa' => now(),
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_profile_returns_current_student_profile(): void
    {
        $response = $this->getJson('/api/v1/student/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Профил успешно учитат');
        $response->assertJsonPath('data.id', $this->kandidat->id);
    }

    public function test_polozeni_ispiti_returns_active_passed_exams(): void
    {
        $active = PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'indikatorAktivan' => 1,
            'konacnaOcena' => 9,
        ]);

        PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'indikatorAktivan' => 0,
            'konacnaOcena' => 6,
        ]);

        $response = $this->getJson('/api/v1/student/ispiti');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Положени испити успешно учитати');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $active->id);
    }

    public function test_prijave_returns_exam_registrations_with_relations(): void
    {
        $prijava = PrijavaIspita::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'datum' => now(),
            'brojPolaganja' => 1,
        ]);

        $response = $this->getJson('/api/v1/student/prijave');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Пријаве испита успешно учитате');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $prijava->id);
    }

    public function test_upis_returns_enrollment_history(): void
    {
        $response = $this->getJson('/api/v1/student/upis');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Историја уписа успешно учитата');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.kandidat_id', $this->kandidat->id);
    }

    public function test_stats_returns_student_exam_statistics(): void
    {
        PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'indikatorAktivan' => 1,
            'konacnaOcena' => 8,
        ]);

        PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'indikatorAktivan' => 1,
            'konacnaOcena' => 10,
        ]);

        $response = $this->getJson('/api/v1/student/stats');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Статистика успешно учитата');
        $response->assertJsonPath('data.polozeni_ispiti', 2);
        $response->assertJsonPath('data.prosek', 9);
        $response->assertJsonPath('data.espb', 12);
    }

    public function test_profile_returns_not_found_when_student_record_missing(): void
    {
        $this->kandidat->delete();

        $response = $this->getJson('/api/v1/student/profile');

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Студент није пронађен');
    }
}
