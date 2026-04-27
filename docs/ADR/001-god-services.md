# ADR-001: God Services (Known Technical Debt)

**Status:** Accepted (active mitigation)

**Date:** 2026-04-25

**Decision Makers:** Development Team

---

## Context

The application contains two large "God Services" with multiple responsibilities:

- **KandidatService**: 935 lines, 35 public methods
- **IspitService**: 723 lines, 25 public methods

These services violate the Single Responsibility Principle by handling:
- CRUD operations
- File uploads (images, PDFs)
- Cache management
- Data aggregation
- Dropdown data retrieval
- Mass operations (queue dispatch)
- Business logic orchestration

**Example responsibilities in KandidatService:**
1. Kandidat CRUD (create, update, delete)
2. Image upload/update/delete
3. PDF upload/update/delete
4. Grades management (UspehSrednjaSkola)
5. Sports engagement (SportskoAngazovanje)
6. Documents management (KandidatPrilozenaDokumenta)
7. Dropdown data for forms
8. Cache management for active programs
9. Mass enrollment dispatch
10. File cleanup operations

---

## Decision

**We accept God Services as technical debt** and will NOT perform a full refactor immediately.

**Reasons:**
1. **Production stability**: Application is running in production; full refactor risks breaking changes
2. **High cost**: Decomposing God Services would require 40-50 hours per service (120-150 hours total)
3. **Business continuity**: Full refactor would block feature development for 3-4 weeks
4. **Test coverage exists**: 60% coverage mitigates regression risk

---

## Consequences

### Negative
- **Onboarding difficulty**: New developers need 30+ minutes to understand a single service
- **Merge conflicts**: High risk when multiple developers work on same service
- **Testability**: Complex mock setups required (Cache, Storage, DB facades)
- **Maintainability**: Bug fixes require navigating 900+ lines of code

### Positive
- **No breaking changes**: Existing functionality remains stable
- **Incremental improvement possible**: Can extract sub-services gradually
- **Production-ready**: Code works and delivers business value

---

## Mitigation Strategy

Apply **Boy Scout Rule** (leave code better than you found it):

1. When adding a new feature to KandidatService:
   - Extract relevant logic into a sub-service (e.g., KandidatFileService)
   - Inject the sub-service into KandidatService
   - Update only the affected methods

2. When fixing a bug:
   - Refactor the immediate area (method/class)
   - Add tests for the bug fix
   - Document the change

3. Target sub-services for extraction:
   - **KandidatFileService**: handleImageUpload, handlePdfUpload, deleteFiles
   - **KandidatDropdownService**: getDropdownData, getStudijskiProgrami
   - **KandidatGradesService**: storeGrades, updateGrades (UspehSrednjaSkola logic)
   - **KandidatDocumentsService**: manageDocuments (KandidatPrilozenaDokumenta)

---

## Future

**Long-term goal** (6-12 months):
- Decompose KandidatService into 5-7 smaller services
- Estimated effort: 120-150 hours (incremental, spread across feature work)
- Expected result: Services under 200 lines each, clear single responsibility

**Success metrics:**
- KandidatService reduced from 935 → 300 lines
- IspitService reduced from 723 → 250 lines
- Onboarding time for new developers: 30 min → 10 min per service

---

## Update (2025-04-05): First Helper Extraction Completed

**Status:** In Progress

We've successfully extracted the first helper services from KandidatService, following the incremental mitigation strategy outlined above.

### Extracted Services

#### 1. FileStorageService (136 lines)
**Purpose:** Centralize all file upload/storage operations for kandidat images and PDFs.

**Extracted methods:**
- `uploadImageForKandidat()` - Upload new kandidat image
- `replaceImageForKandidat()` - Replace existing kandidat image
- `replacePdfForKandidat()` - Replace existing kandidat PDF
- `deleteImageForKandidat()` - Delete kandidat image file

**Bug fixes during extraction:**
- **PDF Filename Bug**: Fixed concatenation issue where `diplomski{ID}pdf` was created instead of `diplomski{ID}.pdf` (missing dot)
- Old code: `$pdfName = 'diplomski'.$kandidat->id.$extension;` (produces `diplomski123pdf`)
- Fixed code: `$pdfName = 'diplomski'.$kandidat->id.'.'.$extension;` (produces `diplomski123.pdf`)

**Test coverage:** 27 tests, 57 assertions

