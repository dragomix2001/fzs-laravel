# Frontend Template Audit Report

**Generated:** 2026-07-01  
**Scope:** 247 blade template files (21,227 total lines)  
**Purpose:** Quantify Bootstrap & jQuery usage before migration to Tailwind + Alpine.js

---

## Executive Summary

### Overall Statistics

| Metric | Count | Notes |
|--------|-------|-------|
| **Total Files** | 247 | All `.blade.php` templates |
| **Total Lines** | 21,227 | Template code only (no vendor) |
| **Bootstrap Classes** | 2,564 | `btn-*`, `col-*`, `card-*`, `form-*`, etc. |
| **jQuery Calls** | 251 | `$()`, `$.ajax`, `.modal()`, etc. |
| **Modules Affected** | 48 | Grouped by `resources/views/` subdirectory |

### Risk Assessment

- **High Complexity Modules** (100+ Bootstrap classes): **6 modules**
  - Sifarnici (533 classes), Kandidat (425), Prijava (288), Izvestaji (148), Raspored (111), Ispit (112)
- **jQuery-Heavy Modules** (20+ calls): **4 modules**
  - Prijava (90 calls), Ispit (49), Sifarnici (33), Upis (24)
- **Low-Risk Modules** (0 Bootstrap/jQuery): **8 modules**
  - Components, emails, stats, etc. (already clean or minimal HTML)

---

## Module-by-Module Analysis

### 🔴 **Critical Priority** (Weeks 1-3)

These modules have the highest Bootstrap/jQuery density and are critical user workflows.

#### 1. **Sifarnici** (Codebooks/Lookups)
- **Files:** 70 templates
- **Lines:** 3,392
- **Bootstrap:** 533 classes
- **jQuery:** 33 calls
- **Complexity Score:** 567 ⚠️ **HIGHEST**
- **Migration Effort:** 20-25 hours
- **Critical Workflows:** CRUD for lookup tables (predmeti, studijskiProgram, godinaStudija, etc.)
- **Risk:** High - many forms, tables, modals

#### 2. **Kandidat** (Candidate Enrollment)
- **Files:** 12 templates
- **Lines:** 2,279
- **Bootstrap:** 425 classes
- **jQuery:** 14 calls
- **Complexity Score:** 457
- **Migration Effort:** 18-22 hours
- **Critical Workflows:** Multi-page enrollment wizard (create_part_1, create_part_2), update forms, document uploads
- **Risk:** High - multi-step forms, file uploads, validation
- **Top Files:**
  - `kandidat/update.blade.php` - 528 lines, 108 Bootstrap classes
  - `kandidat/create.blade.php` - 229 lines, 65 classes
  - `kandidat/create_part_2.blade.php` - 257 lines, 56 classes

#### 3. **Prijava** (Exam Registration)
- **Files:** 13 templates
- **Lines:** 2,407
- **Bootstrap:** 288 classes
- **jQuery:** 90 calls ⚠️ **HIGHEST**
- **Complexity Score:** 396
- **Migration Effort:** 16-20 hours
- **Critical Workflows:** Exam registration, diplomski tema/odbrana/polaganje
- **Risk:** Very High - heavy jQuery for dynamic forms (add/remove subjects, modal popups)
- **Top Files:**
  - `prijava/create.blade.php` - 381 lines, 59 Bootstrap, 24 jQuery
  - `prijava/createManyPredmet.blade.php` - 222 lines, 22 Bootstrap, 21 jQuery
  - `prijava/polaganje/editDiplomskiPolaganje.blade.php` - 252 lines, 34 Bootstrap, 12 jQuery

---

### 🟠 **High Priority** (Week 4-5)

#### 4. **Izvestaji** (Reports)
- **Files:** 21 templates
- **Lines:** 2,094
- **Bootstrap:** 148 classes
- **jQuery:** 14 calls
- **Complexity Score:** 170
- **Migration Effort:** 10-12 hours
- **Critical Workflows:** PDF/Excel report generation forms, student lists, diploma entry
- **Risk:** Medium - mostly forms with DataTables integration
- **Top Files:**
  - `izvestaji/spiskoviStudenti.blade.php` - 421 lines, 67 Bootstrap, 4 jQuery
  - `izvestaji/diplomskiUnos.blade.php` - 199 lines, 48 Bootstrap, 7 jQuery

#### 5. **Ispit** (Exams)
- **Files:** 5 templates
- **Lines:** 956
- **Bootstrap:** 112 classes
- **jQuery:** 49 calls
- **Complexity Score:** 168
- **Migration Effort:** 8-10 hours
- **Critical Workflows:** Zapisnik (exam record) CRUD, student membership
- **Risk:** High - jQuery-heavy for dynamic student lists
- **Top Files:**
  - `ispit/pregledZapisnik.blade.php` - 391 lines, 47 Bootstrap, 23 jQuery
  - `ispit/createZapisnik.blade.php` - 232 lines, 30 Bootstrap, 24 jQuery

