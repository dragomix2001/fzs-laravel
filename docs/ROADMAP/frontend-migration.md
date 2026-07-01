# Frontend Migration Roadmap: Bootstrap → Tailwind + Visual Testing

**Last Updated:** 2026-07-01  
**Status:** Planning Phase  
**Estimated Duration:** 4-6 weeks (modular approach)  
**Risk Level:** HIGH (100+ UI files affected)

---

## Executive Summary

**Current State:**
- **CSS:** Bootstrap 5 (1900+ class usages) + Tailwind CSS 4 (300+ utility usages) = Conflicts
- **JS:** jQuery + Alpine.js = Double reactivity layer
- **Templates:** 247 blade files
- **Bundle Size:** ~160KB CSS (Bootstrap 145KB + Tailwind 15KB)

**Target State:**
- **CSS:** Tailwind CSS 4 only (utility-first, modern)
- **JS:** Alpine.js only (lightweight reactivity)
- **Bundle Size:** ~40KB CSS (estimated 60% reduction)
- **Testing:** 100% visual regression coverage via Playwright

---

## Testing Strategy: Visual Regression + E2E

### Why Visual Testing is Critical

Frontend migrations **always break UI**. Without visual regression:
- Manual QA misses 60%+ of visual bugs (alignment, spacing, colors)
- Refactoring blind spots (modals, dropdowns, hover states)
- Responsive breakpoints untested (mobile/tablet views)

### Tool Selection: Playwright

| Feature | Laravel Dusk | Playwright | Cypress |
|---------|--------------|------------|---------|
| Visual Diffing | ❌ Manual | ✅ Built-in | ⚠️ Plugin |
| Speed | Slow (PHP) | Fast (Node) | Fast (Node) |
| Cross-browser | Chrome only | Chrome/Firefox/Safari | Chrome/Edge |
| Laravel Integration | Native | Via HTTP | Via HTTP |
| **Recommendation** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ **BEST** | ⭐⭐⭐⭐ |

**Decision:** Use **Playwright** for:
1. Visual regression (screenshot diffing)
2. E2E smoke tests (form submissions, navigation)
3. Cross-browser validation (Chrome + Firefox minimum)

---

## Phase 0: Testing Infrastructure Setup

### 0.1 Install Playwright

```bash
# Install Playwright
npm install -D @playwright/test
npx playwright install chromium firefox

# Create test directory structure
mkdir -p tests/e2e/{specs,fixtures,screenshots}
```

**Config:** `playwright.config.ts`
```typescript
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e/specs',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  
  use: {
    baseURL: 'http://localhost:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  // Visual regression settings
  expect: {
    toHaveScreenshot: {
      threshold: 0.2,        // 20% pixel diff tolerance
      maxDiffPixels: 100,    // Max 100 pixels can differ
      animations: 'disabled', // Disable animations for stable screenshots
    },
  },

  projects: [
    { name: 'chromium', use: { browserName: 'chromium' } },
    { name: 'firefox', use: { browserName: 'firefox' } },
  ],

  webServer: {
    command: 'php artisan serve --port=8000',
    port: 8000,
    reuseExistingServer: !process.env.CI,
  },
});
```

**Estimated Time:** 2 hours  
**Deliverable:** Working Playwright setup, CI integration

---

### 0.2 Create Authentication Helper

Laravel requires auth for most routes. Create test user seeder:

```php
// database/seeders/TestUserSeeder.php
class TestUserSeeder extends Seeder {
    public function run() {
        User::factory()->create([
            'email' => 'test@fzs.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }
}
```

**Playwright auth helper:**
```typescript
// tests/e2e/fixtures/auth.ts
import { test as base } from '@playwright/test';

export const test = base.extend({
  authenticatedPage: async ({ page }, use) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@fzs.test');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');
    
    await use(page);
  },
});
```

---

### 0.3 Baseline Capture (PRE-MIGRATION)

**Critical UI States to Capture:**

