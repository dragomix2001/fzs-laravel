import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright Configuration for FZS-Laravel Frontend Migration
 * 
 * Purpose: Visual regression testing to ensure UI parity during
 * Bootstrap → Tailwind CSS migration.
 */
export default defineConfig({
  testDir: './tests/e2e/specs',
  
  /* Run tests in files in parallel */
  fullyParallel: true,
  
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['list'],
  ],
  
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: 'http://localhost:8000',
    
    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
    
    /* Screenshot on failure only (saves space) */
    screenshot: 'only-on-failure',
    
    /* Video on failure only */
    video: 'retain-on-failure',
    
    /* Ignore HTTPS errors (for local dev) */
    ignoreHTTPSErrors: true,
  },

  /* Visual regression settings */
  expect: {
    toHaveScreenshot: {
      // Allow 20% pixel difference (accounts for font rendering, anti-aliasing)
      threshold: 0.2,
      
      // Max 100 pixels can differ
      maxDiffPixels: 100,
      
      // Disable animations for stable screenshots
      animations: 'disabled',
      
      // Wait for fonts to load
      scale: 'css',
      
      // Increase timeout for slow pages
      timeout: 15000,
    },
  },

  /* Increase default test timeout */
  timeout: 90000,

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  /* Run your local dev server before starting the tests */
  webServer: {
    command: 'php artisan serve --port=8000',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000, // 2 minutes
  },
});