#### 6. **Raspored** (Schedule)
- **Files:** 5 templates
- **Lines:** 570
- **Bootstrap:** 111 classes
- **jQuery:** 0 calls ✅ (easy migration)
- **Complexity Score:** 114
- **Migration Effort:** 6-8 hours
- **Critical Workflows:** Class schedule CRUD
- **Risk:** Low - no jQuery, mostly Bootstrap grid/forms
- **Top Files:**
  - `raspored/edit.blade.php` - 155 lines, 39 Bootstrap
  - `raspored/create.blade.php` - 149 lines, 39 Bootstrap

---

### 🟡 **Medium Priority** (Week 6-7)

#### 7. **Student** (Student Management)
- **Files:** 5 templates
- **Lines:** 621
- **Bootstrap:** 84 classes
- **jQuery:** 6 calls
- **Complexity Score:** 95
- **Migration Effort:** 5-7 hours

#### 8. **Upis** (Enrollment)
- **Files:** 3 templates
- **Lines:** 610
- **Bootstrap:** 62 classes
- **jQuery:** 24 calls
- **Complexity Score:** 91
- **Migration Effort:** 5-7 hours

#### 9. **Auth** (Authentication)
- **Files:** 7 templates
- **Lines:** 426
- **Bootstrap:** 85 classes
- **jQuery:** 0 calls ✅
- **Complexity Score:** 86
- **Migration Effort:** 4-6 hours

#### 10. **Skolarina** (Tuition)
- **Files:** 6 templates
- **Lines:** 605
- **Bootstrap:** 73 classes
- **jQuery:** 4 calls
- **Complexity Score:** 79
- **Migration Effort:** 4-6 hours

#### 11. **Obavestenja** (Notifications)
- **Files:** 6 templates
- **Lines:** 413
- **Bootstrap:** 71 classes
- **jQuery:** 0 calls ✅
- **Complexity Score:** 72
- **Migration Effort:** 4-5 hours

#### 12. **Dashboard**
- **Files:** 3 templates
- **Lines:** 415
- **Bootstrap:** 66 classes
- **jQuery:** 0 calls ✅
- **Complexity Score:** 69
- **Migration Effort:** 4-5 hours

---

### 🟢 **Low Priority** (Week 8+)

#### Remaining Modules (37 modules, 5,780 lines total)
- **Average Complexity:** 20-55 per module
- **Total Bootstrap:** 763 classes
- **Total jQuery:** 17 calls
- **Migration Effort:** 15-20 hours total (batched)
- **Risk:** Low - minimal usage, standalone pages

**Notable Low-Risk Modules:**
- **Widgets** (32 files, 55 complexity) - Mostly HTML components
- **Prediction** (3 files, 54 complexity) - Charts/stats
- **User** (4 files, 53 complexity) - User management
- **Kalendar** (4 files, 53 complexity) - Calendar views
- **Aktivnost** (5 files, 34 complexity) - Activity logs

---

## Migration Priority Matrix

### Recommended Order (by Risk × Impact)

| Phase | Modules | Files | Bootstrap | jQuery | Effort | Cumulative |
|-------|---------|-------|-----------|--------|--------|------------|
| **1** | Kandidat | 12 | 425 | 14 | 20h | 20h |
| **2** | Prijava | 13 | 288 | 90 | 18h | 38h |
| **3** | Sifarnici | 70 | 533 | 33 | 25h | 63h |
| **4** | Izvestaji + Ispit | 26 | 260 | 63 | 18h | 81h |
| **5** | Raspored + Dashboard | 8 | 177 | 0 | 10h | 91h |
| **6** | Auth + Obavestenja + Student | 18 | 240 | 6 | 14h | 105h |
| **7** | Upis + Skolarina + Remaining | 87 | 641 | 45 | 25h | 130h |

**Total Estimated Effort:** 130 hours (16-18 working days at full capacity)

**Rationale for Order:**
1. **Kandidat First** - Most critical enrollment workflow, moderate jQuery (easier than Prijava)
2. **Prijava Second** - High jQuery usage, but after learning from Kandidat migration
3. **Sifarnici Third** - Largest scope, but mostly repetitive CRUD (can batch after patterns established)
4. **Remaining** - Grouped by decreasing complexity

---

## jQuery Hotspots (Detailed)

Files with **10+ jQuery calls** requiring Alpine.js conversion:

