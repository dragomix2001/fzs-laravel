<?php

namespace App\Services;

use App\Models\Kandidat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * File Storage Service - Centralized file handling for kandidat documents.
 *
 * This is a helper service extracted from KandidatService to centralize all file storage
 * operations (image and PDF upload/update/delete). Improves testability and maintainability.
 *
 * **CRITICAL BUG FIX**: Fixes PDF filename concatenation bug from KandidatService where
 * `diplomski{ID}pdf` was being generated instead of `diplomski{ID}.pdf` (missing dot).
 *
 * Storage Configuration:
 * - Disk: 'uploads' (defined in config/filesystems.php)
 * - Image directory: 'images/'
 * - PDF directory: 'pdf/'
 *
 * @see KandidatService (original implementation with bug)
 * @see Kandidat (fields: slika, diplomski)
 */
class FileStorageService
{
    /**
     * Replace kandidat image with new file.
     *
     * @param  Kandidat  $kandidat  The kandidat whose image should be replaced
     * @param  UploadedFile  $file  The uploaded image file
     * @return string The stored filename (e.g., 'slika123.jpg')
     *
     * @throws \InvalidArgumentException If file is not a valid image
     */
    public function replaceImageForKandidat(Kandidat $kandidat, UploadedFile $file): string
    {
        if (!$file->isValid() || substr($file->getMimeType(), 0, 5) !== 'image') {
            throw new \InvalidArgumentException('Uploaded file is not a valid image.');
        }

        $extension = $file->getClientOriginalExtension();
        $imageName = 'slika'.$kandidat->id;
        $filename = $imageName.'.'.$extension;

        $oldImages = collect(Storage::disk('uploads')->files('images'))
            ->filter(fn ($f) => str_starts_with(basename($f), $imageName.'.'));

        foreach ($oldImages as $old) {
            Storage::disk('uploads')->delete($old);
        }

        Storage::disk('uploads')->putFileAs('images', $file, $filename);

        $kandidat->slika = $filename;
        $kandidat->save();

        return $filename;
    }

    /**
     * Upload image for new kandidat.
     *
     * @param  Kandidat  $kandidat  The new kandidat
     * @param  UploadedFile  $file  The uploaded image file
     * @return string The stored filename
     *
     * @throws \InvalidArgumentException If file is not a valid image
     */
    public function uploadImageForKandidat(Kandidat $kandidat, UploadedFile $file): string
    {
        if (!$file->isValid() || substr($file->getMimeType(), 0, 5) !== 'image') {
            throw new \InvalidArgumentException('Uploaded file is not a valid image.');
        }

        $filename = 'slika'.$kandidat->id.'.'.$file->getClientOriginalExtension();

        Storage::disk('uploads')->putFileAs('images', $file, $filename);

        $kandidat->slika = $filename;
        $kandidat->save();

        return $filename;
    }

    /**
     * Replace kandidat PDF with new file.
     *
     * @param  Kandidat  $kandidat  The kandidat whose PDF should be replaced
     * @param  UploadedFile  $file  The uploaded PDF file
     * @return string The stored filename (e.g., 'diplomski123.pdf')
     *
     * @throws \InvalidArgumentException If file is not a valid PDF
     */
    public function replacePdfForKandidat(Kandidat $kandidat, UploadedFile $file): string
    {
        if (!$file->isValid() || $file->getMimeType() !== 'application/pdf') {
            throw new \InvalidArgumentException('Uploaded file is not a valid PDF.');
        }

        $extension = $file->getClientOriginalExtension();
        $pdfName = 'diplomski'.$kandidat->id;
        $filename = $pdfName.'.'.$extension;

        $oldPdfs = collect(Storage::disk('uploads')->files('pdf'))
            ->filter(fn ($f) => str_starts_with(basename($f), $pdfName.'.'));

        foreach ($oldPdfs as $old) {
            Storage::disk('uploads')->delete($old);
        }

        Storage::disk('uploads')->putFileAs('pdf', $file, $filename);

        $kandidat->diplomski = $filename;
        $kandidat->save();

        return $filename;
    }

    /**
     * Delete kandidat image from storage.
     *
     * @param  Kandidat  $kandidat  The kandidat whose image should be deleted
     * @return bool True if image was deleted, false if it didn't exist
     */
    public function deleteImageForKandidat(Kandidat $kandidat): bool
    {
        if (empty($kandidat->slika) || !Storage::disk('uploads')->exists("images/{$kandidat->slika}")) {
            return false;
        }

        Storage::disk('uploads')->delete("images/{$kandidat->slika}");
        return true;
    }
}
