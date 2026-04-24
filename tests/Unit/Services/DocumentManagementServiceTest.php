<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Kandidat;
use App\Models\KandidatPrilozenaDokumenta;
use App\Models\PrilozenaDokumenta;
use App\Models\User;
use App\Services\DocumentManagementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected DocumentManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        Model::unguard();
        $this->service = app(DocumentManagementService::class);
    }

    public function test_attach_documents_stores_uploaded_files_and_metadata(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();
        $documentId = 111;
        $file = UploadedFile::fake()->create('izvod.pdf', 120, 'application/pdf');

        $this->service->attachDocumentsForKandidat(
            $kandidatId,
            [$documentId],
            [],
            [$documentId => $file],
            []
        );

        $record = KandidatPrilozenaDokumenta::query()
            ->where('kandidat_id', $kandidatId)
            ->where('prilozenaDokumenta_id', $documentId)
            ->first();

        $this->assertNotNull($record);
        $this->assertNotNull($record->file_path);
        $this->assertSame('izvod.pdf', $record->file_name);
        $this->assertSame('application/pdf', $record->mime_type);
        $this->assertNotNull($record->file_size);
        Storage::disk('uploads')->assertExists($record->file_path);
    }

    public function test_delete_documents_for_kandidat_removes_uploaded_files_from_storage(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();
        $documentId = 222;
        $file = UploadedFile::fake()->create('uverenje.pdf', 90, 'application/pdf');

        $this->service->attachDocumentsForKandidat(
            $kandidatId,
            [$documentId],
            [],
            [$documentId => $file],
            []
        );

        $record = KandidatPrilozenaDokumenta::query()
            ->where('kandidat_id', $kandidatId)
            ->where('prilozenaDokumenta_id', $documentId)
            ->first();

        $this->assertNotNull($record);
        $this->assertNotNull($record->file_path);
        Storage::disk('uploads')->assertExists($record->file_path);

        $this->service->deleteDocumentsForKandidat($kandidatId);

        Storage::disk('uploads')->assertMissing($record->file_path);
    }

    public function test_attach_documents_with_dokumenti_prva_only(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();
        $dokumentiPrva = [101, 102, 103];

        $this->service->attachDocumentsForKandidat($kandidatId, $dokumentiPrva, []);

        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 101,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 102,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 103,
            'indikatorAktivan' => 1,
        ]);

        $attachedIds = $this->service->getAttachedDocumentIds($kandidatId);
        $this->assertCount(3, $attachedIds);
        $this->assertContains(101, $attachedIds);
        $this->assertContains(102, $attachedIds);
        $this->assertContains(103, $attachedIds);
    }

    public function test_attach_documents_with_dokumenti_druga_only(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();
        $dokumentiDruga = [201, 202];

        $this->service->attachDocumentsForKandidat($kandidatId, [], $dokumentiDruga);

        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 201,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 202,
            'indikatorAktivan' => 1,
        ]);

        $attachedIds = $this->service->getAttachedDocumentIds($kandidatId);
        $this->assertCount(2, $attachedIds);
    }

    public function test_attach_documents_with_both_arrays(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();
        $dokumentiPrva = [301, 302];
        $dokumentiDruga = [401, 402, 403];

        $this->service->attachDocumentsForKandidat($kandidatId, $dokumentiPrva, $dokumentiDruga);

        $attachedIds = $this->service->getAttachedDocumentIds($kandidatId);
        $this->assertCount(5, $attachedIds);
        $this->assertContains(301, $attachedIds);
        $this->assertContains(302, $attachedIds);
        $this->assertContains(401, $attachedIds);
        $this->assertContains(402, $attachedIds);
        $this->assertContains(403, $attachedIds);
    }

    public function test_attach_documents_with_empty_arrays_does_nothing(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();

        $this->service->attachDocumentsForKandidat($kandidatId, [], []);

        $attachedIds = $this->service->getAttachedDocumentIds($kandidatId);
        $this->assertCount(0, $attachedIds);
        $this->assertEmpty($attachedIds);
    }

    public function test_get_attached_document_ids_returns_empty_array_when_no_documents(): void
    {
        $attachedIds = $this->service->getAttachedDocumentIds(9999);

        $this->assertIsArray($attachedIds);
        $this->assertEmpty($attachedIds);
    }

    public function test_get_attached_document_ids_returns_populated_array(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 501,
            'indikatorAktivan' => 1,
        ]);

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 502,
            'indikatorAktivan' => 1,
        ]);

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 503,
            'indikatorAktivan' => 1,
        ]);

        $attachedIds = $this->service->getAttachedDocumentIds($kandidatId);

        $this->assertCount(3, $attachedIds);
        $this->assertEquals([501, 502, 503], $attachedIds);
    }

    public function test_delete_documents_for_kandidat_removes_all(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 601,
            'indikatorAktivan' => 1,
        ]);

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 602,
            'indikatorAktivan' => 1,
        ]);

        $deletedCount = $this->service->deleteDocumentsForKandidat($kandidatId);

        $this->assertEquals(2, $deletedCount);
        $this->assertDatabaseMissing('kandidat_prilozena_dokumenta', ['kandidat_id' => $kandidatId]);
    }

    public function test_delete_documents_for_kandidat_returns_zero_when_empty(): void
    {
        $deletedCount = $this->service->deleteDocumentsForKandidat(9999);

        $this->assertEquals(0, $deletedCount);
    }

    public function test_data_isolation_between_kandidats(): void
    {
        $firstKandidatId = $this->nextUnusedKandidatId();
        $secondKandidatId = $firstKandidatId + 1;

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $firstKandidatId,
            'prilozenaDokumenta_id' => 101,
            'indikatorAktivan' => 1,
        ]);

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $secondKandidatId,
            'prilozenaDokumenta_id' => 201,
            'indikatorAktivan' => 1,
        ]);

        $this->service->deleteDocumentsForKandidat($firstKandidatId);

        $this->assertDatabaseMissing('kandidat_prilozena_dokumenta', ['kandidat_id' => $firstKandidatId]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $secondKandidatId,
            'prilozenaDokumenta_id' => 201,
        ]);
    }

    public function test_attach_documents_sets_indikator_aktivan_to_one(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();
        $dokumentiPrva = [701];

        $this->service->attachDocumentsForKandidat($kandidatId, $dokumentiPrva, []);

        $record = KandidatPrilozenaDokumenta::where('kandidat_id', $kandidatId)
            ->where('prilozenaDokumenta_id', 701)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals(1, $record->indikatorAktivan);
        $this->assertEquals(KandidatPrilozenaDokumenta::STATUS_PENDING, $record->review_status);
    }

    public function test_document_attachment_defaults_to_pending_review_status(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 801,
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 801,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_PENDING,
        ]);
    }

    public function test_document_attachment_model_relations_are_available(): void
    {
        $kandidat = $this->createKandidat();
        $dokument = PrilozenaDokumenta::create([
            'redniBrojDokumenta' => 901,
            'naziv' => 'Izvod iz maticne knjige',
            'skolskaGodina_id' => '1',
        ]);
        $reviewer = User::create([
            'name' => 'Admin Reviewer',
            'email' => 'reviewer_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $attachment = KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidat->id,
            'prilozenaDokumenta_id' => $dokument->id,
            'indikatorAktivan' => 1,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_APPROVED,
            'reviewer_id' => $reviewer->id,
        ]);

        $this->assertTrue($attachment->kandidat->is($kandidat));
        $this->assertTrue($attachment->dokument->is($dokument));
        $this->assertTrue($attachment->reviewer->is($reviewer));
        $this->assertTrue($kandidat->kandidatDokumenta->contains('id', $attachment->id));
        $this->assertTrue($kandidat->prilozenaDokumenta->contains('id', $dokument->id));
        $this->assertTrue($dokument->kandidati->contains('id', $kandidat->id));
    }

    public function test_document_status_scopes_filter_review_states(): void
    {
        $kandidatId = $this->nextUnusedKandidatId();

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 901,
            'indikatorAktivan' => 1,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_PENDING,
        ]);
        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 902,
            'indikatorAktivan' => 1,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_APPROVED,
        ]);
        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 903,
            'indikatorAktivan' => 1,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_REJECTED,
        ]);
        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidatId,
            'prilozenaDokumenta_id' => 904,
            'indikatorAktivan' => 1,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_NEEDS_REVISION,
        ]);

        $this->assertCount(1, KandidatPrilozenaDokumenta::pending()->where('kandidat_id', $kandidatId)->get());
        $this->assertCount(1, KandidatPrilozenaDokumenta::approved()->where('kandidat_id', $kandidatId)->get());
        $this->assertCount(1, KandidatPrilozenaDokumenta::rejected()->where('kandidat_id', $kandidatId)->get());
        $this->assertCount(1, KandidatPrilozenaDokumenta::needsRevision()->where('kandidat_id', $kandidatId)->get());
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
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'Kandidat',
            'jmbg' => (string) random_int(1000000000000, 9999999999999),
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

    private function nextUnusedKandidatId(): int
    {
        return ((int) KandidatPrilozenaDokumenta::max('kandidat_id')) + 1000;
    }

    private function firstIdOrCreate(string $table, array $attributes): int
    {
        $id = DB::table($table)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return (int) DB::table($table)->insertGetId(array_merge($attributes, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}
