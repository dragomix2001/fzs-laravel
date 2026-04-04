<?php

namespace Tests\Feature;

use App\Models\Obavestenje;
use App\Models\Profesor;
use App\Models\StatusProfesora;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class ObavestenjeControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Profesor $profesor;

    protected int $seededNoticeCount;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $statusProfesora = StatusProfesora::query()->first() ?? StatusProfesora::create([
            'id' => 1,
            'naziv' => 'Aktivan',
            'indikatorAktivan' => 1,
        ]);

        $this->profesor = Profesor::factory()->create([
            'mail' => 'obavestenje_profesor@test.com',
            'status_id' => $statusProfesora->id,
            'indikatorAktivan' => 1,
        ]);

        $this->user = User::create([
            'name' => 'Profesor User',
            'email' => $this->profesor->mail,
            'password' => bcrypt('password'),
            'role' => User::ROLE_PROFESSOR,
        ]);

        $this->seededNoticeCount = Obavestenje::count();

        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_index_filters_by_tip_and_active_status(): void
    {
        $aktivnoOpste = Obavestenje::create([
            'naslov' => 'Aktivno opste',
            'sadrzaj' => 'Tekst',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now()->subDay(),
            'datum_isteka' => now()->addDays(5),
            'profesor_id' => $this->profesor->id,
        ]);

        Obavestenje::create([
            'naslov' => 'Neaktivno opste',
            'sadrzaj' => 'Tekst',
            'tip' => 'opste',
            'aktivan' => 0,
            'datum_objave' => now()->subDay(),
            'datum_isteka' => now()->addDays(5),
            'profesor_id' => $this->profesor->id,
        ]);

        Obavestenje::create([
            'naslov' => 'Aktivno ispit',
            'sadrzaj' => 'Tekst',
            'tip' => 'ispit',
            'aktivan' => 1,
            'datum_objave' => now()->subDay(),
            'datum_isteka' => now()->addDays(5),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->get('/obavestenja?tip=opste&samo_aktivna=1');

        $response->assertStatus(200);
        $response->assertViewIs('obavestenja.index');
        $response->assertViewHas('obavestenja', function ($obavestenja) use ($aktivnoOpste) {
            return $obavestenja->contains('id', $aktivnoOpste->id)
                && ! $obavestenja->contains('naslov', 'Neaktivno opste')
                && ! $obavestenja->contains('naslov', 'Aktivno ispit');
        });
    }

    public function test_create_returns_form_with_professors_and_types(): void
    {
        $response = $this->get('/obavestenja/create');

        $response->assertStatus(200);
        $response->assertViewIs('obavestenja.create');
        $response->assertViewHas('profesori', function ($profesori) {
            return $profesori->contains('id', $this->profesor->id);
        });
        $response->assertViewHas('tipovi', function (array $tipovi) {
            return isset($tipovi['opste'], $tipovi['ispit'], $tipovi['raspored']);
        });
    }

    public function test_store_creates_notice_and_uses_authenticated_professor_email_mapping(): void
    {
        $notificationMock = Mockery::mock(NotificationService::class);
        $notificationMock->shouldNotReceive('sendObavestenjeToAllStudents');
        $this->app->instance(NotificationService::class, $notificationMock);

        $response = $this->post('/obavestenja', [
            'naslov' => 'Novo obavestenje',
            'sadrzaj' => 'Sadrzaj obavestenja',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now()->format('Y-m-d H:i:s'),
            'datum_isteka' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'profesor_id' => null,
        ]);

        $response->assertRedirect(route('obavestenja.index'));
        $response->assertSessionHas('success', 'Обавештење креирано');
        $this->assertDatabaseHas('obavestenja', [
            'naslov' => 'Novo obavestenje',
            'tip' => 'opste',
            'profesor_id' => $this->profesor->id,
            'aktivan' => 1,
        ]);
        $this->assertSame($this->seededNoticeCount + 1, Obavestenje::count());
    }

    public function test_store_with_email_dispatches_notification_service(): void
    {
        $notificationMock = Mockery::mock(NotificationService::class);
        $notificationMock->shouldReceive('sendObavestenjeToAllStudents')
            ->once()
            ->with('Email obavestenje', 'Sadrzaj za sve studente', 'ispit');
        $this->app->instance(NotificationService::class, $notificationMock);

        $response = $this->post('/obavestenja', [
            'naslov' => 'Email obavestenje',
            'sadrzaj' => 'Sadrzaj za sve studente',
            'tip' => 'ispit',
            'aktivan' => 1,
            'datum_objave' => now()->format('Y-m-d H:i:s'),
            'datum_isteka' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'posalji_email' => 1,
        ]);

        $response->assertRedirect(route('obavestenja.index'));
        $this->assertDatabaseHas('obavestenja', [
            'naslov' => 'Email obavestenje',
            'tip' => 'ispit',
        ]);
    }

    public function test_show_returns_notice_view(): void
    {
        $obavestenje = Obavestenje::create([
            'naslov' => 'Detalji',
            'sadrzaj' => 'Detaljan tekst',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->get('/obavestenja/'.$obavestenje->id);

        $response->assertStatus(200);
        $response->assertViewIs('obavestenja.show');
        $response->assertViewHas('obavestenje', function (Obavestenje $notice) use ($obavestenje) {
            return $notice->is($obavestenje);
        });
    }

    public function test_update_persists_notice_changes(): void
    {
        $obavestenje = Obavestenje::create([
            'naslov' => 'Staro',
            'sadrzaj' => 'Stari sadrzaj',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->put('/obavestenja/'.$obavestenje->id, [
            'naslov' => 'Novo',
            'sadrzaj' => 'Novi sadrzaj',
            'tip' => 'stipendija',
            'aktivan' => 0,
            'datum_objave' => now()->format('Y-m-d H:i:s'),
            'datum_isteka' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'profesor_id' => $this->profesor->id,
        ]);

        $response->assertRedirect(route('obavestenja.index'));
        $response->assertSessionHas('success', 'Обавештење ажурирано');
        $this->assertDatabaseHas('obavestenja', [
            'id' => $obavestenje->id,
            'naslov' => 'Novo',
            'sadrzaj' => 'Novi sadrzaj',
            'tip' => 'stipendija',
            'aktivan' => 0,
        ]);
    }

    public function test_toggle_status_flips_active_flag(): void
    {
        $obavestenje = Obavestenje::create([
            'naslov' => 'Status',
            'sadrzaj' => 'Promena statusa',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->get('/obavestenja/'.$obavestenje->id.'/toggle');

        $response->assertRedirect(route('obavestenja.index'));
        $response->assertSessionHas('success', 'Статус промењен');
        $this->assertDatabaseHas('obavestenja', [
            'id' => $obavestenje->id,
            'aktivan' => 0,
        ]);
    }

    public function test_destroy_removes_notice(): void
    {
        $obavestenje = Obavestenje::create([
            'naslov' => 'Brisanje',
            'sadrzaj' => 'Za brisanje',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->delete('/obavestenja/'.$obavestenje->id);

        $response->assertRedirect(route('obavestenja.index'));
        $response->assertSessionHas('success', 'Обавештење обрисано');
        $this->assertDatabaseMissing('obavestenja', [
            'id' => $obavestenje->id,
        ]);
    }

    public function test_javna_returns_only_active_notices(): void
    {
        $aktivno = Obavestenje::create([
            'naslov' => 'Javno aktivno',
            'sadrzaj' => 'Tekst',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(1),
            'profesor_id' => $this->profesor->id,
        ]);

        Obavestenje::create([
            'naslov' => 'Javno isteklo',
            'sadrzaj' => 'Tekst',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now()->subDays(3),
            'datum_isteka' => now()->subDay(),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->get('/obavestenja/javna');

        $response->assertStatus(200);
        $response->assertViewIs('obavestenja.javna');
        $response->assertViewHas('obavestenja', function ($obavestenja) use ($aktivno) {
            return $obavestenja->contains('id', $aktivno->id)
                && ! $obavestenja->contains('naslov', 'Javno isteklo');
        });
    }

    public function test_moja_returns_user_specific_and_general_active_notices(): void
    {
        $licno = Obavestenje::create([
            'naslov' => 'Licno',
            'sadrzaj' => 'Za korisnika',
            'tip' => 'ispit',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);
        $licno->korisnici()->attach($this->user->id, [
            'procitano' => 0,
            'datum_citanja' => null,
        ]);

        $opste = Obavestenje::create([
            'naslov' => 'Opste aktivno',
            'sadrzaj' => 'Za sve',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);

        Obavestenje::create([
            'naslov' => 'Opste neaktivno',
            'sadrzaj' => 'Ne treba da se vidi',
            'tip' => 'opste',
            'aktivan' => 0,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(2),
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->get('/moja-obavestenja');

        $response->assertStatus(200);
        $response->assertViewIs('obavestenja.moja');
        $response->assertViewHas('obavestenja', function ($obavestenja) use ($licno, $opste) {
            return $obavestenja->contains('id', $licno->id)
                && $obavestenja->contains('id', $opste->id)
                && ! $obavestenja->contains('naslov', 'Opste neaktivno');
        });
    }
}
