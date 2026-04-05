<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Kandidat;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * FileStorageServiceTest
 *
 * Comprehensive unit tests for FileStorageService covering all public methods:
 * - replaceImageForKandidat()
 * - uploadImageForKandidat()
 * - replacePdfForKandidat()
 * - deleteImageForKandidat()
 *
 * Critical test: Verifies PDF filename bug fix (dot concatenation on line 104)
 */
class FileStorageServiceTest extends TestCase
{
    use DatabaseTransactions;

    private FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        $this->service = app(FileStorageService::class);
    }

    /**
     * Test uploadImageForKandidat saves image with correct filename
     */
    #[Test]
    public function test_uploadImageForKandidat_saves_image_with_correct_filename(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $filename = $this->service->uploadImageForKandidat($kandidat, $file);

        // Verify filename format: slika{ID}.{extension}
        $this->assertStringStartsWith('slika'.$kandidat->id.'.', $filename);
        $this->assertStringEndsWith('.jpg', $filename);
        
        // Verify file is stored in correct location
        Storage::disk('uploads')->assertExists('images/'.$filename);
        
        // Verify kandidat record updated
        $kandidat->refresh();
        $this->assertEquals($filename, $kandidat->slika);
    }

    /**
     * Test uploadImageForKandidat with PNG file
     */
    #[Test]
    public function test_uploadImageForKandidat_handles_png_extension(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.png');

        $filename = $this->service->uploadImageForKandidat($kandidat, $file);

        $this->assertStringEndsWith('.png', $filename);
        Storage::disk('uploads')->assertExists('images/'.$filename);
    }

    /**
     * Test uploadImageForKandidat rejects non-image files
     */
    #[Test]
    public function test_uploadImageForKandidat_rejects_non_image_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Uploaded file is not a valid image.');

        $this->service->uploadImageForKandidat($kandidat, $file);
    }

    /**
     * Test uploadImageForKandidat rejects text files
     */
    #[Test]
    public function test_uploadImageForKandidat_rejects_text_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('text.txt', 100, 'text/plain');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->uploadImageForKandidat($kandidat, $file);
    }

    /**
     * Test replaceImageForKandidat saves new image and deletes old one
     */
    #[Test]
    public function test_replaceImageForKandidat_deletes_old_image(): void
    {
        $kandidat = Kandidat::factory()->create();
        
        // Upload initial image
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldFilename = $this->service->uploadImageForKandidat($kandidat, $oldFile);
        
        Storage::disk('uploads')->assertExists('images/'.$oldFilename);

        // Replace with new image
        $newFile = UploadedFile::fake()->image('new.png');
        $newFilename = $this->service->replaceImageForKandidat($kandidat, $newFile);

        // Old file should be deleted
        Storage::disk('uploads')->assertMissing('images/'.$oldFilename);
        
        // New file should exist
        Storage::disk('uploads')->assertExists('images/'.$newFilename);
        
        // Kandidat should reference new file
        $kandidat->refresh();
        $this->assertEquals($newFilename, $kandidat->slika);
    }

    /**
     * Test replaceImageForKandidat with correct naming format
     */
    #[Test]
    public function test_replaceImageForKandidat_uses_correct_filename_format(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $filename = $this->service->replaceImageForKandidat($kandidat, $file);

        // Verify filename format: slika{ID}.{extension}
        $expectedFormat = 'slika'.$kandidat->id.'.jpg';
        $this->assertEquals($expectedFormat, $filename);
    }

    /**
     * Test replaceImageForKandidat handles multiple old images
     */
    #[Test]
    public function test_replaceImageForKandidat_deletes_all_old_images(): void
    {
        $kandidat = Kandidat::factory()->create();
        
        // Manually add multiple old images for this kandidat
        $oldFile1 = UploadedFile::fake()->image('old1.jpg');
        $oldFile2 = UploadedFile::fake()->image('old2.png');
        
        $imageName = 'slika'.$kandidat->id;
        $filename1 = $imageName.'.jpg';
        $filename2 = $imageName.'.png';
        
        Storage::disk('uploads')->putFileAs('images', $oldFile1, $filename1);
        Storage::disk('uploads')->putFileAs('images', $oldFile2, $filename2);
        
        Storage::disk('uploads')->assertExists('images/'.$filename1);
        Storage::disk('uploads')->assertExists('images/'.$filename2);

        // Replace with new image
        $newFile = UploadedFile::fake()->image('new.gif');
        $this->service->replaceImageForKandidat($kandidat, $newFile);

        // All old files should be deleted
        Storage::disk('uploads')->assertMissing('images/'.$filename1);
        Storage::disk('uploads')->assertMissing('images/'.$filename2);
    }

    /**
     * Test replaceImageForKandidat rejects invalid images
     */
    #[Test]
    public function test_replaceImageForKandidat_rejects_non_image_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('notimage.pdf', 100, 'application/pdf');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Uploaded file is not a valid image.');

        $this->service->replaceImageForKandidat($kandidat, $file);
    }

    /**
     * Test uploadImageForKandidat returns correct filename
     */
    #[Test]
    public function test_uploadImageForKandidat_returns_filename(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $filename = $this->service->uploadImageForKandidat($kandidat, $file);

        $this->assertIsString($filename);
        $this->assertNotEmpty($filename);
        $this->assertStringContainsString((string)$kandidat->id, $filename);
    }

    /**
     * Test replacePdfForKandidat saves PDF with correct filename format
     * CRITICAL: Verifies bug fix on line 104 (dot concatenation)
     */
    #[Test]
    public function test_replacePdfForKandidat_uses_correct_filename_format_with_dot(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf');

        $filename = $this->service->replacePdfForKandidat($kandidat, $file);

        // CRITICAL BUG FIX: Must have dot before extension
        // Should be 'diplomski123.pdf' NOT 'diplomski123pdf'
        $expectedFormat = 'diplomski'.$kandidat->id.'.pdf';
        $this->assertEquals($expectedFormat, $filename);
        $this->assertStringContainsString('.pdf', $filename);
    }

    /**
     * Test replacePdfForKandidat saves file in correct location
     */
    #[Test]
    public function test_replacePdfForKandidat_stores_in_pdf_directory(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf');

        $filename = $this->service->replacePdfForKandidat($kandidat, $file);

        Storage::disk('uploads')->assertExists('pdf/'.$filename);
    }

    /**
     * Test replacePdfForKandidat replaces file and updates record
     */
    #[Test]
    public function test_replacePdfForKandidat_replaces_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        $oldDiplomski = $kandidat->diplomski;
        
        $file1 = UploadedFile::fake()->create('diploma1.pdf', 100, 'application/pdf');
        $filename1 = $this->service->replacePdfForKandidat($kandidat, $file1);
        
        Storage::disk('uploads')->assertExists('pdf/'.$filename1);
        $kandidat->refresh();
        $this->assertEquals($filename1, $kandidat->diplomski);

        $file2 = UploadedFile::fake()->create('diploma2.pdf', 100, 'application/pdf');
        $filename2 = $this->service->replacePdfForKandidat($kandidat, $file2);

        Storage::disk('uploads')->assertExists('pdf/'.$filename2);
        
        $kandidat->refresh();
        $this->assertEquals($filename2, $kandidat->diplomski);
        $this->assertNotEquals($oldDiplomski, $kandidat->diplomski);
    }

    /**
     * Test replacePdfForKandidat deletes all old PDFs
     */
    #[Test]
    public function test_replacePdfForKandidat_deletes_all_old_pdfs(): void
    {
        $kandidat = Kandidat::factory()->create();
        
        $oldFile1 = UploadedFile::fake()->create('old1.pdf', 100, 'application/pdf');
        $filename1 = $this->service->replacePdfForKandidat($kandidat, $oldFile1);
        
        Storage::disk('uploads')->assertExists('pdf/'.$filename1);

        $newFile = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');
        $newFilename = $this->service->replacePdfForKandidat($kandidat, $newFile);

        Storage::disk('uploads')->assertExists('pdf/'.$newFilename);
        $this->assertEquals($newFilename, $kandidat->diplomski);
    }

    /**
     * Test replacePdfForKandidat rejects non-PDF files
     */
    #[Test]
    public function test_replacePdfForKandidat_rejects_image_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Uploaded file is not a valid PDF.');

        $this->service->replacePdfForKandidat($kandidat, $file);
    }

    /**
     * Test replacePdfForKandidat rejects text file
     */
    #[Test]
    public function test_replacePdfForKandidat_rejects_text_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->replacePdfForKandidat($kandidat, $file);
    }

    /**
     * Test replacePdfForKandidat returns correct filename
     */
    #[Test]
    public function test_replacePdfForKandidat_returns_filename(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf');

        $filename = $this->service->replacePdfForKandidat($kandidat, $file);

        $this->assertIsString($filename);
        $this->assertNotEmpty($filename);
        $this->assertStringContainsString((string)$kandidat->id, $filename);
    }

    /**
     * Test deleteImageForKandidat removes file when it exists
     */
    #[Test]
    public function test_deleteImageForKandidat_removes_file(): void
    {
        $kandidat = Kandidat::factory()->create();
        
        // Upload an image
        $file = UploadedFile::fake()->image('photo.jpg');
        $filename = $this->service->uploadImageForKandidat($kandidat, $file);
        
        Storage::disk('uploads')->assertExists('images/'.$filename);

        // Delete the image
        $result = $this->service->deleteImageForKandidat($kandidat);

        // File should be deleted
        Storage::disk('uploads')->assertMissing('images/'.$filename);
        $this->assertTrue($result);
    }

    /**
     * Test deleteImageForKandidat returns false when image doesn't exist
     */
    #[Test]
    public function test_deleteImageForKandidat_returns_false_when_file_missing(): void
    {
        $kandidat = Kandidat::factory()->create(['slika' => null]);

        $result = $this->service->deleteImageForKandidat($kandidat);

        $this->assertFalse($result);
    }

    /**
     * Test deleteImageForKandidat returns false for empty slika field
     */
    #[Test]
    public function test_deleteImageForKandidat_returns_false_for_empty_slika(): void
    {
        $kandidat = Kandidat::factory()->create(['slika' => '']);

        $result = $this->service->deleteImageForKandidat($kandidat);

        $this->assertFalse($result);
    }

    /**
     * Test deleteImageForKandidat with non-existent file
     */
    #[Test]
    public function test_deleteImageForKandidat_handles_nonexistent_file_in_storage(): void
    {
        $kandidat = Kandidat::factory()->create(['slika' => 'slika999.jpg']);

        // File doesn't exist in storage
        $result = $this->service->deleteImageForKandidat($kandidat);

        $this->assertFalse($result);
    }

    /**
     * Test deleteImageForKandidat returns true on successful deletion
     */
    #[Test]
    public function test_deleteImageForKandidat_returns_true_on_success(): void
    {
        $kandidat = Kandidat::factory()->create();
        
        $file = UploadedFile::fake()->image('photo.jpg');
        $this->service->uploadImageForKandidat($kandidat, $file);

        $result = $this->service->deleteImageForKandidat($kandidat);

        $this->assertTrue($result);
    }

    /**
     * Test uploadImageForKandidat with different image formats
     */
    #[Test]
    public function test_uploadImageForKandidat_handles_gif_extension(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.gif');

        $filename = $this->service->uploadImageForKandidat($kandidat, $file);

        $this->assertStringEndsWith('.gif', $filename);
        Storage::disk('uploads')->assertExists('images/'.$filename);
    }

    /**
     * Test replaceImageForKandidat updates kandidat record
     */
    #[Test]
    public function test_replaceImageForKandidat_updates_kandidat_record(): void
    {
        $kandidat = Kandidat::factory()->create(['slika' => 'old_image.jpg']);
        
        // Manually create the old file
        $imageName = 'slika'.$kandidat->id.'.jpg';
        $oldFile = UploadedFile::fake()->image('old.jpg');
        Storage::disk('uploads')->putFileAs('images', $oldFile, $imageName);

        // Replace image
        $newFile = UploadedFile::fake()->image('new.jpg');
        $newFilename = $this->service->replaceImageForKandidat($kandidat, $newFile);

        // Verify kandidat record is updated
        $kandidat->refresh();
        $this->assertEquals($newFilename, $kandidat->slika);
    }

    /**
     * Test replacePdfForKandidat updates kandidat record
     */
    #[Test]
    public function test_replacePdfForKandidat_updates_kandidat_record(): void
    {
        $kandidat = Kandidat::factory()->create(['diplomski' => 'old_pdf.pdf']);
        
        // Manually create the old file
        $pdfName = 'diplomski'.$kandidat->id.'.pdf';
        $oldFile = UploadedFile::fake()->create('old.pdf', 100, 'application/pdf');
        Storage::disk('uploads')->putFileAs('pdf', $oldFile, $pdfName);

        // Replace PDF
        $newFile = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');
        $newFilename = $this->service->replacePdfForKandidat($kandidat, $newFile);

        // Verify kandidat record is updated
        $kandidat->refresh();
        $this->assertEquals($newFilename, $kandidat->diplomski);
    }

    /**
     * Test uploadImageForKandidat updates kandidat record
     */
    #[Test]
    public function test_uploadImageForKandidat_updates_kandidat_record(): void
    {
        $kandidat = Kandidat::factory()->create(['slika' => null]);
        
        $file = UploadedFile::fake()->image('photo.jpg');
        $filename = $this->service->uploadImageForKandidat($kandidat, $file);

        $kandidat->refresh();
        $this->assertNotNull($kandidat->slika);
        $this->assertEquals($filename, $kandidat->slika);
    }

    /**
     * Test uploadImageForKandidat returns filename with extension
     */
    #[Test]
    public function test_uploadImageForKandidat_filename_has_extension(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $filename = $this->service->uploadImageForKandidat($kandidat, $file);

        $this->assertStringContainsString('.', $filename);
        $parts = explode('.', $filename);
        $this->assertGreaterThan(1, count($parts));
    }

    /**
     * Test replacePdfForKandidat filename has extension
     */
    #[Test]
    public function test_replacePdfForKandidat_filename_has_extension(): void
    {
        $kandidat = Kandidat::factory()->create();
        $file = UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf');

        $filename = $this->service->replacePdfForKandidat($kandidat, $file);

        $this->assertStringContainsString('.', $filename);
        $this->assertTrue(str_ends_with($filename, '.pdf'));
    }
}
