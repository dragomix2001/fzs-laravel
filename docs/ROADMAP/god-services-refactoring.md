# God Services Refactoring Roadmap

**Last Updated:** 2025-04-06  
**Current Quality:** 8.0/10  
**Target Quality:** 10.0/10  
**Progress:** ~60% complete (5/8 target services extracted)

---

## Quick Start for Next Session

### Current State
- **KandidatService:** 785 lines (originally 1026 lines)
- **Extracted Services (5):** FileStorage, GradeManagement, DropdownData, SportsManagement, DocumentManagement
- **Total Helper Code:** 644 lines
- **Test Coverage:** 111 unit tests, 100% coverage for all helper services

### What's Done (Waves 1 & 2)
✅ FileStorageService (Wave 1) - 136 lines, 27 tests  
✅ GradeManagementService (Wave 1) - 175 lines, 22 tests  
✅ DropdownDataService (Wave 2) - 172 lines, 14 tests  
✅ SportsManagementService (Wave 2) - 79 lines, 9 tests  
✅ DocumentManagementService (Wave 2) - 82 lines, 10 tests  

### Next Target: 9.0/10 Quality

**Wave 3: Mass Operations Extraction**
- Extract `masovniUpis()`, `masovnaUplata()`, `masovniUpisAsync()`
- Expected reduction: ~150 lines
- Target: KandidatService → ~600 lines
- Estimated effort: 15-20 hours

---

## Detailed Roadmap

### Phase 1: 9.0/10 Quality (Next Session)

#### Task 1: Extract MassOperationsService (~150 lines)

**Methods to extract:**
```php
// From KandidatService.php (lines to find and extract)
public function masovniUpis($selectedIds, $statusId)
public function masovnaUplata($selectedIds, $statusId)
public function masovniUpisAsync($selectedIds, $statusId)
```

**Service Structure:**
```php
class MassOperationsService
{
    public function __construct(
        protected KandidatService $kandidatService,
        protected UpisService $upisService
    ) {}

    public function massEnroll(array $kandidatIds, int $statusId): int
    public function massPayment(array $kandidatIds, int $statusId): int
    public function massEnrollAsync(array $kandidatIds, int $statusId): void
}
```

**Expected Impact:**
- KandidatService: 785 → ~635 lines (19% additional reduction)
- Total reduction from original: 1026 → 635 lines (38% decrease)

**Test Requirements:**
- Test mass enroll with 1 kandidat
- Test mass enroll with 10 kandidats
- Test mass payment success
- Test async dispatch (queue verification)
- Test transaction rollback on failure
- Estimated: 12-15 tests

**Acceptance Criteria:**
- [ ] MassOperationsService created with 3 public methods
- [ ] KandidatService reduced to ~635 lines
- [ ] Minimum 12 tests with 100% coverage
- [ ] All 123+ unit tests pass (111 existing + 12 new)
- [ ] CI/CD passes (Laravel CI/CD + CodeQL)
- [ ] ADR-001 updated with Wave 3 metrics

---

### Phase 2: 9.5/10 Quality

#### Task 2: Extract CacheManagementService (~50 lines)

**Methods to extract:**
```php
// Cache operations scattered in KandidatService
Cache::forget('active_programs')
Cache::remember('active_programs', ...)
```

**Service Structure:**
```php
class CacheManagementService
{
    public function clearActiveProgramsCache(): void
    public function getActiveProgramsFromCache(): Collection
    public function refreshActiveProgramsCache(): void
}
```

**Expected Impact:**
- KandidatService: 635 → ~585 lines (8% additional reduction)

**Test Requirements:**
- Test cache hit
- Test cache miss
- Test cache clear
- Test cache refresh
- Estimated: 6-8 tests

---

### Phase 3: 10.0/10 Quality

#### Task 3: Extract ValidationService (~80 lines)

**Methods to extract:**
```php
// Validation logic scattered in store/update methods
protected function validateKandidatData(array $data): array
protected function validateMasterKandidatData(array $data): array
```

**Service Structure:**
```php
class KandidatValidationService
{
    public function validateBasicKandidatData(array $data): array
    public function validateMasterKandidatData(array $data): array
    public function validateGradeData(array $data): array
}
```

**Expected Impact:**
- KandidatService: 585 → ~505 lines (14% additional reduction)
- **Total reduction from original: 1026 → 505 lines (51% decrease)**

---

## Test Coverage Improvement Roadmap

### Current Coverage Status
- **Overall Project Coverage:** 58.94% (4143/7029 elements)
- **Helper Services Coverage:** 100% (all 5 services fully tested)
- **KandidatService Coverage:** ~60% (partial coverage)
- **IspitService Coverage:** ~40% (low coverage)

