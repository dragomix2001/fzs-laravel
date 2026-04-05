<?php

declare(strict_types=1);

namespace App\Services;

use App\KandidatPrilozenaDokumenta;

/**
 * Document Management Service - Centralized candidate document attachment management.
 *
 * This is a helper service extracted from KandidatService to centralize all document
 * attachment (KandidatPrilozenaDokumenta) operations for candidates.
 *
 * Responsibilities:
 * - Attach documents to candidates (prilozena dokumenta)
 * - Retrieve attached document IDs for a candidate
 * - Delete all attached documents when candidate is deleted
 *
 * @see KandidatService (original implementation)
 * @see KandidatPrilozenaDokumenta (kandidat-document pivot model)
 */
class DocumentManagementService
{
    /**
     * Attach documents to a kandidat.
     *
     * Creates KandidatPrilozenaDokumenta pivot records linking kandidat to documents.
     * Supports two document categories: first-year documents and other-year documents.
     *
     * @param  int  $kandidatId  The kandidat ID
     * @param  array  $dokumentiPrva  Array of document IDs for first year (skolskaGodina_id = 1)
     * @param  array  $dokumentiDruga  Array of document IDs for other years (skolskaGodina_id = 2)
     */
    public function attachDocumentsForKandidat(int $kandidatId, array $dokumentiPrva = [], array $dokumentiDruga = []): void
    {
        foreach ($dokumentiPrva as $dokument) {
            $prilozenDokument = new KandidatPrilozenaDokumenta;
            $prilozenDokument->prilozenaDokumenta_id = $dokument;
            $prilozenDokument->kandidat_id = $kandidatId;
            $prilozenDokument->indikatorAktivan = 1;
            $prilozenDokument->save();
        }

        foreach ($dokumentiDruga as $dokument) {
            $prilozenDokument = new KandidatPrilozenaDokumenta;
            $prilozenDokument->prilozenaDokumenta_id = $dokument;
            $prilozenDokument->kandidat_id = $kandidatId;
            $prilozenDokument->indikatorAktivan = 1;
            $prilozenDokument->save();
        }
    }

    /**
     * Get attached document IDs for a kandidat.
     *
     * Returns array of prilozenaDokumenta_id values for use in form multi-select inputs.
     *
     * @param  int  $kandidatId  The kandidat ID
     * @return array Array of document IDs attached to this kandidat
     */
    public function getAttachedDocumentIds(int $kandidatId): array
    {
        return KandidatPrilozenaDokumenta::where('kandidat_id', $kandidatId)
            ->pluck('prilozenaDokumenta_id')
            ->toArray();
    }

    /**
     * Delete all attached documents for a kandidat.
     *
     * Removes all KandidatPrilozenaDokumenta pivot records when kandidat is deleted.
     * Prevents orphaned document attachment records in database.
     *
     * @param  int  $kandidatId  The kandidat ID
     * @return int Number of deleted records
     */
    public function deleteDocumentsForKandidat(int $kandidatId): int
    {
        return KandidatPrilozenaDokumenta::where('kandidat_id', $kandidatId)->delete();
    }
}
