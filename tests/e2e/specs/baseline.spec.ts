import { test, expect } from '../fixtures/auth';

/**
 * Baseline Screenshot Tests - PRE-MIGRATION Reference
 * 
 * Purpose: Capture visual state of all critical UI before Bootstrap → Tailwind migration.
 * These screenshots serve as the "source of truth" for visual regression testing.
 * 
 * Run:
 *   npx playwright test baseline --update-snapshots
 * 
 * IMPORTANT: Run this BEFORE starting any frontend migration work!
 */

test.describe('Baseline Screenshots - Desktop (1920x1080)', () => {
  test.use({ viewport: { width: 1920, height: 1080 } });

  test('Dashboard - Main View', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-dashboard-main.png', {
      fullPage: true,
    });
  });

  test('Dashboard - Ispiti Stats', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard/ispiti');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-dashboard-ispiti.png', {
      fullPage: true,
    });
  });

  test('Dashboard - Studenti Stats', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard/studenti');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-dashboard-studenti.png', {
      fullPage: true,
    });
  });

  test('Kandidat - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-kandidat-list.png', {
      fullPage: true,
    });
  });

  test('Kandidat - Create Form (Page 1)', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-kandidat-create-page1.png', {
      fullPage: true,
    });
  });

  test('Kandidat - Incomplete Documents', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/documents/incomplete');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-kandidat-documents-incomplete.png', {
      fullPage: true,
    });
  });

  test('Ispit - Zapisnici List', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-ispit-zapisnici.png', {
      fullPage: true,
    });
  });

  test('Ispit - Rezultati List', async ({ authenticatedPage: page }) => {
    await page.goto('/rezultat');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-ispit-rezultati.png', {
      fullPage: true,
    });
  });

  test('IspitniRok - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/ispitniRok');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-ispitnirok-list.png', {
      fullPage: true,
    });
  });

  test('Aktivnost - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/aktivnost');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-aktivnost-list.png', {
      fullPage: true,
    });
  });

  test('Aktivnost - Create Form', async ({ authenticatedPage: page }) => {
    await page.goto('/aktivnost/create');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-aktivnost-create.png', {
      fullPage: true,
    });
  });

  test('Obavestenja - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/obavestenje');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-obavestenja-list.png', {
      fullPage: true,
    });
  });

  test('Raspored - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/raspored');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-raspored-list.png', {
      fullPage: true,
    });
  });

  test('Student - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/student');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-student-list.png', {
      fullPage: true,
    });
  });

  test('Users - List View', async ({ authenticatedPage: page }) => {
    await page.goto('/users');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-users-list.png', {
      fullPage: true,
    });
  });
});

test.describe('Baseline Screenshots - Tablet (768x1024)', () => {
  test.use({ viewport: { width: 768, height: 1024 } });

  test('Dashboard - Tablet View', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-tablet-dashboard.png', {
      fullPage: true,
    });
  });

  test('Kandidat List - Tablet View', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-tablet-kandidat-list.png', {
      fullPage: true,
    });
  });

  test('Ispit Zapisnici - Tablet View', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-tablet-zapisnici.png', {
      fullPage: true,
    });
  });
});

test.describe('Baseline Screenshots - Mobile (375x812)', () => {
  test.use({ viewport: { width: 375, height: 812 } }); // iPhone X dimensions

  test('Dashboard - Mobile View', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-mobile-dashboard.png', {
      fullPage: true,
    });
  });

  test('Kandidat List - Mobile View', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('baseline-mobile-kandidat-list.png', {
      fullPage: true,
    });
  });

  test('Navigation Menu - Mobile', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    
    // Click hamburger menu (if exists)
    const menuButton = page.locator('button[data-toggle="collapse"], button.navbar-toggler').first();
    if (await menuButton.isVisible()) {
      await menuButton.click();
      await page.waitForTimeout(500); // Wait for animation
    }
    
    await expect(page).toHaveScreenshot('baseline-mobile-menu-open.png', {
      fullPage: true,
    });
  });
});

test.describe('Baseline Screenshots - Interactive Elements', () => {
  test.use({ viewport: { width: 1920, height: 1080 } });

  test('Button Hover States', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.waitForLoadState('networkidle');
    
    // Hover over first action button
    const firstButton = page.locator('a.btn, button.btn').first();
    await firstButton.hover();
    await page.waitForTimeout(300); // Wait for hover transition
    
    await expect(page).toHaveScreenshot('baseline-button-hover.png');
  });

  test('Form Input Focus State', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    await page.waitForLoadState('networkidle');
    
    // Focus on first input
    const firstInput = page.locator('input[type="text"], input[type="email"]').first();
    await firstInput.focus();
    await page.waitForTimeout(200);
    
    await expect(page).toHaveScreenshot('baseline-input-focus.png');
  });

  test('Dropdown Open State', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    await page.waitForLoadState('networkidle');
    
    // Click first select to open dropdown
    const firstSelect = page.locator('select').first();
    if (await firstSelect.isVisible()) {
      await firstSelect.click();
      await page.waitForTimeout(300);
      
      await expect(page).toHaveScreenshot('baseline-dropdown-open.png');
    }
  });
});

test.describe('Baseline Screenshots - Error States', () => {
  test.use({ viewport: { width: 1920, height: 1080 } });

  test('Form Validation Errors', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    await page.waitForLoadState('networkidle');
    
    // Submit empty form to trigger validation
    const submitButton = page.locator('button[type="submit"]').first();
    await submitButton.click();
    await page.waitForTimeout(500); // Wait for validation errors to appear
    
    await expect(page).toHaveScreenshot('baseline-validation-errors.png', {
      fullPage: true,
    });
  });
});
