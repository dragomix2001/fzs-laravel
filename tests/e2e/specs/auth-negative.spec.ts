import { test, expect } from '@playwright/test';

test.use({ storageState: { cookies: [], origins: [] } });

test('invalid login stays on the login page', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[name="email"]', 'invalid@example.test');
  await page.fill('input[name="password"]', 'wrong-password');
  await page.click('button[type="submit"]');

  await expect(page).toHaveURL(/\/login/);
  await expect(page.locator('input[name="email"]')).toBeVisible();
});