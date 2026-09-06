import { test as base, Page } from '@playwright/test';

/**
 * Auth Fixture - Extends Playwright test with authenticated page
 * 
 * Usage:
 *   import { test, expect } from '../fixtures/auth';
 *   
 *   test('some test', async ({ authenticatedPage }) => {
 *     await authenticatedPage.goto('/dashboard');
 *     // User is already logged in
 *   });
 */

type AuthFixtures = {
  authenticatedPage: Page;
};

export const test = base.extend<AuthFixtures>({
  authenticatedPage: async ({ page }, use) => {
    await page.goto('/dashboard');

    if (page.url().endsWith('/login')) {
      await page.fill('input[name="email"]', process.env.E2E_EMAIL ?? 'fzs@fzs.rs');
      await page.fill('input[name="password"]', process.env.E2E_PASSWORD ?? 'fzs123');
      await page.click('button[type="submit"]');
      await page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'commit' });
    }

    await use(page);
  },
});

export { expect } from '@playwright/test';