| File | jQuery Calls | Description | Migration Complexity |
|------|--------------|-------------|----------------------|
| `prijava/create.blade.php` | 24 | Dynamic subject add/remove | High |
| `ispit/pregledZapisnik.blade.php` | 23 | Student list manipulation | High |
| `ispit/createZapisnik.blade.php` | 24 | Form field toggling | High |
| `prijava/createManyPredmet.blade.php` | 21 | Multi-select handling | High |
| `upis/unosPrivremeni.blade.php` | 17 | Temporary enrollment form | Medium |
| `prijava/polaganje/editDiplomskiPolaganje.blade.php` | 12 | Modal interactions | Medium |
| `prijava/polaganje/diplomskiPolaganje.blade.php` | 11 | Form validation | Medium |

**Total jQuery-Heavy Files:** 7 files (38% of all jQuery usage)  
**Strategy:** Convert to Alpine.js with `x-data`, `x-show`, `@click` directives

---

## Bootstrap Pattern Analysis

### Most Common Patterns (by class frequency)

| Pattern | Count | Tailwind Equivalent | Migration Effort |
|---------|-------|---------------------|------------------|
| `col-*` (grid) | ~650 | `w-full md:w-1/2 lg:w-1/3` | Medium (responsive breakpoints) |
| `btn btn-*` | ~480 | `<x-button variant="...">` | Easy (component) |
| `form-group` | ~320 | `<x-form-input>` | Easy (component) |
| `card` | ~280 | `<x-card>` | Easy (component) |
| `table table-*` | ~210 | `<x-table>` | Medium (DataTables integration) |
| `modal` | ~150 | `<x-modal>` + Alpine.js | Hard (JS interactions) |
| `alert alert-*` | ~120 | `<x-alert type="...">` | Easy (component) |
| `badge` | ~80 | `px-2 py-1 rounded text-xs` | Easy |
| `navbar` | ~60 | Custom Tailwind nav | Medium |
| `dropdown` | ~50 | Alpine.js `x-show` | Medium |

**Reusable Components to Build (Priority):**
1. `<x-button>` - Replaces ~480 instances
2. `<x-form-input>` - Replaces ~320 instances
3. `<x-card>` - Replaces ~280 instances
4. `<x-table>` - Replaces ~210 instances
5. `<x-modal>` - Replaces ~150 instances
6. `<x-alert>` - Replaces ~120 instances
7. Grid utility classes - Replaces ~650 instances

**Total Coverage:** ~2,210 instances (86% of all Bootstrap usage)

---

## Risk Mitigation Strategies

### High-Risk Areas

#### 1. **DataTables.js Integration**
**Affected Modules:** Izvestaji, Ispit, Kandidat  
**Issue:** DataTables expects Bootstrap HTML structure  
**Solution:**  
- Keep DataTables Bootstrap compatibility mode temporarily
- Add `@tailwindcss/forms` plugin for form styling
- Gradually replace with Alpine.js + Tailwind tables

#### 2. **jQuery Modal Interactions**
**Affected Modules:** Prijava, Ispit  
**Issue:** `.modal('show')` calls throughout codebase  
**Solution:**  
```blade
{{-- Old jQuery --}}
<script>$('#confirmModal').modal('show');</script>

{{-- New Alpine.js --}}
<div x-data="{ show: false }">
  <x-button @click="show = true">Open</x-button>
  <x-modal x-show="show" @close="show = false">...</x-modal>
</div>
```

#### 3. **Dynamic Form Field Addition**
**Affected Modules:** Prijava (createManyPredmet.blade.php - 21 jQuery calls)  
**Issue:** jQuery `.append()` for adding subject rows  
**Solution:**  
```blade
{{-- New Alpine.js --}}
<div x-data="{ subjects: [{}] }">
  <template x-for="(subject, index) in subjects">
    <x-form-input :name="`subjects[${index}]`" />
  </template>
  <x-button @click="subjects.push({})">Add Subject</x-button>
</div>
```

#### 4. **Responsive Grid Breakpoints**
**Affected Modules:** All (650+ `col-*` classes)  
**Issue:** Bootstrap uses `col-md-6`, Tailwind uses `md:w-1/2`  
**Solution:**  
- Create mapping table for common patterns
- Use automated find/replace for bulk conversion
- Test each breakpoint (mobile 375px, tablet 768px, desktop 1920px)

---

## Testing Strategy (Per Module)

### 1. **Pre-Migration Baseline**
```bash
npx playwright test baseline --update-snapshots
```
- Capture screenshots of ALL pages in module
- Save to `tests/e2e/specs/baseline.spec.ts-snapshots/`

### 2. **Component Migration**
- Migrate one template at a time
- Run visual diff after each file:
  ```bash
  npx playwright test baseline --grep "Kandidat"
  ```
- If diff > 20% pixels, manual review required

### 3. **Interaction Testing**
- E2E smoke tests for:
  - Form submissions
  - Modal open/close
  - Dropdown interactions
  - Table sorting/filtering (if DataTables)

### 4. **Responsive Testing**
- Test on 3 viewports:
  - Desktop: 1920x1080
  - Tablet: 768x1024
  - Mobile: 375x812