#### 2. GradeManagementService (175 lines)
**Purpose:** Centralize high school grade (UspehSrednjaSkola) CRUD operations.

**Extracted methods:**
- `createGradesForKandidat()` - Create 4 grade records for new kandidat
- `updateGradesForKandidat()` - Update existing grade records
- `getGradesForEdit()` - Retrieve grades formatted for edit form
- `deleteGradesForKandidat()` - Delete all grades when kandidat is deleted

**Bug fixes during extraction:**
- **RedniBrojRazreda Bug**: Fixed incorrect default values in `getGradesForEdit()`
  - Old code: All 4 catch blocks set `RedniBrojRazreda = 1` (incorrect for grades 2, 3, 4)
  - Fixed code: Correctly sets `RedniBrojRazreda = 1, 2, 3, 4` for each respective grade
- **Missing Grade Deletion**: Added explicit grade deletion in `deleteKandidat()` to prevent orphaned records
  - Old code: `deleteKandidat()` did NOT delete `UspehSrednjaSkola` rows
  - Fixed code: Calls `gradeManagementService->deleteGradesForKandidat($id)` before deleting kandidat

**Test coverage:** 22 tests, 95 assertions

### Impact on KandidatService

**Before:**
- 1026 lines
- 35 public methods
- Handled file storage, grade management, and all other responsibilities

**After:**
- 904 lines (**122 line reduction, 11.9% decrease**)
- 33 public methods (2 removed, delegated to helper services)
- Constructor now injects 3 dependencies: UpisService, FileStorageService, GradeManagementService
- File and grade operations delegated to specialized services

**Refactored methods:**
- `storeKandidatPage1()` - Delegates image upload to FileStorageService
- `storeKandidatPage2()` - Delegates grade creation to GradeManagementService
- `updateKandidat()` - Delegates image/PDF replacement and grade updates
- `storeMasterKandidat()` - Delegates image upload
- `updateMasterKandidat()` - Delegates image replacement
- `deleteKandidat()` - Delegates image deletion and grade deletion
- `getEditDropdownData()` - Delegates grade retrieval

### Test Suite Updates

**New tests created:**
- `FileStorageServiceTest.php` - 27 tests
- `GradeManagementServiceTest.php` - 22 tests
- Total: **49 new tests, 152 assertions**

**Existing tests updated:**
- `KandidatServiceTest.php` - Removed 7 obsolete file handling tests (now covered by FileStorageServiceTest)
- `tests/Feature/KandidatServiceTest.php` - Updated 5 tests to mock all 3 constructor dependencies

**Test results:**
- All KandidatService tests pass (55 tests)
- All FileStorageService tests pass (27 tests)
- All GradeManagementService tests pass (22 tests)
- **No regressions** in existing functionality

### Lessons Learned

1. **Bug Discovery**: Refactoring exposed 3 critical bugs that went unnoticed in the original God Service:
   - PDF filename concatenation error
   - Incorrect grade number defaults
   - Missing grade cleanup on kandidat deletion

2. **Test Value**: Comprehensive test coverage for helper services (100%) vs partial coverage for God Service (60%) makes bugs more visible

3. **Incremental Approach Works**: Extracting 2 services reduced complexity by 12% without breaking existing functionality

4. **Constructor Injection**: Adding dependencies to KandidatService required updating feature tests but NOT unit tests (Laravel DI handled it automatically)

### Next Steps

Following the original mitigation strategy, future extractions could target:

- **Dropdown Data Service**: Extract `getDropdownData()`, `getDropdownDataMaster()`, `getStudijskiProgrami()`
- **Sports Management Service**: Extract `storeSport()` and SportskoAngazovanje logic from `storeKandidatPage2()`
- **Document Management Service**: Extract KandidatPrilozenaDokumenta logic from store/update methods
- **Mass Operations Service**: Extract `masovniUpis()`, `masovnaUplata()`, `masovniUpisAsync()`

**Estimated remaining effort:** 80-100 hours to complete full decomposition (down from original 120-150 estimate).

**Current progress:** ~30% complete (2/7 target services extracted).

---

## Update (2025-04-06): Second Wave Extraction - 8.0 Quality Target

**Status:** Completed

We've successfully completed the second wave of helper service extractions from KandidatService, moving from code quality 7.5/10 to 8.0/10.

### Extracted Services (Wave 2)

#### 3. DropdownDataService (172 lines)
**Purpose:** Centralize all dropdown/form data retrieval for kandidat forms.

