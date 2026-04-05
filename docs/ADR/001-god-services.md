# ADR-001: God Services (Known Technical Debt)

**Status:** Accepted (legacy)

**Date:** 2024-01-XX

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