```typescript
// tests/e2e/specs/baseline.spec.ts
import { test, expect } from '../fixtures/auth';

test.describe('Baseline Screenshots (Pre-Migration)', () => {
  test.use({ viewport: { width: 1920, height: 1080 } });

  test('Dashboard - Main', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveScreenshot('baseline-dashboard.png');
  });

  test('Kandidat - List', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await expect(page).toHaveScreenshot('baseline-kandidat-list.png');
  });

  test('Kandidat - Create Form (Page 1)', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    await expect(page).toHaveScreenshot('baseline-kandidat-create-page1.png');
  });

  test('Kandidat - Create Form (Page 2)', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    // Fill page 1 to reach page 2
    await page.fill('input[name="JMBG"]', '1234567890123');
    // ... fill other required fields
    await page.click('button:has-text("Dalje")');
    await expect(page).toHaveScreenshot('baseline-kandidat-create-page2.png');
  });

  test('Ispit - Zapisnik List', async ({ authenticatedPage: page }) => {
    await page.goto('/ispit/zapisnici');
    await expect(page).toHaveScreenshot('baseline-ispit-zapisnici.png');
  });

  test('Prijava - Diplomski Tema Form', async ({ authenticatedPage: page }) => {
    await page.goto('/prijava/diplomski/tema');
    await expect(page).toHaveScreenshot('baseline-prijava-diplomski-tema.png');
  });

  test('Obavestenja - List', async ({ authenticatedPage: page }) => {
    await page.goto('/obavestenja');
    await expect(page).toHaveScreenshot('baseline-obavestenja-list.png');
  });

  // Modals
  test('Modal - Kandidat Delete Confirmation', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.click('a[href*="/delete"]:first-of-type');
    await page.waitForSelector('.modal.show');
    await expect(page).toHaveScreenshot('baseline-modal-delete-confirm.png');
  });

  // Responsive views
  test('Mobile - Dashboard', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 }); // iPhone X
    await page.goto('/dashboard');
    await expect(page).toHaveScreenshot('baseline-mobile-dashboard.png');
  });
});
```

**Capture Command:**
```bash
# Generate ALL baseline screenshots (run BEFORE migration starts)
npx playwright test baseline --update-snapshots

# Store in version control
git add tests/e2e/specs/baseline.spec.ts-snapshots/
git commit -m "test: capture visual regression baseline before frontend migration"
```

**Estimated Time:** 4 hours (write tests + capture screenshots)  
**Deliverable:** 20-30 baseline screenshots covering all critical UI states

---

## Phase 1: Kandidat Module (Week 1)

**Why Start Here:**
- Most complex forms (multi-page wizard)
- Heavy Bootstrap usage (cards, form-group, btn-*)
- Critical path (enrollment workflow)

### 1.1 Audit Kandidat Templates

```bash
# Find all kandidat-related blade files
find resources/views -name "*kandidat*.blade.php"

# Count Bootstrap classes
grep -r "btn-\|col-\|form-group\|card-" resources/views/kandidat/ | wc -l
```

**Expected Files:**
- `resources/views/kandidat/index.blade.php` - List view
- `resources/views/kandidat/create.blade.php` - Create form (page 1)
- `resources/views/kandidat/create2.blade.php` - Create form (page 2)
- `resources/views/kandidat/edit.blade.php` - Edit form
- `resources/views/kandidat/show.blade.php` - Detail view
- `resources/views/kandidat/partials/*.blade.php` - Reusable components

**Deliverable:** Kandidat template inventory with Bootstrap usage count

---

### 1.2 Create Tailwind Components

Before migration, create **reusable Blade components** for common patterns:

```bash
# Create component structure
mkdir -p resources/views/components/{forms,cards,buttons,modals}
```

**Example: Button Component**
```blade
{{-- resources/views/components/button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
$variantClasses = [
    'primary' => 'bg-blue-600 hover:bg-blue-700 text-white',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white',
];

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-base',
    'lg' => 'px-6 py-3 text-lg',
];

$classes = implode(' ', [
    'inline-flex items-center justify-center',
    'font-medium rounded-md',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    'transition-colors duration-150',
    $variantClasses[$variant] ?? $variantClasses['primary'],
    $sizeClasses[$size] ?? $sizeClasses['md'],
]);
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
```

**Usage:**
```blade
{{-- Old Bootstrap --}}
<button class="btn btn-primary btn-sm">Sačuvaj</button>

{{-- New Tailwind Component --}}
<x-button variant="primary" size="sm">Sačuvaj</x-button>
```

**Components to Create:**
1. `<x-button>` - Buttons (primary, secondary, danger, success)
2. `<x-card>` - Card container with header/body/footer
3. `<x-form-input>` - Input field with label and validation
4. `<x-form-select>` - Select dropdown
5. `<x-modal>` - Modal dialog
6. `<x-alert>` - Alert messages (success, error, warning, info)
7. `<x-table>` - Responsive table wrapper

**Estimated Time:** 8 hours  
**Deliverable:** 7 reusable Tailwind components

---

### 1.3 Migrate Kandidat Templates

