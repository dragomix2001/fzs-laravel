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
    await use(page);
  },
});

export { expect } from '@playwright/test';
