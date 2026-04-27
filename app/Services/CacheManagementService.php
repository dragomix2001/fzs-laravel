<?php

namespace App\Services;

use App\Models\StudijskiProgram;
use Illuminate\Support\Facades\Cache;

/**
 * Cache management helper for active study program lookups.
 */
class CacheManagementService
{
    /**
     * Get active study program id for a study type from cache (or DB on miss).
     */
    public function getActiveStudijskiProgramFromCache(int $tipStudijaId): ?int
    {
        return Cache::remember($this->cacheKey($tipStudijaId), 3600, function () use ($tipStudijaId) {
            return StudijskiProgram::where([
                'tipStudija_id' => $tipStudijaId,
                'indikatorAktivan' => 1,
            ])->value('id');
        });
    }

    /**
     * Remove cached active study program for a study type.
     */
    public function clearActiveStudijskiProgramCache(int $tipStudijaId): void
    {
        Cache::forget($this->cacheKey($tipStudijaId));
    }

    /**
     * Recompute and return active study program id after cache clear.
     */
    public function refreshActiveStudijskiProgramCache(int $tipStudijaId): ?int
    {
        $this->clearActiveStudijskiProgramCache($tipStudijaId);

        return $this->getActiveStudijskiProgramFromCache($tipStudijaId);
    }

    private function cacheKey(int $tipStudijaId): string
    {
        return "active_studijski_program_{$tipStudijaId}";
    }
}