**Step-by-step process for EACH template:**

1. **Copy original file:**
   ```bash
   cp resources/views/kandidat/index.blade.php resources/views/kandidat/index.blade.php.bootstrap-backup
   ```

2. **Replace Bootstrap → Tailwind classes:**

   | Bootstrap | Tailwind Equivalent |
   |-----------|---------------------|
   | `container` | `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8` |
   | `row` | `flex flex-wrap` |
   | `col-md-6` | `w-full md:w-1/2` |
   | `btn btn-primary` | `<x-button variant="primary">` |
   | `card` | `<x-card>` |
   | `form-group` | `<x-form-input>` |
   | `table table-striped` | `<x-table>` |
   | `alert alert-success` | `<x-alert type="success">` |
   | `modal` | `<x-modal>` |

3. **Replace jQuery interactions → Alpine.js:**

   ```blade
   {{-- Old jQuery --}}
   <script>
   $(document).ready(function() {
       $('#deleteBtn').click(function() {
           $('#deleteModal').modal('show');
       });
   });
   </script>

   {{-- New Alpine.js --}}
   <div x-data="{ showModal: false }">
       <x-button @click="showModal = true">Obriši</x-button>
       <x-modal x-show="showModal" @close="showModal = false">
           ...
       </x-modal>
   </div>
   ```

4. **Visual regression test:**
   ```bash
   npx playwright test kandidat --update-snapshots
   ```

5. **Manual QA checklist:**
   - [ ] All buttons render correctly
   - [ ] Forms submit successfully
   - [ ] Validation errors display
   - [ ] Modals open/close
   - [ ] Tables paginate
   - [ ] Responsive layout works (mobile/tablet/desktop)

6. **Commit:**
   ```bash
   git add resources/views/kandidat/
   git commit -m "refactor(frontend): migrate kandidat module to Tailwind + Alpine.js"
   ```

**Estimated Time:** 12 hours (5 templates × 2-3 hours each)  
**Deliverable:** Kandidat module fully migrated with passing visual tests

---

## Phase 2: Ispit Module (Week 2)

**Scope:**
- Ispit zapisnici (exam records)
- Rezultati (results tables)
- Student membership (add/remove students)

**Templates:**
- `resources/views/ispit/zapisnici.blade.php`
- `resources/views/ispit/rezultati.blade.php`
- `resources/views/ispit/add-student.blade.php`

**Testing Focus:**
- DataTables integration (if used)
- Complex table layouts
- Modal forms

**Estimated Time:** 10 hours  
**Deliverable:** Ispit module migrated with visual regression tests

---

## Phase 3: Prijava Module (Week 3)

**Scope:**
- Diplomski tema/odbrana/polaganje forms
- Multi-step wizards
- File upload UI

**Templates:**
- `resources/views/prijava/diplomski/*.blade.php`

**Testing Focus:**
- File upload components
- Multi-step form navigation
- Validation error display

**Estimated Time:** 10 hours  
**Deliverable:** Prijava module migrated

---

## Phase 4: Dashboard & Obavestenja (Week 4)

**Scope:**
- Dashboard (admin + student views)
- Notifications list
- Activity timeline

**Templates:**
- `resources/views/dashboard/*.blade.php`
- `resources/views/obavestenja/*.blade.php`

**Testing Focus:**
- Chart/graph rendering (if any)
- Real-time notifications
- Calendar views

**Estimated Time:** 8 hours  
**Deliverable:** Dashboard + Obavestenja migrated

---

## Phase 5: Remaining Modules (Week 5-6)

**Scope:**
- Aktivnost (activities)
- Raspored (schedule)
- Izveštaji (reports)
- Admin pages (users, settings)

**Estimated Time:** 12 hours  
**Deliverable:** All modules migrated

---

## Phase 6: Cleanup & Optimization

### 6.1 Remove Bootstrap Dependencies

```bash
# Remove Bootstrap files
rm public/css/bootstrap*.css
rm public/js/bootstrap*.js

# Remove jQuery
rm public/js/jquery*.js

# Update package.json (if Bootstrap was in npm)
npm uninstall bootstrap jquery

# Update vite.config.js - remove Bootstrap imports
```

### 6.2 Optimize Tailwind Build

```javascript
// tailwind.config.js
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#3b82f6',   // Your brand blue
        secondary: '#6b7280', // Your brand gray
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
```

### 6.3 Bundle Size Verification

