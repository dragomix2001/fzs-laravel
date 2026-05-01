<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\DTOs\KandidatPage2Data;
use App\DTOs\KandidatUpdateData;
use App\DTOs\MasterKandidatData;
use App\Models\PolozeniIspiti;
use App\Models\User;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Policies\IspitPolicy;
use App\Policies\PolozeniIspitiPolicy;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class DtoAndPolicyMethodTouchTest extends TestCase
{
    #[Test]
    public function private_uploaded_documents_normalizers_are_explicitly_executed(): void
    {
        $files = [
            '1' => UploadedFile::fake()->create('a.pdf', 1, 'application/pdf'),
            'x' => 'invalid',
            '2' => UploadedFile::fake()->create('b.pdf', 1, 'application/pdf'),
        ];

        foreach ([KandidatPage2Data::class, KandidatUpdateData::class, MasterKandidatData::class] as $className) {
            $method = new ReflectionMethod($className, 'normalizeUploadedDocuments');
            $method->setAccessible(true);
            $normalized = $method->invoke(null, $files);

            $this->assertArrayHasKey(1, $normalized);
            $this->assertArrayHasKey(2, $normalized);
            $this->assertArrayNotHasKey('x', $normalized);
        }
    }

    #[Test]
    public function policy_methods_are_directly_touched_with_admin_path(): void
    {
        $admin = new User(['role' => 'admin']);

        $ispitPolicy = new IspitPolicy;
        $zapisnik = new ZapisnikOPolaganjuIspita(['profesor_id' => 1]);

        $this->assertTrue($ispitPolicy->viewAny($admin));
        $this->assertTrue($ispitPolicy->view($admin, $zapisnik));
        $this->assertTrue($ispitPolicy->create($admin));
        $this->assertTrue($ispitPolicy->update($admin, $zapisnik));
        $this->assertTrue($ispitPolicy->delete($admin, $zapisnik));
        $this->assertTrue($ispitPolicy->arhiviraj($admin, $zapisnik));

        $polozeniPolicy = new PolozeniIspitiPolicy;
        $polozeni = new PolozeniIspiti(['kandidat_id' => 1]);

        $this->assertTrue($polozeniPolicy->viewAny($admin));
        $this->assertTrue($polozeniPolicy->view($admin, $polozeni));
        $this->assertTrue($polozeniPolicy->create($admin));
        $this->assertTrue($polozeniPolicy->update($admin, $polozeni));
        $this->assertTrue($polozeniPolicy->delete($admin, $polozeni));
    }
}