### Coverage Target: 70% → 80% → 90%

#### Phase 1: 70% Coverage (Low Hanging Fruit)

**Priority 1: Test Untested Services**
1. **BackupService** - Currently 0% coverage
   - Create `BackupServiceTest.php`
   - Test backup creation, restoration, validation
   - Estimated: 8-10 tests
   - Impact: +2% coverage

2. **UpisService** - Currently ~30% coverage
   - Expand `UpisServiceTest.php`
   - Test enrollment logic, validation, edge cases
   - Estimated: 15-20 tests
   - Impact: +3% coverage

3. **Controllers** - Currently ~25% coverage
   - Add feature tests for critical endpoints
   - Focus on KandidatController, IspitController
   - Estimated: 20-30 tests
   - Impact: +5% coverage

**Total Impact:** 58.94% → ~69% coverage

---

#### Phase 2: 80% Coverage (Medium Priority)

**Priority 2: Increase KandidatService Coverage**
1. **KandidatService Edge Cases**
   - Test error handling paths
   - Test database transaction rollbacks
   - Test file upload failures
   - Estimated: 15-20 tests
   - Impact: +4% coverage

2. **IspitService Expansion**
   - Extract helper services (same pattern as KandidatService)
   - Write comprehensive tests for extracted services
   - Estimated: 30-40 tests
   - Impact: +6% coverage

**Total Impact:** 69% → ~79% coverage

---

#### Phase 3: 90% Coverage (High Priority)

**Priority 3: Models and Complex Logic**
1. **Model Tests**
   - Test Eloquent relationships
   - Test scopes and accessors
   - Test model events
   - Estimated: 40-50 tests
   - Impact: +5% coverage

2. **Integration Tests**
   - Test full kandidat creation flow
   - Test full enrollment process
   - Test exam grading workflow
   - Estimated: 20-25 tests
   - Impact: +3% coverage

3. **Edge Case Coverage**
   - Test error paths
   - Test validation failures
   - Test race conditions
   - Estimated: 15-20 tests
   - Impact: +3% coverage

**Total Impact:** 79% → ~90% coverage

---

## Session Checklists

### Next Session: Wave 3 (9.0 Quality Target)

**Before Starting:**
- [ ] Read `docs/ADR/001-god-services.md` (full context)
- [ ] Read `docs/ROADMAP/god-services-refactoring.md` (this file)
- [ ] Review current KandidatService.php (785 lines)
- [ ] Identify mass operations methods (masovniUpis, masovnaUplata, masovniUpisAsync)

**Execution Steps:**
1. [ ] Create `app/Services/MassOperationsService.php`
2. [ ] Extract 3 mass operation methods
3. [ ] Update KandidatService constructor to inject MassOperationsService
4. [ ] Refactor KandidatService to delegate mass operations
5. [ ] Create `tests/Unit/Services/MassOperationsServiceTest.php` (12-15 tests)
6. [ ] Run full test suite (verify zero regressions)
7. [ ] Update ADR-001 with Wave 3 metrics
8. [ ] Commit, push, verify CI/CD

**Success Criteria:**
- [ ] KandidatService reduced to ~635 lines
- [ ] MassOperationsService 100% test coverage
- [ ] All 123+ tests pass
- [ ] CI/CD green (Laravel + CodeQL)
- [ ] Code quality: 9.0/10

**Estimated Time:** 4-5 hours

---

### Future Session: Test Coverage 70% Target

**Before Starting:**
- [ ] Download latest coverage.xml from CI/CD
- [ ] Identify untested files with `vendor/bin/phpunit --coverage-text`
- [ ] Prioritize by impact (BackupService, UpisService, Controllers)

**Execution Steps:**
1. [ ] Create BackupServiceTest.php (8-10 tests)
2. [ ] Expand UpisServiceTest.php (15-20 tests)
3. [ ] Add KandidatController feature tests (10-15 tests)
4. [ ] Add IspitController feature tests (10-15 tests)
5. [ ] Run coverage report
6. [ ] Verify 70% threshold reached
7. [ ] Commit, push, download coverage.xml

**Success Criteria:**
- [ ] Overall coverage: 58.94% → 70%+
- [ ] BackupService: 0% → 100%
- [ ] UpisService: 30% → 80%+
- [ ] Controllers: 25% → 60%+
- [ ] CI/CD green

**Estimated Time:** 8-10 hours

---

## Key Files to Reference

### Documentation
- `docs/ADR/001-god-services.md` - Full refactoring history and metrics
- `docs/ROADMAP/god-services-refactoring.md` - This file (roadmap)

