# God Services Refactoring Roadmap

**Last Updated:** 2026-04-25  
**Current Quality:** 9.0/10  
**Target Quality:** 10.0/10  
**Progress:** ~80% complete; the first Wave 3 `IspitService` extraction is done and the next slice is pregled/result orchestration

---

## Quick Start for Next Session

### Current State
- **KandidatService:** 670 lines (originally 1026 lines)
- **IspitService:** 545 lines (originally 818 lines, after `IspitPdfService` and `IspitZapisnikService` extractions)
- **PrijavaController:** 280 lines (originally 731, PrijavaService extracted)
- **StudentListService:** 323 lines (originally 408, DRY refactor with BasePdfService)
- **Extracted Services (5 from KandidatService):** FileStorage, GradeManagement, DropdownData, SportsManagement, DocumentManagement
- **Additional Services:** PrijavaService (849), IspitPdfService (222), IspitZapisnikService (134), BasePdfService (53), KandidatEnrollmentService (132)
- **Total Helper Code:** 718 lines (KandidatService helper services only, excluding KandidatEnrollmentService)
- **Test Coverage:** 1378 tests, 3426 assertions, 0 errors
- **PHPStan:** Level 5, 0 errors, empty baseline
- **FormRequest classes:** 31 total
- **Document workflow:** Per-document uploads are live, and admin review routes/views are active in the application

### What's Done (Waves 1 & 2 + Priority Improvements)
✅ FileStorageService (Wave 1) - 136 lines, 27 tests  
✅ GradeManagementService (Wave 1) - 172 lines, 22 tests  
✅ DropdownDataService (Wave 2) - 192 lines, 14 tests  
✅ SportsManagementService (Wave 2) - 79 lines, 9 tests  
✅ DocumentManagementService (Wave 2) - now 138 lines, 15 tests, extended for per-document uploads and metadata  
✅ KandidatEnrollmentService - 132 lines, enrollment and batch kandidat operations extracted  
✅ DocumentReviewService + admin review UI/routes - attachment approval, rejection, revision requests, and completion tracking  
✅ PrijavaService (Priority) - 849 lines, extracted from PrijavaController (731→280)  
✅ IspitPdfService (Priority) - 222 lines, extracted from IspitService  
✅ IspitZapisnikService (Wave 3 slice 1) - 134 lines, extracted for zapisnik listing/create/archive flows with 10 focused tests  
✅ BasePdfService DRY refactor - 53 lines, StudentListService 408→323  
✅ PHPStan baseline eliminated - 40→0 errors  
✅ 9 new FormRequest classes added (22→31 total)  
✅ 23 smoke tests replaced with real assertions (+22 assertions)

### Next Target: 9.5/10 Quality

**Wave 3: IspitService Decomposition**
- Extract pregled/result orchestration from `IspitService`
- Decide whether student membership changes stay in the orchestrator or move into a dedicated flow service
- Keep PDF/report generation behind dedicated helper services where it clarifies responsibilities
- Estimated effort: 15-20 hours

---

## Detailed Roadmap

### Phase 1: 9.5/10 Quality (Next Session)

#### Task 1: Continue IspitService Decomposition (~100-150 lines extracted)

**Methods/areas to extract:**
```php
// Wave 3 slice 1 is already done in IspitZapisnikService.
// Next candidates inside IspitService.php:
// - getZapisnikPregled()
// - savePolozeniIspiti()
// - updateZapisnikDetails()
// - add/remove student membership orchestration
```

**Service Structure:**
```php
class IspitResultService
{
   public function getZapisnikPregled(int $zapisnikId): array
   public function savePolozeniIspiti(array $ispitIds, array $ocenePismeni, array $oceneUsmeni, array $konacneOcene, array $brojBodova, array $statusIspita): int
}
```

**Expected Impact:**
- IspitService: 545 → ~420-460 lines
- Clearer split between query/list responsibilities and report/PDF logic

**Test Requirements:**
- Test pregled data assembly for mixed study-program records
- Test result persistence for multiple students
- Test edge cases around missing predmet-program matches
- Test detail update flow that remains with the result/pregled slice
- Estimated: 12-15 tests

**Acceptance Criteria:**
- [x] `IspitZapisnikService` extracted for listing/create/archive concerns
- [ ] Next Ispit helper service extracted around pregled/result workflow
- [ ] IspitService reduced materially again without moving unrelated logic arbitrarily
- [ ] Minimum 12 tests with 100% coverage
- [ ] All 1378+ tests pass
- [ ] CI/CD passes (Laravel CI/CD + CodeQL)
- [ ] ADR-001 updated with the new extraction metrics

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
- KandidatService: 670 → ~620 lines (incremental reduction without touching already-extracted enrollment flow)

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
- KandidatService: 620 → ~540 lines (another focused reduction after cache extraction)
- **Total reduction from original: 1026 → ~540 lines (about 47% decrease)**

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

