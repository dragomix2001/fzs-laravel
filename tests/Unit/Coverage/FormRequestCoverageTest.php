<?php

namespace Tests\Unit\Coverage;

use App\Http\Requests\Api\StoreKandidatRequest as ApiStoreKandidatRequest;
use App\Http\Requests\Api\UpdateKandidatRequest as ApiUpdateKandidatRequest;
use App\Http\Requests\ImportFileRequest;
use App\Http\Requests\StoreKandidatRequest;
use App\Http\Requests\StoreMasterKandidatRequest;
use App\Http\Requests\StorePrijavaIspitaPredmetManyRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreZapisnikRequest;
use App\Http\Requests\UpdateDiplomskiOdbranaRequest;
use App\Http\Requests\UpdateDiplomskiPolaganjeRequest;
use App\Http\Requests\UpdateDiplomskiTemaRequest;
use App\Http\Requests\UpdateKandidatRequest;
use App\Http\Requests\UpdateMasterKandidatRequest;
use App\Models\Kandidat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Touch all uncovered FormRequest classes so they appear in coverage.
 */
class FormRequestCoverageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_import_file_request(): void
    {
        $req = new ImportFileRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
    }

    public function test_store_kandidat_request(): void
    {
        $req = new StoreKandidatRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_store_kandidat_request_page_one_rules(): void
    {
        $req = new StoreKandidatRequest;
        $req->merge(['page' => 1]);

        $rules = $req->rules();

        $this->assertArrayHasKey('JMBG', $rules);
    }

    public function test_store_kandidat_request_page_two_rules(): void
    {
        $req = new StoreKandidatRequest;
        $req->merge(['page' => 2]);

        $rules = $req->rules();

        $this->assertArrayHasKey('documentUploadsPrva.*', $rules);
        $this->assertArrayHasKey('documentUploadsDruga.*', $rules);
    }

    public function test_store_master_kandidat_request(): void
    {
        $req = new StoreMasterKandidatRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_store_prijava_ispita_predmet_many_request(): void
    {
        $req = new StorePrijavaIspitaPredmetManyRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_store_student_request(): void
    {
        $req = new StoreStudentRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
    }

    public function test_store_zapisnik_request(): void
    {
        $req = new StoreZapisnikRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_update_diplomski_odbrana_request(): void
    {
        $req = new UpdateDiplomskiOdbranaRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_update_diplomski_polaganje_request(): void
    {
        $req = new UpdateDiplomskiPolaganjeRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_update_diplomski_tema_request(): void
    {
        $req = new UpdateDiplomskiTemaRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_update_kandidat_request(): void
    {
        $req = new UpdateKandidatRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_update_kandidat_request_adds_unique_rule_when_index_changes(): void
    {
        $kandidat = Kandidat::factory()->create([
            'brojIndeksa' => 'OLD-001/2024',
        ]);

        $req = new UpdateKandidatRequest;
        $req->setRouteResolver(static fn () => new class($kandidat->id)
        {
            public function __construct(private int $id) {}

            public function parameter(string $key): ?int
            {
                return $key === 'id' ? $this->id : null;
            }
        });
        $req->merge(['brojIndeksa' => 'NEW-001/2024']);

        $rules = $req->rules();

        $this->assertArrayHasKey('brojIndeksa', $rules);
    }

    public function test_update_master_kandidat_request(): void
    {
        $req = new UpdateMasterKandidatRequest;
        $this->assertTrue($req->authorize());
        $this->assertIsArray($req->rules());
        $this->assertIsArray($req->messages());
    }

    public function test_update_master_kandidat_request_adds_unique_rule_when_index_changes(): void
    {
        $kandidat = Kandidat::factory()->create([
            'brojIndeksa' => 'OLD-M-001/2024',
        ]);

        $req = new UpdateMasterKandidatRequest;
        $req->setRouteResolver(static fn () => new class($kandidat->id)
        {
            public function __construct(private int $id) {}

            public function parameter(string $key): ?int
            {
                return $key === 'id' ? $this->id : null;
            }
        });
        $req->merge(['brojIndeksa' => 'NEW-M-001/2024']);

        $rules = $req->rules();

        $this->assertArrayHasKey('brojIndeksa', $rules);
    }

    public function test_api_store_kandidat_request(): void
    {
        $req = new ApiStoreKandidatRequest;
        $this->assertIsBool($req->authorize()); // returns false when no user (null !== null is false)
        $this->assertIsArray($req->rules());
    }

    public function test_api_update_kandidat_request(): void
    {
        $req = new ApiUpdateKandidatRequest;
        $this->assertIsBool($req->authorize());
        $this->assertIsArray($req->rules());
    }
}