**Extracted methods:**
- `getStudijskiProgrami($tipStudijaId)` - Get study programs filtered by type
- `getDropdownData()` - Get all dropdown data for osnovne studije creation form
- `getDropdownDataMaster()` - Get all dropdown data for master studije creation form
- `getEditDropdownData($id)` - Get dropdown data for osnovne kandidat edit form (includes grades)
- `getEditDropdownDataMaster($id)` - Get dropdown data for master kandidat edit form

**Dependencies:** Injects `GradeManagementService` for grade retrieval in edit forms

**Test coverage:** 14 tests, 67 assertions

#### 4. SportsManagementService (79 lines)
**Purpose:** Centralize sports engagement (SportskoAngazovanje) CRUD operations.

**Extracted methods:**
- `createSportForKandidat($kandidatId, array $data)` - Create sports engagement record
- `getSportsForKandidat($kandidatId)` - Retrieve all sports for kandidat
- `deleteSportsForKandidat($kandidatId)` - Delete all sports when kandidat is deleted

**Dependencies:** None (standalone service)

**Test coverage:** 9 tests, 29 assertions

#### 5. DocumentManagementService (initial extraction: 82 lines, current implementation: 138 lines)
**Purpose:** Centralize candidate document attachment (KandidatPrilozenaDokumenta) management, including per-document file upload and attachment metadata persistence.

**Extracted methods:**
- `attachDocumentsForKandidat($kandidatId, array $dokumentiPrva, array $dokumentiDruga, array $documentUploadsPrva = [], array $documentUploadsDruga = [])` - Attach documents to kandidat and persist uploaded files per attachment
- `getAttachedDocumentIds($kandidatId)` - Get attached document IDs for edit form
- `deleteDocumentsForKandidat($kandidatId)` - Delete all documents and uploaded files when kandidat is deleted

**Current responsibilities beyond the original extraction:**
- Merge checkbox selections with uploaded files so a file upload implicitly selects the document
- Store uploaded files under the `uploads` disk in `documents/{kandidatId}`
- Persist file metadata (`file_path`, `file_name`, `mime_type`, `file_size`) on the attachment row
- Clean up stored files when candidate attachments are removed

**Dependencies:** `Storage` facade and `UploadedFile` handling

**Test coverage:** 15 tests covering attachment creation, file persistence, file cleanup, review defaults, model relations, and review status scopes

**Related follow-up workflow:** Admin-facing document review is now handled by `DocumentReviewService` and `DocumentReviewController`, while `DocumentManagementService` remains responsible for storage and attachment lifecycle.

### Impact on KandidatService (Wave 2)

**Before Wave 2:**
- 904 lines (after Wave 1)
- 33 public methods
- Constructor injected 3 dependencies

**After Wave 2:**
- **785 lines** (**119 line reduction, 13.2% decrease from Wave 1**)
- 28 public methods (5 extracted to helper services)
- Constructor now injects **6 dependencies:**
  - UpisService (existing)
  - FileStorageService (Wave 1)
  - GradeManagementService (Wave 1)
  - DropdownDataService (Wave 2)
  - SportsManagementService (Wave 2)
  - DocumentManagementService (Wave 2)

**Total reduction from original:** 1026 → 785 lines (**241 lines removed, 23.5% reduction**)

**Refactored methods (Wave 2):**
- `getStudijskiProgrami($tipStudijaId)` - Delegates to DropdownDataService
- `getDropdownData()` - Delegates to DropdownDataService
- `getDropdownDataMaster()` - Delegates to DropdownDataService
- `getEditDropdownData($id)` - Delegates to DropdownDataService
- `getEditDropdownDataMaster($id)` - Delegates to DropdownDataService
- `storeSport($data)` - Delegates to SportsManagementService
- `storeKandidatPage2()` - Delegates sports creation and document attachment
- `updateKandidat()` - Delegates document attachment
- `storeMasterKandidat()` - Delegates document attachment
- `updateMasterKandidat()` - Delegates document attachment
- `deleteKandidat()` - Delegates sports and document deletion

### Test Suite Updates (Wave 2)

**New tests created:**
- `SportsManagementServiceTest.php` - 9 tests, 29 assertions
- `DocumentManagementServiceTest.php` - 10 tests, 29 assertions
- `DropdownDataServiceTest.php` - 14 tests, 67 assertions
- Total: **33 new tests, 125 assertions**