### Services (Current State)
- `app/Services/KandidatService.php` - 785 lines (main target)
- `app/Services/FileStorageService.php` - 136 lines (Wave 1)
- `app/Services/GradeManagementService.php` - 175 lines (Wave 1)
- `app/Services/DropdownDataService.php` - 172 lines (Wave 2)
- `app/Services/SportsManagementService.php` - 79 lines (Wave 2)
- `app/Services/DocumentManagementService.php` - 82 lines (Wave 2)

### Tests (Current State)
- `tests/Unit/Services/FileStorageServiceTest.php` - 27 tests
- `tests/Unit/Services/GradeManagementServiceTest.php` - 22 tests
- `tests/Unit/Services/SportsManagementServiceTest.php` - 9 tests
- `tests/Unit/Services/DocumentManagementServiceTest.php` - 10 tests
- `tests/Unit/Services/DropdownDataServiceTest.php` - 14 tests

### CI/CD
- `.github/workflows/laravel.yml` - Laravel CI/CD workflow
- `.github/workflows/codeql.yml` - CodeQL security analysis
- Download coverage: `gh run download <run-id> -n coverage-report`

---

## Quick Commands

### Check Current State
```bash
# Line count
wc -l app/Services/KandidatService.php

# Test count
vendor/bin/phpunit --testsuite Unit --list-tests | wc -l

# Coverage
vendor/bin/phpunit --coverage-text

# CI/CD status
gh run list --limit 3
```

### Start Wave 3
```bash
# Create service
touch app/Services/MassOperationsService.php

# Create test
touch tests/Unit/Services/MassOperationsServiceTest.php

# Run tests
vendor/bin/phpunit tests/Unit/Services/MassOperationsServiceTest.php
```

### Verify Quality
```bash
# Pint
vendor/bin/pint --test

# PHPStan
vendor/bin/phpstan analyse

# Full test suite
vendor/bin/phpunit --testsuite Unit --stop-on-failure
```

---

## Progress Tracking

### Quality Milestones
- [x] 7.0/10 - Initial state (KandidatService 1026 lines, 0 helper services)
- [x] 7.5/10 - Wave 1 complete (FileStorage + GradeManagement extracted)
- [x] 8.0/10 - Wave 2 complete (Dropdown + Sports + Documents extracted)
- [ ] 9.0/10 - Wave 3 target (MassOperations extracted, ~635 lines)
- [ ] 9.5/10 - Wave 4 target (CacheManagement extracted, ~585 lines)
- [ ] 10.0/10 - Wave 5 target (Validation extracted, ~505 lines)

### Coverage Milestones
- [x] 58.94% - Current coverage (2025-04-06)
- [ ] 70% - Phase 1 target (BackupService + UpisService + Controllers)
- [ ] 80% - Phase 2 target (KandidatService edge cases + IspitService extraction)
- [ ] 90% - Phase 3 target (Models + Integration tests + Edge cases)

---

## Notes for Future Sessions

### Lessons Learned (Waves 1 & 2)
1. **Always run Pint after creating tests** - `declare(strict_types=1)` requires fully qualified class names
2. **Use `Model::unguard()` in test setUp()** - Avoids mass assignment exceptions
3. **Extract service dependencies early** - DropdownDataService needs GradeManagementService
4. **Write 100% coverage for helpers** - Makes bugs visible immediately
5. **Document extraction in ADR** - Critical for context in future sessions

### Common Pitfalls to Avoid
- ❌ Don't use `Collection::class` with `declare(strict_types=1)` - use `\Illuminate\Support\Collection::class`
- ❌ Don't forget to update KandidatService constructor when adding new service
- ❌ Don't skip manual code review - automated tests don't catch everything
- ❌ Don't commit without verifying CI/CD passes
- ❌ Don't extract too much in one session - keep PRs focused (3-4 hours max)

### Repository Info
- **URL:** https://github.com/dragomix2001/fzs-laravel.git
- **Branch:** master
- **Password:** nautilus142
- **Workflow:** All work in WSL Linux, not Windows

---

## Session History

### 2025-04-05: Wave 1 (7.0 → 7.5)
- Extracted FileStorageService (136 lines)
- Extracted GradeManagementService (175 lines)
- Fixed 3 critical bugs during extraction
- KandidatService: 1026 → 904 lines
- Added 49 new tests
- Duration: ~6 hours

### 2025-04-06: Wave 2 (7.5 → 8.0)
- Extracted DropdownDataService (172 lines)
- Extracted SportsManagementService (79 lines)
- Extracted DocumentManagementService (82 lines)
- KandidatService: 904 → 785 lines
- Added 33 new tests
- Duration: ~5 hours
- CI/CD: All green ✅

---

**Ready for next session! Start with this roadmap and ADR-001 for full context.**
