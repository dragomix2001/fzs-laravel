# ADR-002: Request Object Coupling in Services

**Status:** Accepted (actively mitigated)

**Date:** 2024-01-XX

**Decision Makers:** Development Team

---

## Context

Several service methods accept `Illuminate\Http\Request` objects directly instead of DTOs (Data Transfer Objects).

**Example from KandidatService:**
```php
public function storeKandidatPage2(Request $request): Kandidat
{
    $kandidat = Kandidat::find($request->insertedId);
    $prviRazred->opstiUspeh_id = $request->prviRazred; // Magic string
    $prviRazred->srednja_ocena = $request->SrednjaOcena1; // Magic string
    // ...
}
```

**Problems:**
1. **No type safety**: Magic strings (`$request->prviRazred`) — typos not caught until runtime
2. **Testing difficulty**: Must create HTTP Request objects for unit tests
3. **Reusability**: Cannot call service from Console Commands or Queue Jobs without fake Request
4. **Validation mixing**: Validation logic mixed with business logic

---

## Decision

**We accept Request coupling as technical debt** with **partial mitigation** already in place.

**Current state:**
- Core write paths in `KandidatService`, `IspitService`, `DiplomaService`, and `DiplomskiRadService` use DTO inputs.
- Raw `Request` usage in the service layer is now exception-based, not the default pattern.

**Reasons for acceptance:**
1. **Partial progress**: DTO pattern already introduced in some places
2. **Gradual migration**: Can convert methods incrementally
3. **Low immediate risk**: FormRequest validation prevents invalid data

---

## Consequences

### Negative
- **Testability**: Service tests require HTTP layer setup
- **Magic strings**: No IDE autocomplete, runtime errors possible
- **Coupling**: Services tightly coupled to HTTP layer

### Positive
- **Gradual improvement**: DTO pattern already established
- **Validation exists**: FormRequests catch invalid data before service layer

---

## Mitigation Strategy

**Boy Scout Rule:**
When touching a method that accepts `Request $request`:

1. Create a DTO class:
```php
class StoreKandidatGradesDTO
{
    public function __construct(
        public readonly int $kandidatId,
        public readonly array $grades, // [{razred: 1, uspeh: 5, ocena: 4.5}, ...]
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            kandidatId: $request->insertedId,
            grades: [
                ['razred' => 1, 'uspeh' => $request->prviRazred, 'ocena' => $request->SrednjaOcena1],
                ['razred' => 2, 'uspeh' => $request->drugiRazred, 'ocena' => $request->SrednjaOcena2],
                // ...
            ],
        );
    }
}
```

2. Update service method signature:
```php
public function storeKandidatGrades(StoreKandidatGradesDTO $dto): Kandidat
{
    foreach ($dto->grades as $grade) {
        UspehSrednjaSkola::create([
            'kandidat_id' => $dto->kandidatId,
            'RedniBrojRazreda' => $grade['razred'],
            // ...
        ]);
    }
}
```

3. Update controller:
```php
$dto = StoreKandidatGradesDTO::fromRequest($request);
$this->kandidatService->storeKandidatGrades($dto);
```

---

## Future

**Target:** Keep `Request` out of business services by default and use DTO-first signatures for all new service methods.

**Priority methods (remaining):**
1. Remove remaining edge-case `Request` usage in support/infrastructure services.
2. Continue DTO normalization for any newly introduced service APIs.
3. Keep controller/job layer responsible for `Request -> DTO` mapping.

**Estimated effort:** Low ongoing maintenance effort (incremental updates during feature work).

---

## Update (2026-04-08): DTO Migration Moved from Plan to Practice

**Status:** Substantially Implemented

The ADR intent has been implemented across key flows:

- `KandidatService` uses typed DTOs for create/update paths.
- `IspitService` accepts DTOs for zapisnik and report generation flows.
- `DiplomaService` and `DiplomskiRadService` now use DTO inputs for add/create flows.

### Practical Effect

- Stronger type safety and fewer magic-string dependencies in business services.
- Better unit-test ergonomics because service methods can be called without HTTP request construction.
- Cleaner separation of concerns (`Request` parsing in controller/job layer, business logic in services).

### Remaining Risk

One-off support services may still accept `Request` for audit/context capture; this is tolerated when it does not leak HTTP concerns into core domain logic.
