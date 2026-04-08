# ADR-003: Direct Facade Usage (Cache, Storage, DB)

**Status:** Accepted (technical debt)

**Date:** 2024-01-XX

**Decision Makers:** Development Team

---

## Context

Services directly use Laravel Facades (`Cache::`, `Storage::`, `DB::`) instead of dependency injection with interfaces.

**Example from KandidatService:**
```php
public function getActiveStudijskiProgramOsnovne(): ?int
{
    return Cache::remember('active_studijski_program_osnovne', 3600, function () {
        return StudijskiProgram::where([...])->value('id');
    });
}

public function handleNewImageUpload(Kandidat $kandidat, UploadedFile $file): void
{
    Storage::disk('uploads')->putFileAs('images', $file, $imageName);
}

public function deleteKandidat(int $id)
{
    return DB::transaction(function () use ($id) {
        // ... delete operations
    });
}
```

**Problems:**
1. **Testing complexity**: Must mock Facades in tests (`Cache::shouldReceive(...)`)
2. **Tight coupling**: Cannot swap implementations (e.g., Redis → Memcached requires code changes)
3. **Framework lock-in**: Cannot migrate to another framework without major refactor

---

## Decision

**We accept Facade usage as technical debt** and will NOT refactor immediately.

**Reasons:**
1. **Laravel standard**: Facades are idiomatic Laravel (most Laravel apps use them)
2. **High refactor cost**: Creating interfaces + implementations = 20-25 hours per service
3. **Low migration risk**: No plans to migrate away from Laravel
4. **Testing works**: Facade mocking is well-supported in Laravel

---

## Consequences

### Negative
- **Test setup overhead**: Every test must mock 5-10 facade calls
- **Brittle tests**: Tests break when facade call order changes
- **Hidden dependencies**: Hard to see what a service depends on (facades are global)

### Positive
- **Idiomatic Laravel**: Familiar to Laravel developers
- **Less boilerplate**: No interface/implementation separation needed
- **Well-documented**: Laravel facade mocking is standard practice

---

## Mitigation Strategy

**Current approach: Accept and document.**

If refactor becomes necessary:

1. Create interfaces:
```php
interface CacheInterface {
    public function remember(string $key, int $ttl, Closure $callback): mixed;
}

class LaravelCacheAdapter implements CacheInterface {
    public function remember(string $key, int $ttl, Closure $callback): mixed {
        return Cache::remember($key, $ttl, $callback);
    }
}
```

2. Inject via constructor:
```php
class KandidatService {
    public function __construct(
        protected UpisService $upisService,
        protected CacheInterface $cache,
        protected FileStorageInterface $storage,
        protected DatabaseInterface $db
    ) {}
}
```

3. Bind in service provider:
```php
$this->app->bind(CacheInterface::class, LaravelCacheAdapter::class);
```

---

## Future

**No immediate action planned.**

**Trigger for refactor:**
- Migration to different framework (unlikely)
- Swapping Cache/Storage backends frequently (not current need)
- Test suite becomes unmaintainable due to facade mocking (not yet an issue)

**Estimated effort if needed:** 160-200 hours (8-10 services × 20-25 hours)

---

## Update (2026-04-08): Decision Still Valid, Scope Reduced in Core Flows

**Status:** Unchanged (Accepted)

The core decision remains valid: full facade abstraction is still not justified by current cost/benefit.

Recent refactoring reduced the practical impact in one important way:

- File/storage concerns were extracted from `KandidatService` into dedicated helper services.
- `KandidatService` still uses selected facades (`Cache`, `DB`) for orchestration and transactional boundaries.

### Current Guidance

1. Do not introduce broad adapter layers unless a concrete migration/use-case appears.
2. Keep facade usage localized and test-covered in small services where possible.
3. Prioritize decomposition and DTO boundaries before interface-driven facade abstraction.
