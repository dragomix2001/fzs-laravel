<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\CacheManagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheManagementServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CacheManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CacheManagementService::class);
    }

    public function test_get_active_studijski_program_from_cache_returns_active_program_id(): void
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $result = $this->service->getActiveStudijskiProgramFromCache($tipStudija->id);

        $this->assertSame($program->id, $result);
        $this->assertTrue(Cache::has("active_studijski_program_{$tipStudija->id}"));
    }

    public function test_get_active_studijski_program_from_cache_returns_cached_value_on_second_call(): void
    {
        $tipStudija = TipStudija::factory()->create();
        $firstProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $firstResult = $this->service->getActiveStudijskiProgramFromCache($tipStudija->id);

        $firstProgram->indikatorAktivan = 0;
        $firstProgram->save();

        $secondProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $secondResult = $this->service->getActiveStudijskiProgramFromCache($tipStudija->id);

        $this->assertSame($firstProgram->id, $firstResult);
        $this->assertSame($firstResult, $secondResult);
        $this->assertNotSame($secondProgram->id, $secondResult);
    }

    public function test_clear_active_studijski_program_cache_removes_key(): void
    {
        $tipStudija = TipStudija::factory()->create();
        StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->service->getActiveStudijskiProgramFromCache($tipStudija->id);
        $this->assertTrue(Cache::has("active_studijski_program_{$tipStudija->id}"));

        $this->service->clearActiveStudijskiProgramCache($tipStudija->id);

        $this->assertFalse(Cache::has("active_studijski_program_{$tipStudija->id}"));
    }

    public function test_refresh_active_studijski_program_cache_reloads_latest_value(): void
    {
        $tipStudija = TipStudija::factory()->create();
        $firstProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->service->getActiveStudijskiProgramFromCache($tipStudija->id);

        $firstProgram->indikatorAktivan = 0;
        $firstProgram->save();

        $secondProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $result = $this->service->refreshActiveStudijskiProgramCache($tipStudija->id);

        $this->assertSame($secondProgram->id, $result);
    }
}