```bash
# Build production assets
npm run build

# Check bundle size
du -h public/build/assets/*.css
du -h public/build/assets/*.js

# Expected results:
# CSS: ~15-25KB (compressed)
# JS: ~30-40KB (compressed)
```

**Estimated Time:** 4 hours  
**Deliverable:** Bootstrap fully removed, optimized Tailwind bundle

---

## Testing Checklist (Per Module)

### Visual Regression
- [ ] Desktop (1920x1080) - Chrome
- [ ] Desktop (1920x1080) - Firefox
- [ ] Tablet (768x1024) - Chrome
- [ ] Mobile (375x812) - Chrome
- [ ] Dark mode (if applicable)

### E2E Smoke Tests
- [ ] Navigation works (all links functional)
- [ ] Forms submit successfully
- [ ] Validation errors display correctly
- [ ] Modals open/close
- [ ] Dropdowns work
- [ ] File uploads work
- [ ] Tables sort/filter/paginate
- [ ] Alerts/notifications display

### Manual QA
- [ ] Colors match brand guidelines
- [ ] Spacing/alignment consistent
- [ ] Hover states work
- [ ] Focus states visible (accessibility)
- [ ] Loading states display
- [ ] Error states display

---

## CI/CD Integration

### GitHub Actions Workflow

```yaml
# .github/workflows/frontend-tests.yml
name: Frontend Tests

on: [push, pull_request]

jobs:
  visual-regression:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
      
      - name: Install dependencies
        run: |
          npm ci
          npx playwright install --with-deps chromium
      
      - name: Start Laravel server
        run: |
          php artisan key:generate
          php artisan migrate --seed
          php artisan serve --port=8000 &
          sleep 5
      
      - name: Run Playwright tests
        run: npx playwright test
      
      - name: Upload test results
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: playwright-report
          path: playwright-report/
```

---

## Risk Mitigation

### High-Risk Areas

1. **Complex JavaScript interactions** (DataTables, custom jQuery plugins)
   - **Mitigation:** Port to Alpine.js or vanilla JS first, test thoroughly
   
2. **Third-party library CSS conflicts**
   - **Mitigation:** Use Tailwind's `@layer` to isolate third-party styles
   
3. **Responsive breakpoints mismatch**
   - **Mitigation:** Define Tailwind breakpoints to match Bootstrap's (`sm: 640px` → `sm: 576px`)
   
4. **Browser compatibility**
   - **Mitigation:** Test on Chrome + Firefox minimum, IE11 if required (Tailwind supports IE11 with polyfills)

### Rollback Plan

If a module migration fails:
1. Restore backup: `git checkout resources/views/kandidat/index.blade.php.bootstrap-backup`
2. Keep Bootstrap CSS temporarily
3. Fix issues in separate branch
4. Re-test before re-deploying

---

## Success Metrics

- [ ] **100% visual parity** - No unintended UI changes (via Playwright screenshots)
- [ ] **0 broken interactions** - All forms, modals, tables work (via E2E tests)
- [ ] **Bundle size reduced 60%+** - From 160KB → ~64KB CSS
- [ ] **Improved Lighthouse scores:**
  - Performance: 90+ (faster CSS load)
  - Accessibility: 100 (better focus states with Tailwind)
  - Best Practices: 100
- [ ] **Developer satisfaction** - Easier to maintain Tailwind utility classes vs Bootstrap overrides

---

## Timeline Summary

| Phase | Module | Duration | Cumulative |
|-------|--------|----------|------------|
| 0 | Testing Setup + Baseline | 6h | 6h |
| 1 | Kandidat | 20h | 26h |
| 2 | Ispit | 10h | 36h |
| 3 | Prijava | 10h | 46h |
| 4 | Dashboard + Obavestenja | 8h | 54h |
| 5 | Remaining Modules | 12h | 66h |
| 6 | Cleanup + Optimization | 4h | 70h |

**Total Estimated Time:** 70 hours (8-9 working days)  
**Recommended Schedule:** 2-3 hours/day over 4-6 weeks (modular, low-risk)

---

## Next Steps

1. **Get approval for migration plan** ✅ (waiting for confirmation)
2. **Set up Playwright** → Phase 0.1
3. **Capture baseline screenshots** → Phase 0.3
4. **Start with Kandidat module** → Phase 1

**Questions to resolve:**
- Which module is most critical to migrate first? (Default: Kandidat)
- Do we need IE11 support? (Affects Tailwind config)
- Any custom jQuery plugins that need porting? (Audit required)

---

**Status:** 🟡 Ready to start Phase 0 (Testing Setup)