### 5. **Manual QA Checklist**
- [ ] Colors match brand guidelines
- [ ] Hover states work (buttons, links)
- [ ] Focus states visible (accessibility)
- [ ] Loading states display (spinners, skeletons)
- [ ] Error states display (validation errors, alerts)

---

## Timeline Estimate (Detailed)

### Phase 1: Infrastructure (Week 0) ✅
- ✅ Playwright setup
- ✅ Auth fixture
- ✅ Baseline test suite
- ⏳ **Capture baseline screenshots** (pending DB setup)
- **Effort:** 6 hours (mostly complete)

### Phase 2: Component Library (Week 1)
- Build 7 core Tailwind components
- Document component API
- **Effort:** 12 hours

### Phase 3: Kandidat Module (Week 2-3)
- 12 templates, 425 Bootstrap, 14 jQuery
- **Effort:** 20 hours

### Phase 4: Prijava Module (Week 3-4)
- 13 templates, 288 Bootstrap, 90 jQuery
- **Effort:** 18 hours

### Phase 5: Sifarnici Module (Week 4-5)
- 70 templates, 533 Bootstrap, 33 jQuery
- **Effort:** 25 hours (can batch after patterns established)

### Phase 6: Remaining Critical (Week 6-7)
- Izvestaji, Ispit, Raspored, Dashboard
- **Effort:** 28 hours

### Phase 7: Medium Priority (Week 8-9)
- Auth, Student, Upis, Skolarina, Obavestenja
- **Effort:** 23 hours

### Phase 8: Low Priority + Cleanup (Week 10)
- Remaining 37 modules
- Bundle optimization
- **Effort:** 20 hours

**Total Duration:** 10 weeks at 12-15 hours/week  
**OR:** 4-5 weeks at full-time capacity (30-35 hours/week)

---

## Success Metrics

### Quantitative
- [ ] **100% templates migrated** - 247 files converted
- [ ] **0 Bootstrap classes remaining** - All `btn-*`, `col-*`, etc. removed
- [ ] **0 jQuery calls remaining** - All `$()` replaced with Alpine.js
- [ ] **Bundle size reduced 60%+** - From 160KB → ~64KB CSS
- [ ] **Visual regression pass rate >95%** - Max 5% pixel diff across all pages
- [ ] **0 broken interactions** - All E2E tests pass

### Qualitative
- [ ] **Lighthouse Performance >90** - Faster CSS load time
- [ ] **Lighthouse Accessibility 100** - Better focus states
- [ ] **Code maintainability improved** - Utility-first CSS easier to modify
- [ ] **Developer velocity improved** - No more Bootstrap override conflicts

---

## Rollback Plan

If migration causes critical bugs:

1. **Per-file rollback:**
   ```bash
   git checkout origin/main resources/views/kandidat/create.blade.php
   ```

2. **Per-module rollback:**
   ```bash
   git checkout origin/main resources/views/kandidat/
   ```

3. **Full rollback (emergency):**
   ```bash
   git revert <migration-commit-hash>
   npm install  # Re-install Bootstrap if removed
   ```

4. **Temporary Bootstrap restoration:**
   - Keep Bootstrap CSS in `public/css/bootstrap.min.css` until Phase 8
   - Gradual removal after all modules pass visual tests

---

## Appendix: Top 50 Files by Complexity

See `/tmp/frontend-audit-sorted.csv` for full list.

**Top 10 Most Complex Files:**

1. `kandidat/update.blade.php` - 528L, 108BS, 0JQ, Score: 113
2. `prijava/create.blade.php` - 381L, 59BS, 24JQ, Score: 86
3. `izvestaji/spiskoviStudenti.blade.php` - 421L, 67BS, 4JQ, Score: 75
4. `ispit/pregledZapisnik.blade.php` - 391L, 47BS, 23JQ, Score: 73
5. `kandidat/create.blade.php` - 229L, 65BS, 0JQ, Score: 67
6. `kandidat/create_part_2.blade.php` - 257L, 56BS, 0JQ, Score: 58
7. `kandidat/update_master.blade.php` - 252L, 53BS, 2JQ, Score: 57
8. `izvestaji/diplomskiUnos.blade.php` - 199L, 48BS, 7JQ, Score: 56
9. `ispit/createZapisnik.blade.php` - 232L, 30BS, 24JQ, Score: 56
10. `prijava/polaganje/editDiplomskiPolaganje.blade.php` - 252L, 34BS, 12JQ, Score: 48

---

**Next Steps:**
1. Review this audit report
2. Approve migration priority order
3. Capture baseline screenshots (pending DB setup)
4. Start Phase 2: Build Tailwind component library
5. Begin Phase 3: Migrate Kandidat module

**Status:** 🟡 Ready to proceed after baseline screenshot capture
