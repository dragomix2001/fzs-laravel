<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\KandidatPrilozenaDokumenta;
use App\Services\DocumentManagementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DocumentManagementServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected DocumentManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
        $this->service = app(DocumentManagementService::class);
    }

    public function test_attach_documents_with_dokumenti_prva_only(): void
    {
        $kandidatId = 1;
        $dokumentiPrva = [101, 102, 103];

        $this->service->attachDocumentsForKandidat($kandidatId, $dokumentiPrva, []);

        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => 1,
            'prilozenaDokumenta_id' => 101,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => 1,
            'prilozenaDokumenta_id' => 102,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => 1,
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
        $kandidatId = 2;
        $dokumentiDruga = [201, 202];

        $this->service->attachDocumentsForKandidat($kandidatId, [], $dokumentiDruga);

        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => 2,
            'prilozenaDokumenta_id' => 201,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => 2,
            'prilozenaDokumenta_id' => 202,
            'indikatorAktivan' => 1,
        ]);

        $attachedIds = $this->service->getAttachedDocumentIds($kandidatId);
        $this->assertCount(2, $attachedIds);
    }

    public function test_attach_documents_with_both_arrays(): void
    {
        $kandidatId = 3;
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
        $kandidatId = 4;

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
        $kandidatId = 5;

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
        $kandidatId = 6;

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
        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => 1,
            'prilozenaDokumenta_id' => 101,
            'indikatorAktivan' => 1,
        ]);

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => 2,
            'prilozenaDokumenta_id' => 201,
            'indikatorAktivan' => 1,
        ]);

        $this->service->deleteDocumentsForKandidat(1);

        $this->assertDatabaseMissing('kandidat_prilozena_dokumenta', ['kandidat_id' => 1]);
        $this->assertDatabaseHas('kandidat_prilozena_dokumenta', [
            'kandidat_id' => 2,
            'prilozenaDokumenta_id' => 201,
        ]);
    }

    public function test_attach_documents_sets_indikator_aktivan_to_one(): void
    {
        $kandidatId = 7;
        $dokumentiPrva = [701];

        $this->service->attachDocumentsForKandidat($kandidatId, $dokumentiPrva, []);

        $record = KandidatPrilozenaDokumenta::where('kandidat_id', $kandidatId)
            ->where('prilozenaDokumenta_id', 701)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals(1, $record->indikatorAktivan);
    }
}
