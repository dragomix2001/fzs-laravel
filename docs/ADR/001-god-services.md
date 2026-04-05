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
