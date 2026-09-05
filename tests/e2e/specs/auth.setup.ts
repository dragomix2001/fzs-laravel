import { test as setup } from '@playwright/test';

setup('authenticate admin', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[name="email"]', process.env.E2E_EMAIL ?? 'fzs@fzs.rs');
  await page.fill('input[name="password"]', process.env.E2E_PASSWORD ?? 'fzs123');
  await page.click('button[type="submit"]');
  await page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'commit' });
  await page.waitForLoadState('domcontentloaded');
  await page.goto('/student/index/1');
  await page.waitForURL(/\/student\/index\/1/, { waitUntil: 'domcontentloaded' });
  await page.context().storageState({ path: 'playwright/.auth/admin.json' });
});