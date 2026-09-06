import { request as playwrightRequest, test, expect } from '@playwright/test';

test.use({ storageState: { cookies: [], origins: [] } });

test('invalid login stays on the login page', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[name="email"]', 'invalid@example.test');
  await page.fill('input[name="password"]', 'wrong-password');
  await page.click('button[type="submit"]');

  await expect(page).toHaveURL(/\/login/);
  await expect(page.locator('input[name="email"]')).toBeVisible();
});

test('guest cannot access the admin users page', async ({ page }) => {
  await page.goto('/users');

  await expect(page).toHaveURL(/\/login/);
  await expect(page.locator('input[name="email"]')).toBeVisible();
});

test('public API returns seeded candidates', async ({ request }) => {
  const response = await request.get('/api/v1/kandidati');

  expect(response.ok()).toBeTruthy();
  const payload = await response.json();
  expect(Array.isArray(payload)).toBeTruthy();
});

test('protected student API rejects unauthenticated requests', async () => {
  const unauthenticated = await playwrightRequest.newContext({
    baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:8080',
    storageState: { cookies: [], origins: [] },
    extraHTTPHeaders: { Accept: 'application/json' },
    maxRedirects: 0,
  });
  const response = await unauthenticated.get('/api/v1/student/profile');
  await unauthenticated.dispose();

  expect(response.status()).toBe(401);
});

test('API login returns a token that authenticates the user endpoint', async ({ request }) => {
  const login = await request.post('/api/v1/auth/login', {
    data: { email: 'fzs@fzs.rs', password: 'fzs123' },
  });

  expect(login.ok()).toBeTruthy();
  const loginPayload = await login.json();
  expect(loginPayload.token).toBeTruthy();

  const user = await request.get('/api/v1/auth/user', {
    headers: { Authorization: `Bearer ${loginPayload.token}` },
  });
  expect(user.ok()).toBeTruthy();
  expect((await user.json()).email).toBe('fzs@fzs.rs');
});