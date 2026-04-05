# ADR-002: Request Object Coupling in Services

**Status:** Accepted (technical debt)

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
- Some methods use DTOs (e.g., `KandidatData::fromRequest()`)
- Some methods still accept raw `Request` objects

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

**Target:** Convert all service methods to use DTOs within 6-12 months.

**Priority methods:**
1. KandidatService::storeKandidatPage2 (high complexity)
2. IspitService methods accepting Request
3. DiplomaService, DiplomskiRadService

**Estimated effort:** 15-20 hours per service (5 services = 75-100 hours total)
