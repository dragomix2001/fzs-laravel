<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Kandidat;
use App\Models\KandidatPrilozenaDokumenta;
use App\Models\PrilozenaDokumenta;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class DocumentReviewControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Kandidat $kandidat;

    private KandidatPrilozenaDokumenta $attachment;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->admin = User::create([
            'name' => 'Document Admin',
            'email' => 'document_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->kandidat = $this->createKandidat();
        $dokument = PrilozenaDokumenta::create([
            'redniBrojDokumenta' => random_int(100, 999),
            'naziv' => 'Diploma srednje skole',
            'skolskaGodina_id' => '1',
        ]);

        $this->attachment = KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $this->kandidat->id,
            'prilozenaDokumenta_id' => $dokument->id,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_admin_can_view_document_review_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('kandidat.documents.review', $this->kandidat));

        $response->assertOk();
        $response->assertViewIs('kandidat.documents_review');
        $response->assertViewHas('kandidat', fn (Kandidat $kandidat) => $kandidat->is($this->kandidat));
        $response->assertViewHas('dokumenta', fn ($dokumenta) => $dokumenta->contains('id', $this->attachment->id));
    }

    public function test_admin_can_view_incomplete_document_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('kandidat.documents.incomplete'));

        $response->assertOk();
        $response->assertViewIs('kandidat.documents_incomplete');
        $response->assertViewHas('rows', function (array $rows) {
            return collect($rows)->contains(fn (array $row) => $row['kandidat']->id === $this->kandidat->id);
        });
    }

    public function test_admin_can_approve_document(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('kandidat.documents.approve', [$this->kandidat, $this->attachment]));

        $response->assertRedirect(route('kandidat.documents.review', $this->kandidat));
        $response->assertSessionHas('success', 'Документ је одобрен.');
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'id' => $this->attachment->id,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_APPROVED,
            'reviewer_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'kandidat_prilozena_dokumenta',
            'record_id' => $this->attachment->id,
            'action' => 'update',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_reject_document_with_notes(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('kandidat.documents.reject', [$this->kandidat, $this->attachment]), [
                'notes' => 'Nedostaje overena kopija.',
            ]);

        $response->assertRedirect(route('kandidat.documents.review', $this->kandidat));
        $response->assertSessionHas('success', 'Документ је одбијен.');
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'id' => $this->attachment->id,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_REJECTED,
            'notes' => 'Nedostaje overena kopija.',
            'reviewer_id' => $this->admin->id,
        ]);
    }

    public function test_reject_document_notifies_candidate_user(): void
    {
        $candidateUser = User::create([
            'name' => 'Candidate Student',
            'email' => $this->kandidat->email,
            'password' => bcrypt('password'),
            'role' => User::ROLE_STUDENT,
        ]);

        $notificationMock = Mockery::mock(NotificationService::class);
        $notificationMock->shouldReceive('notifyUser')
            ->once()
            ->with(
                $candidateUser->id,
                'Документ је одбијен',
                Mockery::pattern('/Diploma srednje skole/'),
                'error',
                Mockery::type('array')
            );
        $this->app->instance(NotificationService::class, $notificationMock);

        $response = $this->actingAs($this->admin)
            ->patch(route('kandidat.documents.reject', [$this->kandidat, $this->attachment]), [
                'notes' => 'Nedostaje pecat.',
            ]);

        $response->assertRedirect(route('kandidat.documents.review', $this->kandidat));
    }

    public function test_approve_document_notifies_candidate_when_required_documents_become_complete(): void
    {
        $candidateUser = User::create([
            'name' => 'Candidate Student',
            'email' => $this->kandidat->email,
            'password' => bcrypt('password'),
            'role' => User::ROLE_STUDENT,
        ]);

        $notificationMock = Mockery::mock(NotificationService::class);
        $notificationMock->shouldReceive('notifyUser')
            ->once()
            ->with(
                $candidateUser->id,
                'Документација је комплетна',
                'Сва обавезна документа су одобрена. Можете наставити поступак уписа.',
                'success',
                Mockery::type('array')
            );
        $this->app->instance(NotificationService::class, $notificationMock);

        $response = $this->actingAs($this->admin)
            ->patch(route('kandidat.documents.approve', [$this->kandidat, $this->attachment]));

        $response->assertRedirect(route('kandidat.documents.review', $this->kandidat));
    }

    public function test_needs_revision_requires_notes(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('kandidat.documents.review', $this->kandidat))
            ->patch(route('kandidat.documents.needs-revision', [$this->kandidat, $this->attachment]), [
                'notes' => '',
            ]);

        $response->assertRedirect(route('kandidat.documents.review', $this->kandidat));
        $response->assertSessionHasErrors('notes');
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'id' => $this->attachment->id,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_PENDING,
        ]);
    }

    public function test_non_admin_user_is_redirected_from_document_review_routes(): void
    {
        $student = User::create([
            'name' => 'Student User',
            'email' => 'document_student_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_STUDENT,
        ]);

        $response = $this->actingAs($student)
            ->get(route('kandidat.documents.review', $this->kandidat));

        $response->assertRedirect('/');
    }

    public function test_document_review_route_returns_not_found_for_mismatched_candidate_and_attachment(): void
    {
        $otherKandidat = $this->createKandidat();

        $response = $this->actingAs($this->admin)
            ->patch(route('kandidat.documents.approve', [$otherKandidat, $this->attachment]));

        $response->assertNotFound();
    }

    private function createKandidat(): Kandidat
    {
        $tipStudijaId = $this->firstIdOrCreate('tip_studija', [
            'naziv' => 'Osnovne akademske studije',
            'opis' => 'Test tip studija',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);
        $skolskaGodinaUpisaId = $this->firstIdOrCreate('skolska_god_upisa', [
            'naziv' => '2025/2026',
        ]);
        $krsnaSlavaId = $this->firstIdOrCreate('krsna_slava', [
            'naziv' => 'Sv. Nikola',
            'datumSlave' => '19.12.',
            'indikatorAktivan' => 1,
        ]);
        $srednjaSkolaId = $this->firstIdOrCreate('srednje_skole_fakulteti', [
            'naziv' => 'Gimnazija',
            'indSkoleFakulteta' => 1,
        ]);
        $opstiUspehId = $this->firstIdOrCreate('opsti_uspeh', [
            'naziv' => 'Odlican',
        ]);
        $godinaStudijaId = $this->firstIdOrCreate('godina_studija', [
            'naziv' => 'Prva godina',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'prvoj godini',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);
        $regionId = $this->firstIdOrCreate('region', [
            'naziv' => 'Beograd',
        ]);
        $opstinaId = $this->firstIdOrCreate('opstina', [
            'naziv' => 'Stari Grad',
            'region_id' => $regionId,
        ]);
        $mestoId = $this->firstIdOrCreate('mesto', [
            'naziv' => 'Beograd',
            'opstina_id' => $opstinaId,
        ]);
        $studijskiProgramId = $this->firstIdOrCreate('studijski_program', [
            'naziv' => 'Sport i fizicko vaspitanje',
            'skrNazivStudijskogPrograma' => 'SFV',
            'zvanje' => 'Profesor sporta',
            'tipStudija_id' => $tipStudijaId,
            'indikatorAktivan' => 1,
        ]);

        return Kandidat::create([
            'imeKandidata' => 'Petar',
            'prezimeKandidata' => 'Petrovic',
            'jmbg' => (string) random_int(1000000000000, 9999999999999),
            'email' => 'candidate_'.uniqid().'@test.com',
            'krsnaSlava_id' => $krsnaSlavaId,
            'uspehSrednjaSkola_id' => $srednjaSkolaId,
            'opstiUspehSrednjaSkola_id' => $opstiUspehId,
            'skolskaGodinaUpisa_id' => $skolskaGodinaUpisaId,
            'indikatorAktivan' => 1,
            'studijskiProgram_id' => $studijskiProgramId,
            'tipStudija_id' => $tipStudijaId,
            'godinaStudija_id' => $godinaStudijaId,
            'mesto_id' => $mestoId,
        ]);
    }

    private function firstIdOrCreate(string $table, array $attributes): int
    {
        $id = DB::table($table)->where($attributes)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return (int) DB::table($table)->insertGetId(array_merge($attributes, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}