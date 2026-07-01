import { test, expect } from '../fixtures/auth';

/**
 * SIMPLIFIED Baseline Screenshots - Critical Pages Only
 * 
 * Purpose: Capture visual state of core UI before Bootstrap → Tailwind migration
 * Scope: Reduced to 15 most critical pages for faster execution
 */

test.describe('Baseline Screenshots - Core Pages', () => {
  test.use({ viewport: { width: 1920, height: 1080 } });

  test('01 - Dashboard Main', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('01-dashboard-main.png', { fullPage: true });
  });

  test('02 - Kandidat List', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('02-kandidat-list.png', { fullPage: true });
  });

  test('03 - Kandidat Create', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('03-kandidat-create.png', { fullPage: true });
  });

  test('04 - Zapisnik List', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('04-zapisnik-list.png', { fullPage: true });
  });

  test('05 - Rezultat List', async ({ authenticatedPage: page }) => {
    await page.goto('/rezultat');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('05-rezultat-list.png', { fullPage: true });
  });

  test('06 - IspitniRok List', async ({ authenticatedPage: page }) => {
    await page.goto('/ispitniRok');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('06-ispitnirok-list.png', { fullPage: true });
  });

  test('07 - Aktivnost List', async ({ authenticatedPage: page }) => {
    await page.goto('/aktivnost');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('07-aktivnost-list.png', { fullPage: true });
  });

  test('08 - Obavestenje List', async ({ authenticatedPage: page }) => {
    await page.goto('/obavestenje');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('08-obavestenje-list.png', { fullPage: true });
  });

  test('09 - Raspored List', async ({ authenticatedPage: page }) => {
    await page.goto('/raspored');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('09-raspored-list.png', { fullPage: true });
  });

  test('10 - Student List', async ({ authenticatedPage: page }) => {
    await page.goto('/student');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('10-student-list.png', { fullPage: true });
  });

  test('11 - Prijava Subject List', async ({ authenticatedPage: page }) => {
    await page.goto('/predmeti/');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('11-prijava-subject-list.png', { fullPage: true });
  });
});

test.describe('Baseline Screenshots - Mobile', () => {
  test.use({ viewport: { width: 375, height: 812 } });

  test('M01 - Dashboard Mobile', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('M01-dashboard-mobile.png', { fullPage: true });
  });

  test('M02 - Kandidat List Mobile', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('M02-kandidat-mobile.png', { fullPage: true });
  });

  test('M03 - Prijava Subject List Mobile', async ({ authenticatedPage: page }) => {
    await page.goto('/predmeti/');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await expect(page).toHaveScreenshot('M03-prijava-subject-list-mobile.png', { fullPage: true });
  });
});