### Next Session: Wave 3 (9.5 Quality Target)

**Before Starting:**
- [ ] Read `docs/ADR/001-god-services.md` (full context)
- [ ] Read `docs/ROADMAP/god-services-refactoring.md` (this file)
- [ ] Review current IspitService.php (545 lines)
- [ ] Identify the next cohesive pregled/result slice before extracting anything

**Execution Steps:**
1. [ ] Create a focused Ispit helper service around pregled/result orchestration
2. [ ] Extract only one coherent workflow slice from IspitService
3. [ ] Refactor IspitService to delegate to the new helper
4. [ ] Create matching tests (12-15 tests)
5. [ ] Validate affected feature/view behavior
6. [ ] Run full test suite (verify zero regressions)
7. [ ] Update ADR-001 with Wave 3 metrics
8. [ ] Commit, push, verify CI/CD

**Success Criteria:**
- [ ] IspitService reduced from 545 closer to ~430 lines
- [ ] New helper service has 100% targeted coverage
- [ ] All 1378+ tests pass
- [ ] CI/CD green (Laravel + CodeQL)
- [ ] Code quality: 9.5/10

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
- `app/Services/KandidatService.php` - 670 lines (orchestration + cache/query cleanup candidate)
- `app/Services/KandidatEnrollmentService.php` - 132 lines (already extracted enrollment + batch operations)
- `app/Services/IspitService.php` - 545 lines (main remaining Ispit orchestrator)
- `app/Services/IspitZapisnikService.php` - 134 lines (listing, create-form, AJAX lookup, archive extraction)
- `app/Services/PrijavaService.php` - 849 lines (extracted from PrijavaController)
- `app/Services/StudentListService.php` - 323 lines (DRY refactored)
- `app/Services/BasePdfService.php` - 53 lines (shared PDF generation)
- `app/Services/IspitPdfService.php` - 222 lines (extracted from IspitService)
- `app/Services/UpisService.php` - 387 lines
- `app/Services/FileStorageService.php` - 137 lines (Wave 1)
- `app/Services/GradeManagementService.php` - 172 lines (Wave 1)
- `app/Services/DropdownDataService.php` - 192 lines (Wave 2)
- `app/Services/SportsManagementService.php` - 79 lines (Wave 2)
- `app/Services/DocumentManagementService.php` - 138 lines (Wave 2, later extended)

### Tests (Current State)
- `tests/Feature/IspitZapisnikServiceTest.php` - 10 tests
- `tests/Unit/Services/FileStorageServiceTest.php` - 27 tests
- `tests/Unit/Services/GradeManagementServiceTest.php` - 22 tests
- `tests/Unit/Services/SportsManagementServiceTest.php` - 9 tests
- `tests/Unit/Services/DocumentManagementServiceTest.php` - 15 tests
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
# Identify the next cohesive IspitService slice after IspitZapisnikService
grep -n "public function" app/Services/IspitService.php

# Create service + test after choosing the slice
touch app/Services/IspitResultService.php
touch tests/Feature/IspitResultServiceTest.php

# Run tests
vendor/bin/phpunit tests/Feature/IspitResultServiceTest.php tests/Feature/IspitServiceTest.php tests/Feature/IspitControllerTest.php
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
- [x] 9.0/10 - Priority improvements (PrijavaController refactor, PHPStan 0, FormRequests, test assertions, DRY StudentListService)
- [ ] 9.5/10 - Wave 3 target (IspitService workflow extraction)
- [ ] 10.0/10 - Wave 4+5 target (Cache + Validation extracted, ~380 lines)

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

### 2026-04-08: DTO and CI Stabilization (8.0 → 8.5)
- Request-to-service coupling reduced through DTO usage
- Legacy model reference cleanup
- CI reliability improved (Pint + PHPStan green)
- IspitPdfService extracted from IspitService (818→614)
- KandidatEnrollmentService extracted
- KandidatService: 785 → 733 lines

### 2026-04-14: Priority Improvements (8.5 → 9.0)
- PrijavaController refactored: 731→280 LOC, PrijavaService created (849 LOC)
- PHPStan baseline fully eliminated: 40→0 errors across 20+ files
- 9 new FormRequest classes added (22→31 total)
- 23 smoke tests replaced with real assertions (+22 assertions)
- StudentListService DRY refactor: 408→323 LOC via BasePdfService
- KandidatService: 733 → 662 lines
- Final state: 1378 tests, 3426 assertions, 0 errors
- CI/CD: All green ✅

---

**Ready for next session! Start with this roadmap and ADR-001 for full context.**