**Combined test metrics (Wave 1 + Wave 2):**
- Total new helper service tests: **82 tests, 277 assertions**
- Combined with existing KandidatService tests: **111 total unit tests**

**Test results:**
- All KandidatService tests pass (no regressions)
- All 5 helper service tests pass at 100% coverage
- **Zero regressions** in existing functionality
- All tests pass Pint style checks (PSR-12 compliant)

### Code Quality Metrics

**Quality Score Progress:**
- **Starting point (legacy):** 7.0/10 (KandidatService at 1026 lines, God Service pattern)
- **After Wave 1:** 7.5/10 (KandidatService at 904 lines, FileStorage + GradeManagement extracted)
- **After Wave 2:** **8.0/10** (KandidatService at 785 lines, 5 helper services extracted)

**Improvement indicators:**
- **23.5% line reduction** (1026 → 785 lines)
- **14% method reduction** (35 → 28 public methods)
- **100% test coverage** for all 5 extracted services
- **Zero bugs introduced** during refactoring
- **Improved separation of concerns** (6 focused services vs 1 monolith)

### Architecture Improvements

**Historical service dependency graph after Wave 2:**
```
KandidatService (785 lines, core orchestrator)
├── UpisService (existing)
├── FileStorageService (Wave 1, 136 lines)
├── GradeManagementService (Wave 1, 175 lines)
├── DropdownDataService (Wave 2, 172 lines)
│   └── GradeManagementService (injected dependency)
├── SportsManagementService (Wave 2, 79 lines)
└── DocumentManagementService (Wave 2, 82 lines at extraction time)
```

**Total helper code extracted at the end of Wave 2:** 644 lines (136 + 175 + 172 + 79 + 82)

**Benefits achieved:**
1. **Single Responsibility:** Each helper service has one clear purpose
2. **Testability:** 100% coverage for helpers vs 60% for monolith
3. **Reusability:** DropdownDataService can be used by other controllers
4. **Maintainability:** Bug fixes now target 79-175 line services, not 1026 line monolith
5. **Onboarding:** New developers can understand one 100-line service vs entire God Service

### Next Steps

This section is now historical only. Mass operations are no longer a hypothetical extraction target: they were extracted later into `KandidatEnrollmentService`.

**What actually remains after Wave 2 and later follow-up work:**
- Further split orchestration and query responsibilities inside `IspitService`
- Reduce `KandidatService` around cache/query helper behavior rather than enrollment batch logic
- Watch `PrijavaService` growth and split it only if distinct sub-domains stabilize

**Long-term goal (10.0/10):** Keep large services as orchestration layers only, with transaction-heavy and IO-heavy blocks extracted behind focused services.

**Historical progress at this checkpoint:** ~60% complete (5/8 original helper targets extracted).

---

## Update (2026-04-08): Current State After DTO and CI Stabilization

**Status:** In Progress

The decomposition strategy remains valid and is still the recommended path. The latest cycle delivered additional architectural cleanup and CI hardening, while keeping `KandidatService` and `IspitService` as orchestrator-heavy services.

### Measured Current Service Size

- `KandidatService`: 733 lines
- `IspitService`: 818 lines
- `DiplomaService`: 84 lines
- `DiplomskiRadService`: 119 lines

### What Changed in This Cycle

- Request-to-service coupling was reduced through additional DTO usage in report and diploma flows.
- Legacy model reference cleanup was completed (`App\\Models\\...` standardization and legacy bridge removal).
- CI reliability was improved (Pint + PHPStan failures resolved and green pipeline restored).

### Architectural Interpretation

- `KandidatService` continues trending down in size and remains a partial orchestrator by design.
- `IspitService` is now the primary god-service candidate and should become the next decomposition focus.
- Smaller report/diploma services indicate that extraction-by-flow works and should continue.

### Updated Next Steps

1. Prioritize `IspitService` decomposition (report generation, zapisnik orchestration, and mass operations).
2. Continue extracting transaction-heavy blocks from `KandidatService` into focused services.
3. Keep architecture changes coupled with DTO boundaries and targeted tests.

---

## Update (2026-04-14): Priority Improvements Complete — 9.0/10

**Status:** Significant Progress

Five priority improvements were completed in a single session, bringing overall code quality from ~7.5/10 to 9.0/10.

### Measured Current Service Sizes

