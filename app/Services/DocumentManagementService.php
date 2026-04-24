<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KandidatPrilozenaDokumenta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    public function attachDocumentsForKandidat(
        int $kandidatId,
        array $dokumentiPrva = [],
        array $dokumentiDruga = [],
        array $documentUploadsPrva = [],
        array $documentUploadsDruga = []
    ): void {
        $dokumentiPrva = $this->mergeSelectedWithUploaded($dokumentiPrva, $documentUploadsPrva);
        $dokumentiDruga = $this->mergeSelectedWithUploaded($dokumentiDruga, $documentUploadsDruga);

        foreach ($dokumentiPrva as $dokument) {
            $this->createAttachment($kandidatId, (int) $dokument, $documentUploadsPrva[(int) $dokument] ?? null);
        }

        foreach ($dokumentiDruga as $dokument) {
            $this->createAttachment($kandidatId, (int) $dokument, $documentUploadsDruga[(int) $dokument] ?? null);
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
        $paths = KandidatPrilozenaDokumenta::where('kandidat_id', $kandidatId)
            ->whereNotNull('file_path')
            ->pluck('file_path')
            ->all();

        if (! empty($paths)) {
            Storage::disk('uploads')->delete($paths);
        }

        return KandidatPrilozenaDokumenta::where('kandidat_id', $kandidatId)->delete();
    }

    private function createAttachment(int $kandidatId, int $dokumentId, ?UploadedFile $file = null): void
    {
        $prilozenDokument = new KandidatPrilozenaDokumenta;
        $prilozenDokument->prilozenaDokumenta_id = $dokumentId;
        $prilozenDokument->kandidat_id = $kandidatId;
        $prilozenDokument->indikatorAktivan = 1;

        if ($file !== null && $file->isValid()) {
            $pathData = $this->storeDocumentFile($kandidatId, $dokumentId, $file);
            $prilozenDokument->file_path = $pathData['path'];
            $prilozenDokument->file_name = $pathData['name'];
            $prilozenDokument->mime_type = $pathData['mime'];
            $prilozenDokument->file_size = $pathData['size'];
        }

        $prilozenDokument->save();
    }

    private function mergeSelectedWithUploaded(array $selectedIds, array $uploads): array
    {
        $selected = array_map('intval', array_values(array_filter($selectedIds, static fn ($id) => $id !== null && $id !== '')));
        $fromUploads = array_map('intval', array_keys(array_filter($uploads, static fn ($file) => $file instanceof UploadedFile)));

        return array_values(array_unique(array_merge($selected, $fromUploads)));
    }

    /**
     * @return array{path: string, name: string, mime: string|null, size: int|null}
     */
    private function storeDocumentFile(int $kandidatId, int $dokumentId, UploadedFile $file): array
    {
        $directory = "documents/{$kandidatId}";
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $filename = 'dokument_'.$dokumentId.'_'.time().'.'.$extension;

        Storage::disk('uploads')->putFileAs($directory, $file, $filename);

        return [
            'path' => $directory.'/'.$filename,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }
}
