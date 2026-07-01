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
    // Navigate to login page
    await page.goto('/login');
    
    // Fill login form
    await page.fill('input[name="email"]', 'test@fzs.test');
    await page.fill('input[name="password"]', 'password');
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Wait for redirect after login (Laravel redirects to '/' after login)
    await page.waitForURL(/^(?!.*\/login).*$/);  // Wait for any URL that's NOT /login
    await page.waitForLoadState('networkidle');  // Wait for page to fully load
    
    // Fixture is now ready - pass authenticated page to test
    await use(page);
  },
});

export { expect } from '@playwright/test';