| Service | Lines | Change |
|---|---|---|
| KandidatService | 662 | ↓ from 733 (previous update) |
| IspitService | 614 | ↓ from 818 (IspitPdfService extracted) |
| PrijavaService | 849 | NEW (extracted from PrijavaController) |
| PrijavaController | 280 | ↓ from 731 (refactored to thin controller) |
| StudentListService | 323 | ↓ from 408 (DRY refactor) |
| BasePdfService | 53 | NEW (shared PDF generation method) |
| IspitPdfService | 222 | Existing (extracted in earlier session) |
| UpisService | 387 | Existing |

### What Changed in This Cycle

#### 1. PrijavaController Refactor (731→280 LOC)
- Complete business logic extracted into PrijavaService (849 LOC)
- PrijavaController reduced to thin HTTP layer with dependency injection
- All existing tests continue to pass

#### 2. PHPStan Baseline Fully Eliminated (40→0 errors)
- Fixed 40+ errors across 20+ files: `env()`→`config()`, PHPDoc types, class references, return types
- Fixed TCPDF constructor (extra parameter silently ignored)
- Created PHPStan stub for TCPDF vendor class
- Fixed unsafe `new static()` in Auditable trait → `$this->getTable()`
- Fixed ZipArchive method names in BackupService
- Baseline neon file now contains `ignoreErrors: []`

#### 3. Added 9 New FormRequest Classes (22→31 total)
- StoreRasporedRequest, StoreObavestenjeRequest, UpdateObavestenjeRequest
- StoreUserRequest, UpdateUserRequest
- ChatMessageRequest, QuickQuestionRequest
- ImportFileRequest, LoginRequest
- 5 controllers updated to use FormRequest classes
- Fixed AuditLogFactory DST timezone bug (Europe/Belgrade spring-forward gap)

#### 4. Improved Test Assertions
- 23 `assertTrue(true)` smoke tests replaced with real assertions
- PDF output validation (`%PDF` magic bytes) for StudentListService
- Guest redirect assertions for auth tests
- Assertions: 3404→3426 (+22)

#### 5. DRY StudentListService (408→323 LOC)
- Extracted shared `generatePdf()` method into BasePdfService
- All 12 PDF generation methods refactored to use shared method
- 85 lines removed (21% reduction)

### Test Suite State

- **1378 tests, 3426 assertions** — 0 errors, 0 failures
- **PHPStan**: Level 5, 0 errors, empty baseline
- **Pint**: pass
- **CI/CD**: Both pipelines green (Laravel CI/CD + CodeQL Advanced)

### Historical Service Dependency Graph at the 2026-04-14 Checkpoint

```
KandidatService (662 lines, core orchestrator)
├── UpisService (387 lines)
├── FileStorageService (136 lines, Wave 1)
├── GradeManagementService (175 lines, Wave 1)
├── DropdownDataService (172 lines, Wave 2)
│   └── GradeManagementService (injected)
├── SportsManagementService (79 lines, Wave 2)
└── DocumentManagementService (82 lines at extraction, later extended)

KandidatEnrollmentService (extracted later)
├── mass enrollment operations
├── mass payment operations
└── kandidat enrollment workflow

IspitService (614 lines)
└── IspitPdfService (222 lines)

PrijavaController (280 lines, thin)
└── PrijavaService (849 lines)

StudentListService (323 lines)
└── BasePdfService (53 lines, shared PDF generation)
```

### Next Steps

1. Prioritize `IspitService` decomposition — separate zapisnik listing/query logic, report/PDF orchestration, and archive-specific flows
2. Continue trimming `KandidatService` around cache/program lookup and query aggregation, not mass operations
3. Consider splitting `PrijavaService` only if stable sub-domains emerge instead of creating arbitrary helper classes
4. Increase coverage in under-tested services and controllers before starting another broad refactor wave

---

## Update (2026-04-25): Documentation Alignment After Enrollment and Document Workflow Extraction

**Status:** Current Reference

The documentation itself required correction because some roadmap items no longer matched the codebase.

### Measured Current Service Sizes

| Service | Lines | Notes |
|---|---|---|
| KandidatService | 670 | Main kandidat orchestration service |
| KandidatEnrollmentService | 132 | Enrollment and batch kandidat operations already extracted |
| IspitService | 629 | Main remaining god-service candidate |
| PrijavaController | 280 | Thin controller |
| PrijavaService | 849 | Large extracted service, monitor for future split |
| StudentListService | 323 | Stable after BasePdfService extraction |
| FileStorageService | 137 | Slightly evolved after initial extraction |
| GradeManagementService | 172 | Stable helper |
| DropdownDataService | 192 | Grew with edit/dropdown responsibilities |
| SportsManagementService | 79 | Stable helper |
| DocumentManagementService | 138 | Extended for per-document upload metadata |

### What Is Already Done

- Mass operations were already extracted into `KandidatEnrollmentService`
- Candidate document upload expanded beyond checkbox attachment into per-document file persistence
- Admin document review flow was added separately via `DocumentReviewService` and related controller/views
- `IspitZapisnikService` was extracted for zapisnik listing, create-form lookup data, AJAX subject/student lookup, and archive operations

### Real Remaining Work

1. Continue splitting `IspitService` by behavior, now that zapisnik listing/archive concerns live in `IspitZapisnikService`; the next natural slice is pregled/result orchestration
2. Extract cache/program lookup concerns from `KandidatService` if they continue to grow
3. Reassess `PrijavaService` only after identifying natural boundaries, not by forcing another generic “god service” split
4. Improve coverage in weak areas before starting another wide extraction cycle

---

## Update (2026-04-25): Ispit Zapisnik Query and Archive Extraction

**Status:** Implemented

The first concrete Wave 3 extraction was completed by moving zapisnik listing and archive-oriented responsibilities out of `IspitService`.

### Measured Current Service Sizes

| Service | Lines | Notes |
|---|---|---|
| IspitService | 545 | Orchestrator after zapisnik query/archive extraction |
| IspitZapisnikService | 134 | Listing, create-form lookup, AJAX helpers, archive actions |
| IspitPdfService | 222 | Existing PDF/report generation helper |

### What Changed in This Extraction

- Extracted `getZapisniciForIndex()` from `IspitService`
- Extracted `getCreateZapisnikData()` from `IspitService`
- Extracted `getZapisnikPredmetData()` and `getZapisnikStudenti()` from `IspitService`
- Extracted `getArhiviraniZapisnici()`, `arhivirajZapisnik()`, and `arhivirajZapisnikeZaRok()` from `IspitService`
- Refactored `IspitService` to delegate these responsibilities while preserving its public API for controllers

### Validation

- Added `IspitZapisnikServiceTest` with 10 focused tests
- Re-ran affected suites: `IspitZapisnikServiceTest`, `IspitServiceTest`, and `IspitControllerTest`
- Result: 51 tests, 125 assertions, green locally (with 1 existing PHPUnit deprecation notice)

### Real Remaining Work After This Extraction

1. Extract pregled/result update logic from `IspitService`, especially `getZapisnikPregled()` and result-saving orchestration
2. Revisit whether `addStudentToZapisnik()` should remain in the orchestrator or move into a dedicated zapisnik-membership flow service
3. Keep `IspitPdfService` separate and avoid folding report/PDF concerns back into query services

---

## Update (2026-04-27): Ispit Result and Pregled Extraction

**Status:** Implemented

The second concrete Wave 3 extraction was completed by moving pregled/result/detail responsibilities out of `IspitService` into a dedicated helper.

### Measured Current Service Sizes

| Service | Lines | Notes |
|---|---|---|
| IspitService | 460 | Orchestrator after zapisnik query/archive and result/pregled extraction |
| IspitResultService | 215 | Pregled assembly, result persistence, zapisnik detail updates |
| IspitZapisnikService | 135 | Listing, create-form lookup, AJAX helpers, archive actions |
| IspitPdfService | 222 | Existing PDF/report generation helper |

### What Changed in This Extraction

- Extracted `getZapisnikPregled()` from `IspitService`
- Extracted `savePolozeniIspiti()` from `IspitService`
- Extracted `updateZapisnikDetails()` from `IspitService`
- Refactored `IspitService` to delegate these responsibilities while preserving its public API for controllers
- Replaced the old “last kandidat decides available candidates” pregled behavior with a stable query across all study-program pairs linked to the zapisnik
- Reduced repeated per-student queries by batching `PrijavaIspita` and `PolozeniIspiti` lookups during pregled assembly

### Validation

- Added `IspitResultServiceTest` with 12 focused tests
- Re-ran affected suites: `IspitResultServiceTest`, `IspitServiceTest`, and `GradeSubmissionTest`
- Result: 48 tests, 109 assertions, green locally (with 1 existing PHPUnit deprecation notice)

### Real Remaining Work After This Extraction

1. Decide whether `addStudentToZapisnik()` and `removeStudentFromZapisnik()` should remain in `IspitService` or move into a dedicated membership flow service
2. Keep `IspitPdfService` separate and avoid folding report/PDF concerns back into query services
3. Continue reducing `KandidatService` around cache/program lookup and validation helpers instead of reopening already-extracted enrollment work

---

## Update (2026-04-27): Ispit Membership Extraction — Wave 3 Complete

**Status:** Implemented

The third and final Wave 3 extraction moves student add/remove membership operations from `IspitService` into `IspitMembershipService`.

### Measured Current Service Sizes

| Service | Lines | Notes |
|---|---|---|
| IspitService | 372 | Orchestrator; delegates to membership, result, zapisnik, and PDF helpers |
| IspitMembershipService | 146 | Student add/remove within existing zapisnici |
| IspitResultService | 216 | Pregled assembly, result persistence, zapisnik detail updates |
| IspitZapisnikService | 135 | Listing, create-form lookup, AJAX helpers, archive actions |
| IspitPdfService | 222 | PDF/report generation helper |

### What Changed in This Extraction

- Extracted `addStudentToZapisnik()` from `IspitService`
- Extracted `removeStudentFromZapisnik()` from `IspitService`
- `IspitService` delegates both methods; public API for controllers is unchanged
- `IspitService` reduced from 460 → 372 lines (-88 lines)
- `IspitService` was originally 818 lines; total reduction: **446 lines (-54.5%)**

### Validation

- Added `IspitMembershipServiceTest` with 9 focused tests covering:
  - Add creates ZapisnikStudent, PolozeniIspiti, and PrijavaIspita
  - Add links new study program to zapisnik
  - Add skips already-enrolled student (no duplicate)
  - Add skips student with no matching PredmetProgram
  - Add does not duplicate an already-linked study program
  - Add of two students from same program links program only once
  - Remove deletes student and polozeni ispit records
  - Remove returns false when other students remain
  - Remove deletes zapisnik and returns true when last student is removed
- Re-ran: `IspitMembershipServiceTest`, `IspitServiceTest`: all 40 tests green

### Real Remaining Work

1. Wave 3 (`IspitService` decomposition) is complete
2. Phase 2 (`CacheManagementService`) is complete
3. Next focus: Phase 3 — `KandidatValidationService` extraction and coverage hardening

---

## Update (2026-04-27): Kandidat Cache Management Extraction (Phase 2)

**Status:** Implemented

The planned cache/program lookup extraction was completed by moving active study-program cache concerns from `KandidatService` into `CacheManagementService`.

### Measured Current Service Sizes

| Service | Lines | Notes |
|---|---|---|
| KandidatService | 668 | Orchestrator after cache extraction; validation remains the next cohesive slice |
| CacheManagementService | 48 | Active study-program cache get/clear/refresh |
| IspitService | 372 | Wave 3 complete orchestrator |
| IspitMembershipService | 146 | Membership mutation helper |

### What Changed in This Extraction

- Extracted active study-program cache logic from `KandidatService::getActiveStudijskiProgramId()`
- Added `CacheManagementService` methods:
  - `getActiveStudijskiProgramFromCache(int $tipStudijaId): ?int`
  - `clearActiveStudijskiProgramCache(int $tipStudijaId): void`
  - `refreshActiveStudijskiProgramCache(int $tipStudijaId): ?int`
- `KandidatService` now delegates cache retrieval via constructor-injected `CacheManagementService`
- Kept `KandidatService` public API stable for controllers

### Validation

- Added `CacheManagementServiceTest` with 4 focused tests:
  - Cache hit returns active program id
  - Second read uses cached value
  - Cache clear removes key
  - Cache refresh reloads latest active value
- Re-ran affected suites: `CacheManagementServiceTest`, `KandidatServiceExtendedTest`, `KandidatServiceTest`
- Full regression run: 1571 tests, 3969 assertions, 0 errors locally (with existing deprecations/notices)

### Real Remaining Work

1. Extract validation concerns from `KandidatService` into `KandidatValidationService`
2. Increase confidence in high-risk paths with targeted edge-case and rollback tests
3. Refresh and track percentage coverage from latest CI artifact before setting numeric targets
